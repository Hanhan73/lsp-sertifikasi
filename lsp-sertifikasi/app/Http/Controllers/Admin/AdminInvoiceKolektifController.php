<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\CollectivePayment;
use App\Models\Asesmen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminInvoiceKolektifController extends Controller
{
    /**
     * Daftar semua invoice kolektif dari semua TUK.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['tuk', 'collectivePayments'])
            ->orderByDesc('issued_at');

        if ($request->filled('tuk_id')) {
            $query->where('tuk_id', $request->tuk_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->get()->map(function ($inv) {
            $inv->total_asesi = Asesmen::whereIn('collective_batch_id', $inv->batch_ids)->count();
            return $inv;
        });

        $tuks = \App\Models\Tuk::orderBy('name')->get(['id', 'name']);

        return view('admin.invoice-kolektif.index', compact('invoices', 'tuks'));
    }

    /**
     * Detail invoice — sama dengan TUK show.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['tuk', 'collectivePayments']);

        $payments = $invoice->collectivePayments()->orderBy('installment_number')->get();

        $asesmens = Asesmen::whereIn('collective_batch_id', $invoice->batch_ids)
            ->with('skema')
            ->get();

        return view('admin.invoice-kolektif.show', compact('invoice', 'payments', 'asesmens'));
    }

    /**
     * Upload bukti transfer untuk satu angsuran.
     */
    public function uploadBukti(Request $request, CollectivePayment $payment)
    {
        abort_if($payment->status === 'verified', 403, 'Angsuran sudah terverifikasi.');

        $request->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'proof.required' => 'File bukti wajib diupload.',
            'proof.mimes'    => 'Format file harus JPG, PNG, atau PDF.',
            'proof.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        // Hapus bukti lama kalau ada
        if ($payment->proof_path) {
            Storage::disk('private')->delete($payment->proof_path);
        }

        $file = $request->file('proof');
        $path = $file->store(
            'collective-payments/' . $payment->invoice_id,
            'private'
        );

        $payment->update([
            'proof_path'         => $path,
            'proof_uploaded_at'  => now(),
            'status'             => 'pending',
            'rejection_notes'    => null,
        ]);

        return redirect()->route('admin.invoice-kolektif.show', $payment->invoice)
            ->with('success', 'Bukti pembayaran angsuran ke-' . $payment->installment_number . ' berhasil diupload.');
    }

    /**
     * Download / preview bukti angsuran.
     */
    public function downloadBukti(CollectivePayment $payment, Request $request)
    {
        abort_unless($payment->proof_path && Storage::disk('private')->exists($payment->proof_path), 404);

        $ext      = strtolower(pathinfo($payment->proof_path, PATHINFO_EXTENSION));
        $isImage  = in_array($ext, ['jpg', 'jpeg', 'png']);
        $filename = 'bukti-angsuran-' . $payment->installment_number . '.' . $ext;

        if ($request->boolean('download')) {
            return Storage::disk('private')->download($payment->proof_path, $filename);
        }

        return response(Storage::disk('private')->get($payment->proof_path), 200, [
            'Content-Type'        => $isImage
                ? 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext)
                : 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Download PDF invoice.
     */
    public function pdf(Invoice $invoice)
    {
        $pdf      = Pdf::loadView('pdf.invoice-kolektif', compact('invoice'))->setPaper('A4');
        $filename = 'Invoice_' . str_replace('/', '-', $invoice->invoice_number) . '.pdf';
        return $pdf->download($filename);
    }
}