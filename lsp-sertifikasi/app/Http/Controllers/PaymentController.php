<?php

namespace App\Http\Controllers;

use App\Models\Asesmen;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Get Midtrans configuration
     */
    private function getMidtransConfig()
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Create Midtrans Snap Token
     */
    public function createSnapToken(Asesmen $asesmen)
    {
        // Verify asesmen belongs to authenticated user
        if ($asesmen->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Check if asesmen is verified and has fee amount
        if ($asesmen->status !== 'verified' || !$asesmen->fee_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Asesmen belum diverifikasi atau biaya belum ditentukan'
            ], 400);
        }

        $this->getMidtransConfig();

        // Generate unique order ID
        $orderId = 'LSP-' . $asesmen->id . '-' . time();

        // Transaction details
        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => (int) $asesmen->fee_amount,
        ];

        // Customer details
        $customerDetails = [
            'first_name' => $asesmen->full_name,
            'email' => $asesmen->email,
            'phone' => $asesmen->phone,
        ];

        // Item details - UPDATED: Include training fee breakdown
        $itemDetails = [];
        
        // Check if there's training fee
        $trainingFee = $asesmen->training_flag ? 1500000 : 0;
        $certificationFee = $asesmen->fee_amount - $trainingFee;
        
        if ($trainingFee > 0) {
            // If training is included, show breakdown
            $itemDetails[] = [
                'id' => 'CERT-' . $asesmen->skema_id,
                'price' => (int) $certificationFee,
                'quantity' => 1,
                'name' => 'Sertifikasi ' . $asesmen->skema->name,
            ];
            
            $itemDetails[] = [
                'id' => 'TRAINING-' . $asesmen->id,
                'price' => (int) $trainingFee,
                'quantity' => 1,
                'name' => 'Pelatihan Sertifikasi',
            ];
        } else {
            // No training, just certification
            $itemDetails[] = [
                'id' => 'CERT-' . $asesmen->skema_id,
                'price' => (int) $asesmen->fee_amount,
                'quantity' => 1,
                'name' => 'Sertifikasi ' . $asesmen->skema->name,
            ];
        }

        // Transaction params
        $params = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
            'callbacks' => [
                'finish' => route('payment.finish', $asesmen->id),
            ]
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // Create payment record with pending status
            Payment::updateOrCreate(
                ['asesmen_id' => $asesmen->id],
                [
                    'amount' => $asesmen->fee_amount,
                    'method' => 'midtrans',
                    'status' => 'pending',
                    'order_id' => $orderId,
                    'notes' => $asesmen->training_flag 
                        ? 'Menunggu pembayaran via Midtrans (Termasuk Pelatihan Rp 1.500.000)'
                        : 'Menunggu pembayaran via Midtrans',
                ]
            );

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $orderId,
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans Snap Token Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat token pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Midtrans notification webhook
     * UPDATED: Now supports both individual and collective payments
     */
    public function handleNotification(Request $request)
    {
        Log::info('Midtrans notification received: ' . json_encode($request->all()));
        
        $this->getMidtransConfig();

        try {
            $notification = new \Midtrans\Notification();

            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status;
            $orderId = $notification->order_id;
            $paymentType = $notification->payment_type;
            $transactionId = $notification->transaction_id;

            Log::info("Processing notification for order: {$orderId}, status: {$transactionStatus}");

            // Check if this is a collective payment (BATCH-xxx) or individual (LSP-xxx)
            if (str_starts_with($orderId, 'BATCH-')) {
                return $this->handleCollectivePaymentNotification(
                    $orderId, 
                    $transactionStatus, 
                    $fraudStatus, 
                    $paymentType, 
                    $transactionId
                );
            } else {
                return $this->handleIndividualPaymentNotification(
                    $orderId, 
                    $transactionStatus, 
                    $fraudStatus, 
                    $paymentType, 
                    $transactionId
                );
            }

        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Handle individual payment notification
     */
    private function handleIndividualPaymentNotification($orderId, $transactionStatus, $fraudStatus, $paymentType, $transactionId)
    {
        // Extract asesmen_id from order_id (format: LSP-{asesmen_id}-{timestamp})
        $orderParts = explode('-', $orderId);
        if (count($orderParts) < 2) {
            Log::error('Invalid order ID format: ' . $orderId);
            return response()->json(['status' => 'failed'], 400);
        }

        $asesmenId = $orderParts[1];
        $asesmen = Asesmen::find($asesmenId);

        if (!$asesmen) {
            Log::error('Asesmen not found for order: ' . $orderId);
            return response()->json(['status' => 'failed'], 404);
        }

        $payment = Payment::where('asesmen_id', $asesmen->id)->first();

        if (!$payment) {
            Log::error('Payment record not found for asesmen: ' . $asesmenId);
            return response()->json(['status' => 'failed'], 404);
        }

        // Handle payment status
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $this->updatePaymentSuccess($payment, $asesmen, $paymentType, $transactionId);
            }
        } elseif ($transactionStatus == 'settlement') {
            $this->updatePaymentSuccess($payment, $asesmen, $paymentType, $transactionId);
        } elseif ($transactionStatus == 'pending') {
            $payment->update([
                'status' => 'pending',
                'payment_type' => $paymentType,
                'transaction_id' => $transactionId,
                'notes' => $asesmen->training_flag 
                    ? 'Menunggu pembayaran via Midtrans (Termasuk Pelatihan)'
                    : 'Menunggu pembayaran via Midtrans',
            ]);
        } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
            $payment->update([
                'status' => 'rejected',
                'payment_type' => $paymentType,
                'transaction_id' => $transactionId,
                'notes' => 'Pembayaran gagal/dibatalkan. Status: ' . $transactionStatus,
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle collective payment notification
     * NEW: Process all payments with the same order_id
     */
    private function handleCollectivePaymentNotification($orderId, $transactionStatus, $fraudStatus, $paymentType, $transactionId)
    {
        // Find ALL payment records with this order_id (collective payment)
        $payments = Payment::where('order_id', $orderId)->get();

        if ($payments->isEmpty()) {
            Log::error('No payment records found for collective order: ' . $orderId);
            return response()->json(['status' => 'failed'], 404);
        }

        Log::info("Found {$payments->count()} payment records for collective order {$orderId}");

        // Handle payment status for ALL payments in the batch
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                foreach ($payments as $payment) {
                    $asesmen = Asesmen::find($payment->asesmen_id);
                    if ($asesmen) {
                        $this->updatePaymentSuccess($payment, $asesmen, $paymentType, $transactionId);
                    }
                }
            }
        } elseif ($transactionStatus == 'settlement') {
            foreach ($payments as $payment) {
                $asesmen = Asesmen::find($payment->asesmen_id);
                if ($asesmen) {
                    $this->updatePaymentSuccess($payment, $asesmen, $paymentType, $transactionId);
                }
            }
        } elseif ($transactionStatus == 'pending') {
            foreach ($payments as $payment) {
                $payment->update([
                    'status' => 'pending',
                    'payment_type' => $paymentType,
                    'transaction_id' => $transactionId,
                    'notes' => 'Menunggu pembayaran kolektif via Midtrans',
                ]);
            }
        } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
            foreach ($payments as $payment) {
                $payment->update([
                    'status' => 'rejected',
                    'payment_type' => $paymentType,
                    'transaction_id' => $transactionId,
                    'notes' => 'Pembayaran kolektif gagal/dibatalkan. Status: ' . $transactionStatus,
                ]);
            }
        }

        Log::info("Successfully updated {$payments->count()} payments for collective order {$orderId}");
        return response()->json(['status' => 'success']);
    }

    /**
     * Update payment to success
     */
    private function updatePaymentSuccess($payment, $asesmen, $paymentType, $transactionId)
    {
        $notes = 'Pembayaran berhasil terverifikasi otomatis via Midtrans. Transaction ID: ' . $transactionId;
        
        // Add training info to notes if applicable
        if ($asesmen->training_flag) {
            $notes .= ' (Termasuk Pelatihan Rp 1.500.000)';
        }
        
        $payment->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => null, // Auto verified by system
            'notes' => $notes,
            'transaction_id' => $transactionId,
            'payment_type' => $paymentType,
        ]);

        // Update asesmen status to paid
        $asesmen->update([
            'status' => 'paid',
        ]);

        Log::info("Payment auto-verified for asesmen {$asesmen->id}, transaction {$transactionId}, training: " . ($asesmen->training_flag ? 'yes' : 'no'));

        // TODO: Send email notification
        // Mail::to($asesmen->email)->send(new PaymentSuccessMail($asesmen));
    }

    /**
     * Payment finish page (redirect from Midtrans)
     */
    public function finish(Asesmen $asesmen)
    {
        // Verify asesmen belongs to authenticated user
        if ($asesmen->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return view('asesi.payment-finish', compact('asesmen'));
    }

    /**
     * Check payment status
     * FOR TESTING: Auto verify if status is pending
     */
    public function checkStatus(Asesmen $asesmen)
    {
        // Verify asesmen belongs to authenticated user
        if ($asesmen->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $payment = Payment::where('asesmen_id', $asesmen->id)->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        // FOR TESTING ONLY: Auto verify pending payments
        // Remove this block in production when webhook is working
        if ($payment->status === 'pending' && $payment->order_id) {
            try {
                $this->getMidtransConfig();
                $status = \Midtrans\Transaction::status($payment->order_id);
                
                Log::info("Checking Midtrans status for order {$payment->order_id}: " . $status->transaction_status);
                
                // Update payment status based on Midtrans response
                if ($status->transaction_status === 'settlement' || 
                    ($status->transaction_status === 'capture' && $status->fraud_status === 'accept')) {
                    
                    // Check if this is collective payment
                    if (str_starts_with($payment->order_id, 'BATCH-')) {
                        // Update all payments in the batch
                        $allPayments = Payment::where('order_id', $payment->order_id)->get();
                        foreach ($allPayments as $p) {
                            $a = Asesmen::find($p->asesmen_id);
                            if ($a) {
                                $this->updatePaymentSuccess(
                                    $p, 
                                    $a, 
                                    $status->payment_type ?? 'unknown',
                                    $status->transaction_id ?? $payment->order_id
                                );
                            }
                        }
                    } else {
                        // Individual payment
                        $this->updatePaymentSuccess(
                            $payment, 
                            $asesmen, 
                            $status->payment_type ?? 'unknown',
                            $status->transaction_id ?? $payment->order_id
                        );
                    }
                } elseif ($status->transaction_status === 'pending') {
                    $payment->update([
                        'transaction_id' => $status->transaction_id ?? null,
                        'payment_type' => $status->payment_type ?? null,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error checking payment status: ' . $e->getMessage());
            }
        }

        // Refresh payment after potential update
        $payment->refresh();
        $asesmen->refresh();

        return response()->json([
            'success' => true,
            'status' => $payment->status,
            'asesmen_status' => $asesmen->status,
            'payment_details' => [
                'amount' => $payment->amount,
                'method' => $payment->method,
                'transaction_id' => $payment->transaction_id,
                'verified_at' => $payment->verified_at,
            ]
        ]);
    }

    /**
     * FOR TESTING: Manual verify payment (simulate success)
     * This bypasses Midtrans and directly marks payment as verified
     */
    public function testVerify(Asesmen $asesmen)
    {
        // Only allow in non-production
        if (config('midtrans.is_production')) {
            abort(403, 'Test verify not allowed in production');
        }

        // Verify asesmen belongs to authenticated user
        if ($asesmen->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $payment = Payment::where('asesmen_id', $asesmen->id)->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        // Simulate successful payment
        $this->updatePaymentSuccess(
            $payment, 
            $asesmen, 
            'simulation',
            'TEST-' . time()
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment verified (TEST MODE)',
            'status' => 'verified',
            'asesmen_status' => 'paid'
        ]);
    }
}