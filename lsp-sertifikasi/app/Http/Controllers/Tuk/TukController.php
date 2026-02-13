<?php

namespace App\Http\Controllers\Tuk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tuk;
use App\Models\Asesmen;
use App\Models\Schedule;
use App\Models\Payment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ParticipantsImport;
use Barryvdh\DomPDF\Facade\Pdf;

class TukController extends Controller
{
    /**
     * Dashboard
     */
    public function dashboard()
    {
        $tuk = auth()->user()->tuk;
        
        if (!$tuk) {
            abort(403, 'Akun TUK tidak ditemukan.');
        }

        $stats = [
            'total_asesi' => Asesmen::where('tuk_id', $tuk->id)->count(),
            'pending_schedule' => Asesmen::where('tuk_id', $tuk->id)
                ->where('status', 'paid')
                ->count(),
            'scheduled' => Asesmen::where('tuk_id', $tuk->id)
                ->where('status', 'scheduled')
                ->count(),
            'completed' => Asesmen::where('tuk_id', $tuk->id)
                ->whereIn('status', ['assessed', 'certified'])
                ->count(),
            'pending_payment' => Asesmen::where('tuk_id', $tuk->id)
                ->where('is_collective', true)
                ->where(function($q) {
                    $q->where(function($subq) {
                        // Before timing: verified but not paid
                        $subq->where('payment_phases', 'single')
                            ->where('status', 'verified');
                    })->orWhere(function($subq) {
                        // After timing: assessed but not paid
                        $subq->where('payment_phases', 'two_phase')
                            ->whereIn('status', ['assessed', 'certified']);
                    });
                })
                ->whereDoesntHave('payment', function($q) {
                    $q->where('status', 'verified');
                })
                ->distinct('collective_batch_id')
                ->count(),
            'pending_verification' => Asesmen::where('tuk_id', $tuk->id)
                ->where('status', 'data_completed')->count(),
        ];

        $recent_asesmens = Asesmen::with(['user', 'skema', 'schedule'])
            ->where('tuk_id', $tuk->id)
            ->latest()
            ->take(10)
            ->get();

        return view('tuk.dashboard', compact('stats', 'recent_asesmens', 'tuk'));
    }

    /**
     * Collective Registration - Form
     */
    public function collectiveRegistration()
    {
        $tuk = auth()->user()->tuk;
        return view('tuk.collective.form', compact('tuk'));
    }

