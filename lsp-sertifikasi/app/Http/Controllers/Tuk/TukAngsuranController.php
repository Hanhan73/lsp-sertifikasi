<?php

namespace App\Http\Controllers\Tuk;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\CollectivePayment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TukAngsuranController extends Controller
{
    public function index()
    {
        $tuk = auth()->user()->tuk;

        $invoices = Invoice::where('tuk_id', $tuk->id)
            ->with('collectivePayments')
            ->orderByDesc('issued_at')
            ->get()
            ->map(function ($inv) {
                $inv->total_asesi = Asesmen::whereIn('collective_batch_id', $inv->batch_ids)->count();
                return $inv;
            });

        return view('tuk.invoice-kolektif.index', compact('invoices', 'tuk'));
    }

    public function show(Invoice $invoice)
    {
        $tuk = auth()->user()->tuk;
        abort_if($invoice->tuk_id !== $tuk->id, 403);

        $payments = $invoice->collectivePayments;

        $asesmens = Asesmen::whereIn('collective_batch_id', $invoice->batch_ids)
            ->with('skema')
            ->get();

        return view('tuk.invoice-kolektif.show', compact('invoice', 'payments', 'tuk', 'asesmens'));
    }

    public function pdf(Invoice $invoice)
    {
        $tuk = auth()->user()->tuk;
        abort_if($invoice->tuk_id !== $tuk->id, 403);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice-kolektif', compact('invoice'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('Invoice_' . str_replace('/', '-', $invoice->invoice_number) . '.pdf');
    }

    /**
     * TUK tambah angsuran baru + langsung upload bukti bayar
     */
    public function storeAngsuran(Request $request, Invoice $invoice)
    {
        $tuk = auth()->user()->tuk;
        abort_if($invoice->tuk_id !== $tuk->id, 403);
        abort_if($invoice->status === 'draft', 403, 'Invoice belum dikirim oleh bendahara.');

        $request->validate([
            'amount'   => 'required|numeric|min:1',
            'proof'    => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'due_date' => 'nullable|date',
            'notes'    => 'nullable|string|max:500',
        ]);

        $count = $invoice->collectivePayments()->count();

        if ($count >= 3) {
            return back()->with('error', 'Maksimal 3 angsuran per invoice.');
        }

        // Validasi total tidak melebihi invoice
        $allocated = $invoice->collectivePayments()->sum('amount');
        $remaining = $invoice->total_amount - $allocated;

        if ($request->amount > $remaining) {
            return back()->with('error',
                'Nominal melebihi sisa tagihan (Rp ' . number_format($remaining, 0, ',', '.') . ').'
            );
        }

        // Upload bukti
        $path = $request->file('proof')->store(
            'collective-payments/' . $invoice->id,
            'private'
        );

        CollectivePayment::create([
            'invoice_id'         => $invoice->id,
            'tuk_id'             => $tuk->id,
            'installment_number' => $count + 1,
            'amount'             => $request->amount,
            'due_date'           => $request->due_date,
            'notes'              => $request->notes,
            'proof_path'         => $path,
            'proof_uploaded_at'  => now(),
            'status'             => 'pending',
        ]);

        return redirect()->route('tuk.invoice-kolektif.show', $invoice)
            ->with('success', 'Angsuran ke-' . ($count + 1) . ' berhasil ditambahkan. Menunggu verifikasi bendahara.');
    }

    /**
     * TUK upload ulang bukti untuk angsuran yang ditolak
     */
    public function uploadBukti(Request $request, CollectivePayment $payment)
    {
        $tuk = auth()->user()->tuk;
        abort_if($payment->tuk_id !== $tuk->id, 403);
        abort_if($payment->status === 'verified', 422, 'Angsuran ini sudah terverifikasi.');

        $request->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($payment->proof_path) {
            Storage::disk('private')->delete($payment->proof_path);
        }

        $path = $request->file('proof')->store(
            'collective-payments/' . $payment->invoice_id,
            'private'
        );

        $payment->update([
            'proof_path'        => $path,
            'proof_uploaded_at' => now(),
            'status'            => 'pending',
            'rejection_notes'   => null,
        ]);

        return redirect()->route('tuk.invoice-kolektif.show', $payment->invoice_id)
            ->with('success', 'Bukti bayar angsuran ke-' . $payment->installment_number . ' berhasil diupload ulang.');
    }
}