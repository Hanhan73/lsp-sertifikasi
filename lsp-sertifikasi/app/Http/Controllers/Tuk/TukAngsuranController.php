<?php

namespace App\Http\Controllers\Tuk;

use App\Http\Controllers\Controller;
use App\Models\CollectivePayment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TukAngsuranController extends Controller
{
    // =========================================================================
    // Index — daftar invoice + angsuran milik TUK ini
    // =========================================================================

    public function index()
    {
        $tuk = auth()->user()->tuk;

        $invoices = Invoice::where('tuk_id', $tuk->id)
            ->with('collectivePayments')
            ->orderByDesc('issued_at')
            ->get();

        return view('tuk.invoice-kolektif.index', compact('invoices', 'tuk'));
    }

    // =========================================================================
    // Show — detail invoice + riwayat angsuran
    // =========================================================================

    public function show(Invoice $invoice)
    {
        $tuk = auth()->user()->tuk;
        abort_if($invoice->tuk_id !== $tuk->id, 403);

        $payments = $invoice->collectivePayments;

        return view('tuk.invoice-kolektif.show', compact('invoice', 'payments', 'tuk'));
    }

    // =========================================================================
    // Upload bukti bayar untuk angsuran tertentu
    // =========================================================================

    public function uploadBukti(Request $request, CollectivePayment $payment)
    {
        $tuk = auth()->user()->tuk;
        abort_if($payment->tuk_id !== $tuk->id, 403);
        abort_if($payment->status === 'verified', 422, 'Angsuran ini sudah terverifikasi.');

        $request->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Hapus bukti lama jika ada
        if ($payment->proof_path) {
            Storage::disk('private')->delete($payment->proof_path);
        }

        $path = $request->file('proof')->store(
            'collective-payments/' . $payment->invoice->batch_id,
            'private'
        );

        $payment->update([
            'proof_path'          => $path,
            'proof_uploaded_at'   => now(),
            'status'              => 'pending', // reset ke pending kalau sebelumnya rejected
            'rejection_notes'     => null,
        ]);

        return redirect()->route('tuk.invoice-kolektif.show', $payment->invoice_id)
            ->with('success', 'Bukti bayar angsuran ke-' . $payment->installment_number . ' berhasil diupload.');
    }
}