    /**
     * Collective Registration - Store
     */
    public function storeCollectiveRegistration(Request $request)
    {
        $request->validate([
            'participants' => 'required|array|min:1',
            'participants.*.name' => 'required|string|max:255',
            'participants.*.email' => 'required|email|unique:users,email',
            'skema_id' => 'required|exists:skemas,id',
            'payment_phases' => 'required|in:single,two_phase'        
        ]);

        $tuk = auth()->user()->tuk;
        $registeredCount = 0;
        $errors = [];
        
        // Generate batch ID untuk kolektif
        $batchId = 'BATCH-' . $tuk->code . '-' . time();

        DB::beginTransaction();
        try {
            foreach ($request->participants as $index => $participant) {
                // Check if email already exists
                if (User::where('email', $participant['email'])->exists()) {
                    $errors[] = "Email {$participant['email']} sudah terdaftar";
                    continue;
                }

                // Generate random password (6 digit)
                $password = 'password123';
                
                // Create user account
                $user = User::create([
                    'name' => $participant['name'],
                    'email' => $participant['email'],
                    'password' => Hash::make($password),
                    'role' => 'asesi',
                    'is_active' => true,
                    'password_changed_at' => null,
                    'email_verified_at' => now(), // ✅ Mandiri user sudah verifikasi email
                ]);

                // Create asesmen record
                Asesmen::create([
                    'user_id' => $user->id,
                    'tuk_id' => $tuk->id,
                    'skema_id' => $request->skema_id,
                    'full_name' => $participant['name'],
                    'registration_date' => now(),
                    'status' => 'registered',
                    'registered_by' => auth()->id(),
                    'is_collective' => true,
                    'collective_batch_id' => $batchId,
                    'payment_phases' => $request->payment_phases,
                    'collective_paid_by_tuk' => true, // TUK yang akan bayar
                    'skip_payment' => true, // Skip payment step untuk asesi

                ]);

                // TODO: Send email with credentials
                // For now, just log the password
                Log::info("Collective Registration - User: {$participant['email']}, Password: {$password}");

                $registeredCount++;
            }

            DB::commit();

            $message = "$registeredCount peserta berhasil didaftarkan secara kolektif!";
            
            if (!empty($errors)) {
                $message .= " Namun " . count($errors) . " peserta gagal didaftarkan: " . implode(', ', $errors);
            }

            if ($request->payment_timing === 'before') {
                $message .= " Setelah Admin LSP memverifikasi semua peserta, Anda dapat melakukan pembayaran kolektif.";
            } else {
                $message .= " Anda akan melakukan pembayaran kolektif setelah semua peserta menyelesaikan asesmen.";
            }

            return redirect()->route('tuk.asesi')
                ->with('success', $message)
                ->with('batch_id', $batchId)
                ->with('registered_count', $registeredCount);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Collective Registration Error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * List Asesi
     */
    public function asesi()
    {
        $tuk = auth()->user()->tuk;
        
        $asesmens = Asesmen::with(['user', 'skema', 'payment', 'schedule'])
            ->where('tuk_id', $tuk->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Group collective batches
        $collectiveBatches = $asesmens->where('is_collective', true)
            ->groupBy('collective_batch_id')
            ->map(function ($batch) {
                $batchData = $batch->first();
                return [
                    'batch_id' => $batchData->collective_batch_id,
                    'count' => $batch->count(),
                    'skema' => $batchData->skema,
                    'payment_timing' => $batchData->collective_payment_timing,
                    'payment_status' => $batchData->getBatchPaymentStatus(),
                    'ready_for_payment' => $batchData->isBatchReadyForPayment(),
                    'members' => $batch,
                ];
            });

        return view('tuk.asesi.index', compact('asesmens', 'collectiveBatches', 'tuk'));
    }

    /**
     * Scheduling - List asesi yang perlu dijadwalkan
     */
    public function schedules()
    {
        $tuk = auth()->user()->tuk;
        
        $asesmens = Asesmen::with(['user', 'skema'])
            ->where('tuk_id', $tuk->id)
            ->where('status', 'paid')
            ->get();

        $scheduled = Schedule::with(['asesmen.user'])
            ->whereHas('asesmen', function($q) use ($tuk) {
                $q->where('tuk_id', $tuk->id);
            })
            ->orderBy('assessment_date', 'asc') // ✅ FIXED: dari scheduled_date ke assessment_date
            ->get();

        return view('tuk.schedules.index', compact('asesmens', 'scheduled', 'tuk'));
    }

    /**
     * Batch Create Schedule
     */
    public function batchCreateSchedule(Request $request)
    {
        $tuk = auth()->user()->tuk;

        $request->validate([
            'asesmen_ids' => 'required|array|min:1',
            'asesmen_ids.*' => 'exists:asesmens,id',
            'assessment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'location' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $scheduledCount = 0;
            $errors = [];

            foreach ($request->asesmen_ids as $asesmenId) {
                $asesmen = Asesmen::find($asesmenId);

                // Verify asesmen belongs to this TUK
                if (!$asesmen || $asesmen->tuk_id != $tuk->id) {
                    $errors[] = "Asesmen #{$asesmenId} tidak ditemukan atau bukan milik TUK ini";
                    continue;
                }

                // Verify asesmen is in 'paid' status
                if ($asesmen->status !== 'paid') {
                    $errors[] = "Asesmen #{$asesmenId} belum dalam status 'Sudah Bayar'";
                    continue;
                }

                // Check if already has schedule
                if ($asesmen->schedule) {
                    $errors[] = "Asesmen #{$asesmenId} sudah memiliki jadwal";
                    continue;
                }

                // Create schedule
                Schedule::create([
                    'asesmen_id' => $asesmen->id,
                    'assessment_date' => $request->assessment_date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'location' => $request->location,
                    'notes' => $request->notes,
                    'created_by' => auth()->id(),
                ]);

                // Update asesmen status
                $asesmen->update(['status' => 'scheduled']);

                $scheduledCount++;
            }

            DB::commit();

            $message = "$scheduledCount jadwal asesmen berhasil dibuat!";
            
            if (!empty($errors)) {
                $message .= " Namun " . count($errors) . " gagal: " . implode(', ', $errors);
                Log::warning('Batch Schedule Errors: ' . implode(' | ', $errors));
            }

            return redirect()->route('tuk.schedules')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch Schedule Error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * View Schedule Detail
     */
    public function viewSchedule(Schedule $schedule)
    {
        $tuk = auth()->user()->tuk;
        
        // Verify schedule belongs to this TUK
        if ($schedule->asesmen->tuk_id != $tuk->id) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'schedule' => [
                'id' => $schedule->id,
                'asesmen_name' => $schedule->asesmen->full_name ?? $schedule->asesmen->user->name,
                'skema' => $schedule->asesmen->skema->name,
                'assessment_date' => $schedule->assessment_date->format('d F Y'),
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'location' => $schedule->location,
                'notes' => $schedule->notes,
                'status' => $schedule->asesmen->status_label,
            ]
        ]);
    }

    /**
     * Edit Schedule - Get data
     */
    public function editSchedule(Schedule $schedule)
    {
        $tuk = auth()->user()->tuk;
        
        // Verify schedule belongs to this TUK
        if ($schedule->asesmen->tuk_id != $tuk->id) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'schedule' => [
                'id' => $schedule->id,
                'asesmen_id' => $schedule->asesmen_id,
                'asesmen_name' => $schedule->asesmen->full_name ?? $schedule->asesmen->user->name,
                'assessment_date' => $schedule->assessment_date->format('Y-m-d'),
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'location' => $schedule->location,
                'notes' => $schedule->notes,
            ]
        ]);
    }

    /**
     * Update Schedule - Submit
     */
    public function updateScheduleSubmit(Request $request, Schedule $schedule)
    {
        $tuk = auth()->user()->tuk;
        
        // Verify schedule belongs to this TUK
        if ($schedule->asesmen->tuk_id != $tuk->id) {
            abort(403);
        }

        $request->validate([
            'assessment_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'location' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $schedule->update([
            'assessment_date' => $request->assessment_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'notes' => $request->notes,
        ]);

        return redirect()->route('tuk.schedules')
            ->with('success', 'Jadwal asesmen berhasil diupdate!');
    }

    /**
     * Delete Schedule
     */
    public function deleteSchedule(Schedule $schedule)
    {
        $tuk = auth()->user()->tuk;
        
        // Verify schedule belongs to this TUK
        if ($schedule->asesmen->tuk_id != $tuk->id) {
            abort(403);
        }

        // Update asesmen status back to 'paid'
        $schedule->asesmen->update(['status' => 'paid']);

        $schedule->delete();

        return redirect()->route('tuk.schedules')
            ->with('success', 'Jadwal berhasil dihapus. Status asesi dikembalikan ke "Sudah Bayar".');
    }

    /**
     * Detail Asesi
     */
    public function asesiDetail(Asesmen $asesmen)
    {
        $tuk = auth()->user()->tuk;
        
        // Verify asesmen belongs to this TUK
        if ($asesmen->tuk_id != $tuk->id) {
            abort(403);
        }

        $asesmen->load(['user', 'skema', 'payment', 'schedule', 'certificate']);

        // If collective, load batch members
        $batchMembers = $asesmen->is_collective ? $asesmen->fullBatch() : collect([]);

        return view('tuk.asesi.detail', compact('asesmen', 'tuk', 'batchMembers'));
    }

    /**
     * Collective Payment - Show batch for payment
     */
    public function collectivePayment($batchId)
    {
        $tuk = auth()->user()->tuk;
        
        // Get all asesmens in this batch
        $asesmens = Asesmen::with(['user', 'skema', 'payments'])
            ->where('collective_batch_id', $batchId)
            ->where('tuk_id', $tuk->id)
            ->get();

        if ($asesmens->isEmpty()) {
            return redirect()->route('tuk.asesi')
                ->with('error', 'Batch tidak ditemukan');
        }

        $firstAsesmen = $asesmens->first();
        
        // Get payment phases (single or two_phase)
        $paymentPhases = $firstAsesmen->payment_phases ?? 'single';

        // Determine current phase
        $currentPhase = 'full';
        $phase1Status = 'not_paid';
        $phase2Status = 'not_paid';
        
        if ($paymentPhases === 'two_phase') {
            // Check if phase 1 already paid
            $phase1Paid = $asesmens->every(function($asesmen) {
                return $asesmen->payments()
                    ->where('payment_phase', 'phase_1')
                    ->where('status', 'verified')
                    ->exists();
            });
            
            $phase2Paid = $asesmens->every(function($asesmen) {
                return $asesmen->payments()
                    ->where('payment_phase', 'phase_2')
                    ->where('status', 'verified')
                    ->exists();
            });
            
            if ($phase1Paid) {
                $phase1Status = 'paid';
            }
            
            if ($phase2Paid) {
                $phase2Status = 'paid';
            }
            
            // Determine current phase
            if (!$phase1Paid) {
                $currentPhase = 'phase_1';
            } else if (!$phase2Paid) {
                $currentPhase = 'phase_2';
            }
        }

        // Check if ready for payment
        $canPay = false;
        if ($paymentPhases === 'single') {
            $canPay = $firstAsesmen->isBatchReadyForPayment();
        } else {
            if ($currentPhase === 'phase_1') {
                $canPay = $firstAsesmen->isBatchReadyForPayment();
            } else if ($currentPhase === 'phase_2') {
                $canPay = $firstAsesmen->isBatchReadyForPhase2Payment();
            }
        }

        $paymentStatus = $firstAsesmen->getBatchPaymentStatus();

        // Calculate total amount based on phase
        $totalAmount = 0;
        if ($canPay) {
            if ($paymentPhases === 'single') {
                $totalAmount = $asesmens->sum('fee_amount');
            } else {
                if ($currentPhase === 'phase_1') {
                    $totalAmount = $asesmens->sum('phase_1_amount');
                } else {
                    $totalAmount = $asesmens->sum('phase_2_amount');
                }
            }
        }

        // Check if all paid (untuk single phase atau both phases untuk two_phase)
        $allPaid = false;
        if ($paymentPhases === 'single') {
            $allPaid = $asesmens->every(function($asesmen) {
                return $asesmen->payments()
                    ->where('payment_phase', 'full')
                    ->where('status', 'verified')
                    ->exists();
            });
        } else {
            $allPaid = $phase1Status === 'paid' && $phase2Status === 'paid';
        }

        return view('tuk.collective.payment', [
            'asesmens' => $asesmens,
            'batchId' => $batchId,
            'currentPhase' => $currentPhase,
            'paymentPhases' => $paymentPhases,
            'phase1Status' => $phase1Status,
            'phase2Status' => $phase2Status,
            'totalAmount' => $totalAmount,
            'tuk' => $tuk,
            'canPay' => $canPay,
            'paymentStatus' => $paymentStatus,
            'allPaid' => $allPaid,
        ]);
    }

    /**
     * Collective Payment - Create Snap Token
     */
    public function createCollectiveSnapToken(Request $request, $batchId)
    {
        $tuk = auth()->user()->tuk;
        $phase = $request->input('phase', 'full');

        // Get all asesmens in this batch
        $asesmens = Asesmen::with('skema')
            ->where('collective_batch_id', $batchId)
            ->where('tuk_id', $tuk->id)
            ->get();

        if ($asesmens->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada asesi yang ditemukan dalam batch ini'
            ], 400);
        }

        $firstAsesmen = $asesmens->first();

        // Check if ready for payment based on phase
        $canPay = false;
        if ($phase === 'phase_1' || $phase === 'full') {
            $canPay = $firstAsesmen->isBatchReadyForPayment();
        } else if ($phase === 'phase_2') {
            $canPay = $firstAsesmen->isBatchReadyForPhase2Payment();
        }

        if (!$canPay) {
            return response()->json([
                'success' => false,
                'message' => 'Batch belum siap untuk pembayaran fase ' . $phase
            ], 400);
        }

        // Midtrans configuration
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');

        // Determine amount per person based on phase
        $amountPerPerson = 0;
        $paymentPhases = $firstAsesmen->payment_phases ?? 'single';
        
        if ($paymentPhases === 'single' || $phase === 'full') {
            $phase = 'full';
            $amountPerPerson = $firstAsesmen->fee_amount;
        } else if ($phase === 'phase_1') {
            $amountPerPerson = $firstAsesmen->phase_1_amount;
        } else if ($phase === 'phase_2') {
            $amountPerPerson = $firstAsesmen->phase_2_amount;
        }

        // Calculate total
        $totalAmount = $amountPerPerson * $asesmens->count();

        // Generate order ID with phase
        $orderId = 'BATCH-' . str_replace(['BATCH-', '-'], '', $batchId) . '-' . $phase . '-' . time();

        // Transaction details
        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => (int) $totalAmount,
        ];

