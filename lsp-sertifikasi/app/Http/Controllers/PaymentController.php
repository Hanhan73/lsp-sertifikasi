<?php

namespace App\Http\Controllers;

use App\Models\Asesmen;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Halaman form upload bukti pembayaran (asesi mandiri).
     */
    public function show()
    {
        $user    = auth()->user();
        $asesmen = Asesmen::with(['payment', 'skema', 'tuk'])
            ->where('user_id', $user->id)
            ->firstOrFail();
 
        abort_if($asesmen->is_collective, 403, 'Pembayaran kolektif dikelola oleh TUK.');
 
        // Tidak perlu bayar jika sudah pra-asesmen ke atas
        if (in_array($asesmen->status, ['pra_asesmen_started', 'scheduled', 'assessed', 'certified'])) {
            return redirect()->route('asesi.dashboard')
                ->with('info', 'Pembayaran sudah selesai diproses.');
        }
 
        // Admin belum tetapkan biaya
        if (!$asesmen->fee_amount) {
            return redirect()->route('asesi.dashboard')
                ->with('info', 'Biaya belum ditetapkan oleh Admin LSP. Silakan tunggu.');
        }
 
        $payment = $asesmen->payment;
 
        return view('asesi.payment', compact('asesmen', 'payment'));
    }
    /**
     * Upload bukti pembayaran dari asesi.
     */
     public function uploadBukti(Request $request)
    {
        $user    = auth()->user();
        $asesmen = Asesmen::with(['payment', 'skema'])
            ->where('user_id', $user->id)
            ->firstOrFail();
 
        abort_if($asesmen->is_collective, 403);
 
        // Jangan bisa upload jika sudah di tahap pra-asesmen
        if (in_array($asesmen->status, ['pra_asesmen_started', 'scheduled', 'assessed', 'certified'])) {
            return redirect()->route('asesi.dashboard')
                ->with('info', 'Pembayaran sudah selesai.');
        }
 
        $request->validate([
            'proof'  => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'method' => 'required|in:transfer,qris',
            'notes'  => 'nullable|string|max:500',
        ], [
            'proof.required' => 'Bukti pembayaran wajib diupload.',
            'proof.mimes'    => 'Format file harus JPG, PNG, atau PDF.',
            'proof.max'      => 'Ukuran file maksimal 5 MB.',
        ]);
 
        $path = $request->file('proof')->store('uploads/payment-proofs', 'private');
 
        // Buat atau update payment record
        Payment::updateOrCreate(
            ['asesmen_id' => $asesmen->id, 'payment_phase' => 'full'],
            [
                'amount'        => $asesmen->fee_amount,
                'method'        => $request->method,
                'proof_path'    => $path,
                'status'        => 'pending',
                'notes'         => $request->notes,
                'payment_phase' => 'full',
            ]
        );
 
        // Update status asesmen
        $asesmen->update(['status' => 'payment_pending']);
 
        Log::info('[PAYMENT] Asesi upload bukti', [
            'asesmen_id' => $asesmen->id,
            'method'     => $request->method,
        ]);
 
        return redirect()->route('asesi.payment.status')
            ->with('success', 'Bukti pembayaran berhasil diupload! Menunggu verifikasi bendahara.');
    }

    /**
     * Halaman status pembayaran asesi.
     */
    public function status()
    {
        $user    = auth()->user();
        $asesmen = Asesmen::with(['payment', 'skema', 'tuk'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $payment = $asesmen->payment;

        return view('asesi.payment-status', compact('asesmen', 'payment'));
    }

    /**
     * Download bukti pembayaran (untuk asesi sendiri & bendahara).
     */
    public function downloadBukti(Payment $payment)
    {
        $user = auth()->user();

        // Asesi hanya bisa download bukti milik sendiri
        if ($user->isAsesi() && $payment->asesmen->user_id !== $user->id) {
            abort(403);
        }

        // Bendahara bisa akses semua
        if (!$user->isAsesi() && !$user->isBendahara()) {
            abort(403);
        }

        abort_if(!$payment->proof_path, 404, 'Bukti tidak ditemukan.');
        abort_if(!Storage::disk('private')->exists($payment->proof_path), 404, 'File tidak ditemukan.');

        $ext      = pathinfo($payment->proof_path, PATHINFO_EXTENSION);
        $filename = 'bukti-payment-' . $payment->asesmen_id . '.' . $ext;

        return Storage::disk('private')->download($payment->proof_path, $filename);
    }
}