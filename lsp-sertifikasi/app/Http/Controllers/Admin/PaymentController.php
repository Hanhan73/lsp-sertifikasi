<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['asesmen.user', 'asesmen.tuk', 'asesmen.skema'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('verification')) {
            if ($request->verification === 'auto') {
                $query->where('status', 'verified')->whereNull('verified_by');
            } elseif ($request->verification === 'manual') {
                $query->where('status', 'verified')->whereNotNull('verified_by');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('asesmen', function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $payments = $query->paginate(15)->withQueryString();

        $summary = [
            'pending'         => Payment::where('status', 'pending')->count(),
            'auto_verified'   => Payment::where('status', 'verified')->whereNull('verified_by')->count(),
            'manual_verified' => Payment::where('status', 'verified')->whereNotNull('verified_by')->count(),
            'rejected'        => Payment::where('status', 'rejected')->count(),
        ];

        return view('admin.payments.index', compact('payments', 'summary'));
    }

    /**
     * Halaman detail pembayaran (bukan AJAX modal).
     */
    public function show(Payment $payment)
    {
        $payment->load(['asesmen.user', 'asesmen.tuk', 'asesmen.skema', 'verifier']);

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Download / tampilkan bukti transfer via Laravel (bypass 403 symlink).
     */
    public function downloadBukti(Payment $payment)
    {
        abort_unless($payment->proof_path, 404, 'Tidak ada bukti transfer.');

        // Disk public (storage/app/public) — tempat asesi upload
        if (Storage::disk('public')->exists($payment->proof_path)) {
            $mime = Storage::disk('public')->mimeType($payment->proof_path);
            // Tampilkan inline untuk gambar/PDF supaya bisa langsung dilihat
            return response(Storage::disk('public')->get($payment->proof_path), 200, [
                'Content-Type'        => $mime,
                'Content-Disposition' => 'inline; filename="' . basename($payment->proof_path) . '"',
            ]);
        }

        // Fallback: path absolut langsung (untuk Hostinger non-symlink)
        $absPath = public_path($payment->proof_path);
        if (file_exists($absPath)) {
            return response()->file($absPath);
        }

        abort(404, 'File bukti transfer tidak ditemukan.');
    }

    /**
     * Verifikasi manual (backup dari Midtrans webhook).
     */
    public function verify(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes'  => 'nullable|string',
        ]);

        if ($payment->status === 'verified' && $payment->verified_by === null) {
            return redirect()->route('admin.payments.index')
                ->with('warning', 'Pembayaran sudah terverifikasi otomatis oleh sistem.');
        }

        $payment->update([
            'status'      => $request->status,
            'notes'       => trim(($payment->notes ?? '') . ' | Admin: ' . $request->notes),
            'verified_by' => auth()->id(),
            'verified_at' => $payment->verified_at ?? now(),
        ]);

        if ($request->status === 'verified') {
            $payment->asesmen->update(['status' => 'paid']);
        }

        $label = $request->status === 'verified' ? 'diverifikasi' : 'ditolak';

        return redirect()->route('admin.payments.show', $payment)
            ->with('success', "Pembayaran berhasil {$label}.");
    }
}