        // Customer details (use TUK info)
        $customerDetails = [
            'first_name' => $tuk->name,
            'email' => $tuk->email,
            'phone' => $tuk->phone ?? '081234567890',
        ];

        // Item details - FIXED: Use actual amount per person
        $itemDetails = [];
        foreach ($asesmens as $asesmen) {
            $itemDetails[] = [
                'id' => 'CERT-' . $asesmen->id . '-' . $phase,
                'price' => (int) $amountPerPerson,
                'quantity' => 1,
                'name' => Str::limit($asesmen->full_name . ' - ' . $asesmen->skema->name . ' (' . strtoupper($phase) . ')', 50),
            ];
        }

        // Transaction params
        $params = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
            'callbacks' => [
                'finish' => route('tuk.collective.payment.finish', $batchId),
            ]
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // Create payment records for each asesmen
            foreach ($asesmens as $asesmen) {
                Payment::updateOrCreate(
                    [
                        'asesmen_id' => $asesmen->id,
                        'payment_phase' => $phase
                    ],
                    [
                        'amount' => $amountPerPerson,
                        'method' => 'midtrans',
                        'status' => 'pending',
                        'notes' => "Pembayaran kolektif {$phase} - Batch: {$batchId}",
                        'order_id' => $orderId,
                    ]
                );
            }

