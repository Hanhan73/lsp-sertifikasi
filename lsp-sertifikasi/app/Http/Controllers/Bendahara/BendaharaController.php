<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Asesmen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BendaharaController extends Controller
{
    /**
     * Dashboard bendahara — statistik ringkas.
     */
    public function dashboard()
    {
        $stats = [
            'pending'  => Payment::where('status', 'pending')->whereNotNull('proof_path')->count(),
            'verified' => Payment::where('status', 'verified')->whereMonth('verified_at', now()->month)->count(),
            'rejected' => Payment::where('status', 'rejected')->whereMonth('updated_at', now()->month)->count(),
            'total_bulan' => Payment::where('status', 'verified')
                ->whereMonth('verified_at', now()->month)
                ->sum('amount'),
        ];

        $pending = Payment::with(['asesmen.skema', 'asesmen.tuk'])
            ->where('status', 'pending')
            ->whereNotNull('proof_path')
            ->latest()
            ->take(5)
            ->get();

        return view('bendahara.dashboard', compact('stats', 'pending'));
    }

    /**
     * Daftar semua pembayaran mandiri.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['asesmen.skema', 'asesmen.tuk', 'asesmen.user', 'verifier'])
            ->whereHas('asesmen', fn($q) => $q->where('is_collective', false));

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->whereHas('asesmen', function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('nik', 'like', '%' . $request->search . '%');
            });
        }

        // Prioritaskan yang ada bukti tapi belum diverifikasi
        $payments = $query->orderByRaw("
            CASE status
                WHEN 'pending' THEN 0
                WHEN 'rejected' THEN 1
                WHEN 'verified' THEN 2
            END
        ")->orderBy('created_at', 'desc')->paginate(20);

        return view('bendahara.payments.index', compact('payments'));
    }

    /**
     * Detail + form verifikasi satu pembayaran.
     */
    public function show(Payment $payment)
    {
        $payment->load(['asesmen.skema', 'asesmen.tuk', 'asesmen.user', 'verifier']);
        return view('bendahara.payments.show', compact('payment'));
    }

    /**
     * Verifikasi (setujui) pembayaran.
     */
    public function verify(Request $request, Payment $payment)
    {
        abort_if($payment->status === 'verified', 422, 'Pembayaran sudah terverifikasi.');

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $payment->update([
            'status'      => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'notes'       => $request->notes ?? 'Diverifikasi oleh bendahara.',
        ]);

        // Update status asesmen → pra_asesmen_started agar asesi bisa lanjut
        // Catatan: Admin tetap perlu memulai asesmen secara formal,
        // tapi kita tandai pembayaran sudah OK supaya flow tidak terblokir.
        $asesmen = $payment->asesmen;
        if ($asesmen->status === 'payment_pending') {
            $asesmen->update(['status' => 'data_completed']);
        }

        Log::info('[PAYMENT-VERIFY] Bendahara verifikasi pembayaran', [
            'payment_id' => $payment->id,
            'asesmen_id' => $payment->asesmen_id,
            'by'         => auth()->id(),
        ]);

        return redirect()->route('bendahara.payments.index')
            ->with('success', "Pembayaran #{$payment->id} atas nama {$payment->asesmen->full_name} berhasil diverifikasi.");
    }

    /**
     * Tolak pembayaran dengan alasan.
     */
    public function reject(Request $request, Payment $payment)
    {
        abort_if($payment->status === 'verified', 422, 'Pembayaran sudah terverifikasi, tidak bisa ditolak.');

        $request->validate([
            'rejection_notes' => 'required|string|max:500',
        ]);

        $payment->update([
            'status'          => 'rejected',
            'rejection_notes' => $request->rejection_notes,
            'verified_by'     => auth()->id(),
            'verified_at'     => now(),
        ]);

        // Kembalikan status asesmen supaya asesi bisa upload ulang
        $asesmen = $payment->asesmen;
        if (in_array($asesmen->status, ['payment_pending', 'data_completed'])) {
            $asesmen->update(['status' => 'data_completed']);
        }

        Log::info('[PAYMENT-REJECT] Bendahara tolak pembayaran', [
            'payment_id' => $payment->id,
            'asesmen_id' => $payment->asesmen_id,
            'reason'     => $request->rejection_notes,
        ]);

        return redirect()->route('bendahara.payments.index')
            ->with('warning', "Pembayaran #{$payment->id} ditolak. Asesi diminta upload ulang.");
    }

    /**
     * Download bukti pembayaran.
     */
    public function downloadBukti(Payment $payment)
    {
        abort_if(!$payment->proof_path, 404, 'Bukti tidak ditemukan.');
        abort_if(!Storage::disk('private')->exists($payment->proof_path), 404, 'File tidak ada di storage.');

        $ext      = pathinfo($payment->proof_path, PATHINFO_EXTENSION);
        $filename = 'bukti-' . $payment->asesmen->full_name . '-' . $payment->id . '.' . $ext;

        return Storage::disk('private')->download($payment->proof_path, $filename);
    }
}