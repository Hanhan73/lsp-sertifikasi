<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\CollectivePayment;
use App\Models\Invoice;
use App\Models\Tuk;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoiceKolektifController extends Controller
{
    // =========================================================================
    // Index — daftar semua batch kolektif
    // =========================================================================

    public function index()
    {
        // Ambil semua batch kolektif unik
        $batches = Asesmen::where('is_collective', true)
            ->select('collective_batch_id', 'tuk_id',
                DB::raw('COUNT(*) as jumlah_asesi'),
                DB::raw('MIN(registration_date) as tanggal_daftar'))
            ->groupBy('collective_batch_id', 'tuk_id')
            ->with('tuk')
            ->orderByDesc('tanggal_daftar')
            ->get();

        // Map dengan status invoice masing-masing batch
        $batches = $batches->map(function ($b) {
            $b->invoice = Invoice::where('batch_id', $b->collective_batch_id)->first();
            return $b;
        });

        return view('mankeu.invoice-kolektif.index', compact('batches'));
    }

    // =========================================================================
    // Show — detail batch + form buat invoice
    // =========================================================================

    public function show($batchId)
    {
        $asesmens = Asesmen::where('collective_batch_id', $batchId)
            ->with(['skema', 'tuk'])
            ->get();

        abort_if($asesmens->isEmpty(), 404);

        $tuk     = $asesmens->first()->tuk;
        $invoice = Invoice::where('batch_id', $batchId)->first();

        // Group by skema untuk tabel invoice
        $skemaGroups = $asesmens->groupBy('skema_id')->map(function ($group) {
            $skema = $group->first()->skema;
            return [
                'skema_id'    => $skema->id,
                'skema_name'  => $skema->name,
                'jumlah'      => $group->count(),
                'harga_satuan' => $skema->fee ?? 0, // default dari skema, bisa di-override
                'subtotal'    => 0,
            ];
        })->values();

        // Kalau sudah ada invoice, pakai items dari invoice
        if ($invoice) {
            $skemaGroups = collect($invoice->items);
            $collectivePayments = $invoice->collectivePayments;
        } else {
            $collectivePayments = collect();
        }

        return view('mankeu.invoice-kolektif.show', compact(
            'asesmens', 'tuk', 'batchId', 'invoice', 'skemaGroups', 'collectivePayments'
        ));
    }

    // =========================================================================
    // Store — buat invoice baru
    // =========================================================================

    public function store(Request $request)
    {
        $request->validate([
            'batch_id'           => 'required|string',
            'recipient_name'     => 'required|string|max:255',
            'recipient_address'  => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.skema_id'   => 'required|integer',
            'items.*.skema_name' => 'required|string',
            'items.*.jumlah'     => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'notes'              => 'nullable|string',
        ]);

        // Cek belum ada invoice untuk batch ini
        abort_if(
            Invoice::where('batch_id', $request->batch_id)->exists(),
            422,
            'Invoice sudah pernah dibuat untuk batch ini.'
        );

        $asesmens = Asesmen::where('collective_batch_id', $request->batch_id)->get();
        abort_if($asesmens->isEmpty(), 404);

        // Hitung items + total
        $items = collect($request->items)->map(function ($item) {
            $item['subtotal'] = (float) $item['harga_satuan'] * (int) $item['jumlah'];
            return $item;
        })->toArray();

        $total = collect($items)->sum('subtotal');

        // Generate nomor invoice (dalam transaksi agar tidak race condition)
        $invoice = DB::transaction(function () use ($request, $items, $total, $asesmens) {
            $numberData = Invoice::generateNumber();

            return Invoice::create([
                'invoice_number'    => $numberData['invoice_number'],
                'sequence_number'   => $numberData['sequence_number'],
                'invoice_year'      => $numberData['invoice_year'],
                'batch_id'          => $request->batch_id,
                'tuk_id'            => $asesmens->first()->tuk_id,
                'issued_by'         => Auth::id(),
                'issued_at'         => now(),
                'recipient_name'    => $request->recipient_name,
                'recipient_address' => $request->recipient_address,
                'items'             => $items,
                'total_amount'      => $total,
                'notes'             => $request->notes,
                'status'            => 'draft',
            ]);
        });

        return redirect()->route('mankeu.invoice-kolektif.show', $request->batch_id)
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' berhasil dibuat.');
    }

    // =========================================================================
    // Update — edit invoice (hanya jika masih draft)
    // =========================================================================

    public function update(Request $request, Invoice $invoice)
    {
        abort_if($invoice->status !== 'draft', 403, 'Invoice yang sudah dikirim tidak dapat diubah.');

        $request->validate([
            'recipient_name'       => 'required|string|max:255',
            'recipient_address'    => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.skema_id'     => 'required|integer',
            'items.*.skema_name'   => 'required|string',
            'items.*.jumlah'       => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'notes'                => 'nullable|string',
        ]);

        $items = collect($request->items)->map(function ($item) {
            $item['subtotal'] = (float) $item['harga_satuan'] * (int) $item['jumlah'];
            return $item;
        })->toArray();

        $total = collect($items)->sum('subtotal');

        $invoice->update([
            'recipient_name'    => $request->recipient_name,
            'recipient_address' => $request->recipient_address,
            'items'             => $items,
            'total_amount'      => $total,
            'notes'             => $request->notes,
        ]);

        return redirect()->route('mankeu.invoice-kolektif.show', $invoice->batch_id)
            ->with('success', 'Invoice berhasil diperbarui.');
    }

    // =========================================================================
    // Send — ubah status draft → sent
    // =========================================================================

    public function send(Invoice $invoice)
    {
        abort_if($invoice->status !== 'draft', 403);
        $invoice->update(['status' => 'sent']);

        return redirect()->route('mankeu.invoice-kolektif.show', $invoice->batch_id)
            ->with('success', 'Invoice telah dikirim ke TUK.');
    }

    // =========================================================================
    // Download PDF Invoice
    // =========================================================================

    public function downloadPdf(Invoice $invoice)
    {
        $pdf = Pdf::loadView('pdf.invoice-kolektif', compact('invoice'))
            ->setPaper('A4', 'portrait');

        $filename = 'Invoice_' . str_replace('/', '-', $invoice->invoice_number) . '.pdf';

        return $pdf->download($filename);
    }

    // =========================================================================
    // Download PDF Kwitansi (2 versi: kosong / berisi)
    // =========================================================================

    public function downloadKwitansi(Invoice $invoice, Request $request)
    {
        $versi = $request->query('versi', 'kosong'); // 'kosong' atau 'berisi'

        $collectivePayment = null;
        if ($request->has('payment_id')) {
            $collectivePayment = CollectivePayment::findOrFail($request->payment_id);
            abort_if($collectivePayment->invoice_id !== $invoice->id, 403);
        }

        $pdf = Pdf::loadView('pdf.kwitansi-kolektif', compact('invoice', 'collectivePayment', 'versi'))
            ->setPaper('A4', 'portrait');

        $angsuranLabel = $collectivePayment ? '-Angsuran' . $collectivePayment->installment_number : '';
        $filename = 'Kwitansi_' . str_replace('/', '-', $invoice->invoice_number) . $angsuranLabel . '_' . $versi . '.pdf';

        return $pdf->download($filename);
    }

    // =========================================================================
    // Angsuran: tambah angsuran baru
    // =========================================================================

    public function storeAngsuran(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount'   => 'required|numeric|min:1',
            'due_date' => 'nullable|date',
            'notes'    => 'nullable|string',
        ]);

        $currentCount = $invoice->collectivePayments()->count();
        abort_if($currentCount >= 3, 422, 'Maksimal 3 angsuran per invoice.');

        // Validasi total angsuran tidak melebihi total invoice
        $alreadyAllocated = $invoice->collectivePayments()->sum('amount');
        abort_if(
            ($alreadyAllocated + $request->amount) > $invoice->total_amount,
            422,
            'Total angsuran melebihi total invoice (Rp ' . number_format($invoice->total_amount, 0, ',', '.') . ').'
        );

        CollectivePayment::create([
            'invoice_id'          => $invoice->id,
            'batch_id'            => $invoice->batch_id,
            'tuk_id'              => $invoice->tuk_id,
            'installment_number'  => $currentCount + 1,
            'amount'              => $request->amount,
            'due_date'            => $request->due_date,
            'notes'               => $request->notes,
            'status'              => 'pending',
        ]);

        return redirect()->route('mankeu.invoice-kolektif.show', $invoice->batch_id)
            ->with('success', 'Angsuran ke-' . ($currentCount + 1) . ' berhasil ditambahkan.');
    }

    // =========================================================================
    // Verifikasi bukti bayar angsuran
    // =========================================================================

    public function verifyAngsuran(Request $request, CollectivePayment $payment)
    {
        $request->validate([
            'action' => 'required|in:verify,reject',
            'notes'  => 'nullable|string',
        ]);

        abort_if($payment->status !== 'pending', 422, 'Angsuran ini sudah diproses.');
        abort_if(!$payment->proof_path, 422, 'Belum ada bukti bayar yang diupload.');

        if ($request->action === 'verify') {
            $payment->update([
                'status'      => 'verified',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            // Cek apakah semua angsuran sudah verified → update invoice jadi paid
            $invoice = $payment->invoice;
            if ($invoice->isFullyPaid()) {
                $invoice->update(['status' => 'paid']);
            }

            $msg = 'Bukti bayar angsuran ke-' . $payment->installment_number . ' telah diverifikasi.';
        } else {
            $payment->update([
                'status'           => 'rejected',
                'rejection_notes'  => $request->notes,
            ]);
            $msg = 'Bukti bayar angsuran ke-' . $payment->installment_number . ' ditolak.';
        }

        return redirect()->route('mankeu.invoice-kolektif.show', $payment->invoice->batch_id)
            ->with('success', $msg);
    }
}