            Log::info("Collective payment initiated - Order: {$orderId}, Batch: {$batchId}, Phase: {$phase}, Count: {$asesmens->count()}, Total: {$totalAmount}");

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $orderId,
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans Collective Payment Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat token pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Collective Payment Finish
     */
    public function collectivePaymentFinish($batchId)
    {
        $tuk = auth()->user()->tuk;
        
        $asesmens = Asesmen::with(['payment', 'skema', 'user'])
            ->where('collective_batch_id', $batchId)
            ->where('tuk_id', $tuk->id)
            ->get();

        $paymentStatus = $asesmens->first()->getBatchPaymentStatus();

        return view('tuk.collective.payment-finish', compact('asesmens', 'batchId', 'tuk', 'paymentStatus'));
    }

    /**
     * Check Collective Payment Status
     */
    public function checkCollectivePaymentStatus(Request $request, $batchId)
    {
        $tuk = auth()->user()->tuk;
        
        // Get all asesmens in this batch
        $asesmens = Asesmen::with('payments')
            ->where('collective_batch_id', $batchId)
            ->where('tuk_id', $tuk->id)
            ->get();

        if ($asesmens->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Batch tidak ditemukan'
            ], 404);
        }

        // Get the most recent payment (could be phase_1, phase_2, or full)
        $latestPayment = Payment::whereIn('asesmen_id', $asesmens->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->first();
        
        if (!$latestPayment || !$latestPayment->order_id) {
            return response()->json([
                'success' => false,
                'message' => 'Payment belum dibuat'
            ], 404);
        }

        $orderId = $latestPayment->order_id;
        $phase = $latestPayment->payment_phase;

        try {
            // Configure Midtrans
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
            \Midtrans\Config::$is3ds = config('midtrans.is_3ds');

            // Check status dari Midtrans
            $status = \Midtrans\Transaction::status($orderId);
            
            Log::info("Checking collective payment status - Order: {$orderId}, Phase: {$phase}, Status: {$status->transaction_status}");

            // Update all payments in batch if settlement/capture
            if ($status->transaction_status === 'settlement' || 
                ($status->transaction_status === 'capture' && isset($status->fraud_status) && $status->fraud_status === 'accept')) {
                
                DB::beginTransaction();
                try {
                    $updatedCount = 0;
                    
                    foreach ($asesmens as $asesmen) {
                        // Update payment for this phase
                        $payment = $asesmen->payments()
                            ->where('payment_phase', $phase)
                            ->where('order_id', $orderId)
                            ->first();
                        
                        if ($payment) {
                            $payment->update([
                                'status' => 'verified',
                                'verified_at' => now(),
                                'transaction_id' => $status->transaction_id ?? $orderId,
                                'payment_type' => $status->payment_type ?? 'unknown',
                                'notes' => "Pembayaran kolektif {$phase} berhasil - Auto verified"
                            ]);

                            // Update asesmen status
                            if ($phase === 'full' || $phase === 'phase_1') {
                                $asesmen->update(['status' => 'paid']);
                            }
                            // For phase_2, keep status as assessed/certified
                            
                            $updatedCount++;
                        }
                    }
                    
                    DB::commit();

                    Log::info("Collective payment verified - Order: {$orderId}, Phase: {$phase}, Updated: {$updatedCount} payments");

                    return response()->json([
                        'success' => true,
                        'status' => 'verified',
                        'phase' => $phase,
                        'message' => "Pembayaran {$phase} berhasil! {$updatedCount} peserta telah diupdate.",
                        'updated_count' => $updatedCount
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error updating collective payments: ' . $e->getMessage());
                    throw $e;
                }
            } else {
                // Return current status
                return response()->json([
                    'success' => true,
                    'status' => $status->transaction_status,
                    'phase' => $phase,
                    'message' => 'Status pembayaran: ' . $status->transaction_status
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error checking collective payment status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Schedule via AJAX
     */
    public function deleteScheduleAjax(Request $request, Schedule $schedule)
    {
        $tuk = auth()->user()->tuk;
        
        // Verify schedule belongs to this TUK
        if ($schedule->asesmen->tuk_id != $tuk->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Update asesmen status back to 'paid'
            $schedule->asesmen->update(['status' => 'paid']);
            
            $asesmenName = $schedule->asesmen->full_name ?? $schedule->asesmen->user->name;
            $schedule->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Jadwal untuk {$asesmenName} berhasil dihapus!"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete Schedule Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Schedule via AJAX
     */
    public function updateScheduleAjax(Request $request, Schedule $schedule)
    {
        $tuk = auth()->user()->tuk;
        
        // Verify schedule belongs to this TUK
        if ($schedule->asesmen->tuk_id != $tuk->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'assessment_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'location' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            $schedule->update([
                'assessment_date' => $request->assessment_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'location' => $request->location,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil diupdate!',
                'schedule' => [
                    'id' => $schedule->id,
                    'assessment_date' => $schedule->assessment_date->format('d F Y'),
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'location' => $schedule->location,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Update Schedule Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export Schedule Batch to Excel
     */
    public function exportScheduleBatch(Request $request, $groupKey)
    {
        $tuk = auth()->user()->tuk;
        
        // Decode the group key
        $groupData = $request->input('group_data');
        
        if (!$groupData) {
            return response()->json([
                'success' => false,
                'message' => 'Group data tidak ditemukan'
            ], 400);
        }
        
        // Parse group data
        $groupArray = explode('|', $groupData);
        if (count($groupArray) !== 4) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid group data format'
            ], 400);
        }
        
        list($date, $startTime, $endTime, $location) = $groupArray;
        
        // Get all schedules in this group
        $schedules = Schedule::with(['asesmen'])
            ->whereHas('asesmen', function($q) use ($tuk) {
                $q->where('tuk_id', $tuk->id);
            })
            ->whereDate('assessment_date', $date)
            ->where('start_time', $startTime)
            ->where('end_time', $endTime)
            ->where('location', $location)
            ->orderBy('assessment_date', 'asc')
            ->get();
        
        if ($schedules->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada jadwal yang ditemukan'
            ], 404);
        }
        
        // Prepare group info for Excel
        $groupInfo = [
            'date' => \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMMM Y'),
            'time' => $startTime . ' - ' . $endTime . ' WIB',
            'location' => $location,
        ];
        
        try {
            $scheduleIds = $schedules->pluck('id')->toArray();
            
            // Generate filename
            $filename = 'Daftar_Asesmen_' . \Carbon\Carbon::parse($date)->format('Ymd') . '_' . str_replace(':', '', $startTime) . '.xlsx';
            
            // Return Excel download
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ScheduleExport($scheduleIds, $groupInfo),
                $filename
            );
            
        } catch (\Exception $e) {
            Log::error('Export Schedule Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error saat export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download template for collective registration
     */
    public function downloadTemplate($type = 'excel')
    {
        $headers = [
            ['Nama Lengkap', 'Email'],
            ['John Doe', 'john@example.com'],
            ['Jane Smith', 'jane@example.com'],
            ['Bob Wilson', 'bob@example.com'],
        ];

        if ($type === 'csv') {
            // Generate CSV
            $filename = 'Template_Peserta_Kolektif.csv';
            
            $callback = function() use ($headers) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for Excel UTF-8 compatibility
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                foreach ($headers as $row) {
                    fputcsv($file, $row);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } else {
            // Generate Excel
            return Excel::download(new class implements 
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithHeadings,
                \Maatwebsite\Excel\Concerns\WithStyles
            {
                public function array(): array
                {
                    return [
                        ['John Doe', 'john@example.com'],
                        ['Jane Smith', 'jane@example.com'],
                        ['Bob Wilson', 'bob@example.com'],
                    ];
                }
                
                public function headings(): array
                {
                    return ['Nama Lengkap', 'Email'];
                }
                
                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
                {
                    return [
                        1 => [
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '4472C4']
                            ],
                        ],
                    ];
                }
            }, 'Template_Peserta_Kolektif.xlsx');
        }
    }

    /**
     * Parse uploaded participants file
     */
    public function parseParticipantsFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB max
        ]);

        try {
            $file = $request->file('file');
            
            // Import and parse
            $import = new ParticipantsImport();
            Excel::import($import, $file);
            
            // Check for errors
            if (!$import->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terdapat data yang tidak valid',
                    'errors' => $import->getErrors(),
                    'data' => $import->data,
                ], 422);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'File berhasil diparse',
                'data' => $import->data,
                'count' => count($import->data),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Parse Participants File Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file: ' . $e->getMessage(),
            ], 500);
        }
    }         
    
    /**
     * Download Collective Invoice
     */
    public function downloadCollectiveInvoice($batchId)
    {
        $tuk = auth()->user()->tuk;
        
        // Get all asesmens in this batch
        $asesmens = Asesmen::with(['skema', 'payments'])
            ->where('collective_batch_id', $batchId)
            ->where('tuk_id', $tuk->id)
            ->get();

        if ($asesmens->isEmpty()) {
            return redirect()->route('tuk.asesi')
                ->with('error', 'Batch tidak ditemukan');
        }

        // Get the latest verified payment
        $payment = Payment::whereIn('asesmen_id', $asesmens->pluck('id'))
            ->where('status', 'verified')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$payment) {
            return redirect()->back()
                ->with('error', 'Belum ada pembayaran yang terverifikasi untuk batch ini');
        }

        // Generate invoice data
        $invoiceNumber = 'INV-BATCH-' . $payment->order_id;
        $isCollective = true;
        $phase = $payment->payment_phase ?? 'full';
        $asesmen = $asesmens->first(); // For compatibility

        // Generate PDF
        $pdf = Pdf::loadView('pdf.invoice', compact(
            'payment',
            'invoiceNumber',
            'isCollective',
            'phase',
            'asesmen',
            'asesmens',
            'batchId',
            'tuk'
        ));

        $filename = 'Invoice_Kolektif_' . $batchId . '_' . date('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Show Batch Detail
     */
    public function batchDetail($batchId)
    {
        $tuk = auth()->user()->tuk;
        
        // Get all asesmens in this batch
        $asesmens = Asesmen::with(['user', 'skema', 'payments'])
            ->where('collective_batch_id', $batchId)
            ->where('tuk_id', $tuk->id)
            ->get();

        if ($asesmens->isEmpty()) {
            return redirect()->route('tuk.asesi')
                ->with('error', 'Batch tidak ditemukan');
        }

        $firstAsesmen = $asesmens->first();
        
        // Get payment phases
        $paymentPhases = $firstAsesmen->payment_phases ?? 'single';
        
        // Check payment status
        $phase1Status = 'not_paid';
        $phase2Status = 'not_paid';
        
        if ($paymentPhases === 'two_phase') {
            $phase1Paid = $asesmens->every(function($asesmen) {
                return $asesmen->payments()
                    ->where('payment_phase', 'phase_1')
                    ->where('status', 'verified')
                    ->exists();
            });
            
            $phase2Paid = $asesmens->every(function($asesmen) {
                return $asesmen->payments()
                    ->where('payment_phase', 'phase_2')
                    ->where('status', 'verified')
                    ->exists();
            });
            
            if ($phase1Paid) $phase1Status = 'paid';
            if ($phase2Paid) $phase2Status = 'paid';
        }
        
        // Determine pending phase
        $pendingPhase = null;
        if ($paymentPhases === 'two_phase') {
            if ($phase1Status !== 'paid' && $firstAsesmen->status === 'verified') {
                $pendingPhase = 'phase_1';
            } elseif ($phase1Status === 'paid' && $phase2Status !== 'paid' && 
                      $asesmens->every(fn($a) => in_array($a->status, ['assessed', 'certified']))) {
                $pendingPhase = 'phase_2';
            }
        } else {
            if ($firstAsesmen->status === 'verified' && 
                !$asesmens->every(fn($a) => $a->payments()->where('payment_phase', 'full')->where('status', 'verified')->exists())) {
                $pendingPhase = 'full';
            }
        }
        
        // Get payment status
        $paymentStatus = $firstAsesmen->getBatchPaymentStatus();
        
        // Check if has verified payment
        $hasVerifiedPayment = $asesmens->flatMap->payments->where('status', 'verified')->isNotEmpty();
        
        // Get all payments for this batch
        $payments = Payment::whereIn('asesmen_id', $asesmens->pluck('id'))
            ->where('status', 'verified')
            ->orderBy('verified_at', 'desc')
            ->get()
            ->unique('payment_phase');
        
        // Calculate total amount
        $totalAmount = 0;
        if ($paymentPhases === 'single') {
            $totalAmount = $asesmens->sum('fee_amount');
        } else {
            $totalAmount = $asesmens->sum('phase_1_amount') + $asesmens->sum('phase_2_amount');
        }
        
        return view('tuk.batch.detail', compact(
            'asesmens',
            'batchId',
            'firstAsesmen',
            'paymentPhases',
            'phase1Status',
            'phase2Status',
            'pendingPhase',
            'paymentStatus',
            'hasVerifiedPayment',
            'payments',
            'totalAmount',
            'tuk'
        ));
    }
}