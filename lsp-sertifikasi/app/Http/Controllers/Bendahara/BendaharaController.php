<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Asesmen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Invoice;
use App\Models\CollectivePayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\JournalService;


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

        try {
            $paymentFresh = $payment->fresh(['asesmen.skema']);
            // Buat jurnal piutang dulu kalau belum ada (misal asesi belum upload bukti)
            if (!\App\Models\JournalEntry::existsFor(\App\Models\Payment::class . '_piutang', $payment->id)) {
                app(JournalService::class)->jurnalPiutangAsesi($paymentFresh);
            }
            // Jurnal pelunasan
            app(JournalService::class)->jurnalPiutangLunas($paymentFresh);
        } catch (\Exception $e) {
            \Log::warning('Gagal buat jurnal payment: ' . $e->getMessage());
        }

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

    /**
     * Halaman daftar skema + tarif honor asesor per asesi.
     */
    public function tarifHonorIndex()
    {
        $skemas = \App\Models\Skema::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'jenis_skema', 'honor_per_asesi']);

        return view('bendahara.tarif-honor.index', compact('skemas'));
    }

    /**
     * AJAX PATCH — update honor_per_asesi satu skema.
     * Hanya bendahara yang bisa akses (sudah dijaga oleh middleware role:bendahara).
     */
    public function tarifHonorUpdate(Request $request, \App\Models\Skema $skema)
    {
        $request->validate([
            'honor_per_asesi' => 'required|integer|min:0',
        ]);

        $skema->update(['honor_per_asesi' => (int) $request->honor_per_asesi]);

        return response()->json([
            'success'        => true,
            'honor_per_asesi' => $skema->honor_per_asesi,
            'message'        => 'Tarif honor berhasil disimpan.',
        ]);
    }

    // ── Invoice Kolektif ──────────────────────────────────────────────────────

    public function kolektifInvoiceShow($batchId)
    {
        $asesmens = Asesmen::where('collective_batch_id', $batchId)
            ->with(['skema', 'tuk'])
            ->get();
        abort_if($asesmens->isEmpty(), 404);

        $tuk     = $asesmens->first()->tuk;
        $invoice = Invoice::where('batch_id', $batchId)->first();

        $skemaGroups = $asesmens->groupBy('skema_id')->map(function ($group) {
            $skema = $group->first()->skema;
            return [
                'skema_id'     => $skema->id,
                'skema_name'   => $skema->name,
                'jumlah'       => $group->count(),
                'harga_satuan' => $skema->fee ?? 0,
                'subtotal'     => 0,
            ];
        })->values();

        if ($invoice) {
            $skemaGroups        = collect($invoice->items);
            $collectivePayments = $invoice->collectivePayments;
        } else {
            $collectivePayments = collect();
        }

        return view('bendahara.payments.kolektif-invoice', compact(
            'asesmens',
            'tuk',
            'batchId',
            'invoice',
            'skemaGroups',
            'collectivePayments'
        ));
    }

    /**
     * Level 1 — Daftar TUK yang punya batch kolektif
     */
    public function kolektif()
    {
        $tuks = Asesmen::where('is_collective', true)
            ->select(
                'tuk_id',
                DB::raw('COUNT(DISTINCT collective_batch_id) as jumlah_batch'),
                DB::raw('COUNT(*) as jumlah_asesi')
            )
            ->groupBy('tuk_id')
            ->with('tuk')
            ->get()
            ->map(function ($row) {
                // Invoice yang mencakup batch dari TUK ini
                $batchIds = Asesmen::where('tuk_id', $row->tuk_id)
                    ->where('is_collective', true)
                    ->pluck('collective_batch_id')
                    ->unique()
                    ->values()
                    ->toArray();

                $row->pending_invoice = Invoice::where('tuk_id', $row->tuk_id)
                    ->where('status', 'draft')
                    ->count();

                $row->pending_angsuran = CollectivePayment::whereHas(
                    'invoice',
                    fn($q) =>
                    $q->where('tuk_id', $row->tuk_id)
                )->where('status', 'pending')->whereNotNull('proof_path')->count();

                return $row;
            });

        return view('bendahara.payments.kolektif', compact('tuks'));
    }

    /**
     * Level 2 — Daftar batch + daftar invoice milik TUK ini
     * Batch yang belum masuk invoice manapun bisa dipilih untuk invoice baru
     */
    public function kolektifTuk(\App\Models\Tuk $tuk)
    {
        // Semua batch milik TUK ini
        $batches = Asesmen::where('is_collective', true)
            ->where('tuk_id', $tuk->id)
            ->select(
                'collective_batch_id',
                'tuk_id',
                DB::raw('COUNT(*) as jumlah_asesi'),
                DB::raw('MIN(registration_date) as tanggal_daftar')
            )
            ->groupBy('collective_batch_id', 'tuk_id')
            ->orderByDesc('tanggal_daftar')
            ->get()
            ->map(function ($b) {
                $b->skema_names = Asesmen::where('collective_batch_id', $b->collective_batch_id)
                    ->with('skema')->get()->pluck('skema.name')->unique()->filter()->values();

                // Cari invoice yang mengandung batch ini
                $b->invoice = Invoice::where('tuk_id', $b->tuk_id)
                    ->whereJsonContains('batch_ids', $b->collective_batch_id)
                    ->with('collectivePayments')
                    ->first();

                return $b;
            });

        // Daftar invoice yang sudah ada untuk TUK ini
        $invoices = Invoice::where('tuk_id', $tuk->id)
            ->with('collectivePayments')
            ->orderByDesc('issued_at')
            ->get()
            ->map(function ($inv) {
                $inv->batch_count  = count($inv->batch_ids);
                $inv->total_asesi  = Asesmen::whereIn('collective_batch_id', $inv->batch_ids)->count();
                $inv->skema_names  = Asesmen::whereIn('collective_batch_id', $inv->batch_ids)
                    ->with('skema')->get()->pluck('skema.name')->unique()->filter()->values();
                return $inv;
            });

        return view('bendahara.payments.kolektif-tuk', compact('tuk', 'batches', 'invoices'));
    }

    /**
     * Level 3 — Detail invoice (multi-batch)
     */
    public function kolektifDetail(Invoice $invoice)
    {
        $invoice->load('collectivePayments', 'tuk');
        $tuk = $invoice->tuk;

        // Semua asesi dari semua batch dalam invoice ini
        $asesmens = Asesmen::whereIn('collective_batch_id', $invoice->batch_ids)
            ->with(['skema', 'tuk'])
            ->get();

        $collectivePayments = $invoice->collectivePayments;

        // skemaGroups untuk form edit (hanya saat draft)
        $skemaGroups = collect($invoice->items);

        return view('bendahara.payments.kolektif-detail', compact(
            'invoice',
            'tuk',
            'asesmens',
            'skemaGroups',
            'collectivePayments'
        ));
    }

    /**
     * Bulk store — buat 1 invoice dari beberapa batch terpilih
     */
    public function kolektifInvoiceStoreBulk(Request $request, \App\Models\Tuk $tuk)
    {
        $request->validate([
            'batch_ids'   => 'required|array|min:1',
            'batch_ids.*' => 'required|string',
        ]);

        $batchIds = $request->batch_ids;

        // Validasi: semua batch harus milik TUK ini
        $validBatches = Asesmen::where('tuk_id', $tuk->id)
            ->where('is_collective', true)
            ->whereIn('collective_batch_id', $batchIds)
            ->pluck('collective_batch_id')
            ->unique()
            ->values()
            ->toArray();

        if (empty($validBatches)) {
            return back()->with('error', 'Tidak ada batch valid yang dipilih.');
        }

        // Cek batch yang sudah masuk invoice lain
        $alreadyInvoiced = [];
        foreach ($validBatches as $batchId) {
            if (Invoice::where('tuk_id', $tuk->id)
                ->whereJsonContains('batch_ids', $batchId)
                ->exists()
            ) {
                $alreadyInvoiced[] = $batchId;
            }
        }

        $toProcess = array_diff($validBatches, $alreadyInvoiced);

        if (empty($toProcess)) {
            return back()->with('error', 'Semua batch yang dipilih sudah memiliki invoice.');
        }

        // Build items — group by skema dari semua batch terpilih
        $asesmens = Asesmen::whereIn('collective_batch_id', $toProcess)
            ->where('tuk_id', $tuk->id)
            ->with('skema')
            ->get();

        $items = $asesmens->groupBy('skema_id')->map(function ($group) {
            $skema  = $group->first()->skema;
            $jumlah = $group->count();
            $harga  = (float) ($skema->fee ?? 0);
            return [
                'skema_id'     => $skema->id,
                'skema_name'   => $skema->name,
                'jumlah'       => $jumlah,
                'harga_satuan' => $harga,
                'subtotal'     => $harga * $jumlah,
            ];
        })->values()->toArray();

        $total = collect($items)->sum('subtotal');

        $invoice = DB::transaction(function () use ($tuk, $toProcess, $items, $total) {
            $n = Invoice::generateNumber();
            return Invoice::create([
                'invoice_number'    => $n['invoice_number'],
                'sequence_number'   => $n['sequence_number'],
                'invoice_year'      => $n['invoice_year'],
                'batch_ids'         => array_values($toProcess),
                'tuk_id'            => $tuk->id,
                'issued_by'         => Auth::id(),
                'issued_at'         => now(),
                'recipient_name'    => $tuk->name,
                'recipient_address' => $tuk->address ?? '',
                'items'             => $items,
                'total_amount'      => $total,
                'notes'             => null,
                'status'            => 'draft',
            ]);
        });

        $msg = 'Invoice ' . $invoice->invoice_number . ' berhasil dibuat untuk '
            . count($toProcess) . ' batch.';

        if (!empty($alreadyInvoiced)) {
            $msg .= ' ' . count($alreadyInvoiced) . ' batch dilewati (sudah ada invoice).';
        }

        return redirect()->route('bendahara.payments.kolektif.detail', $invoice)
            ->with('success', $msg);
    }

    /**
     * Store invoice manual (single atau multi-batch via form detail)
     */
    public function kolektifInvoiceStore(Request $request)
    {
        $request->validate([
            'batch_ids'              => 'required|array|min:1',
            'batch_ids.*'            => 'required|string',
            'tuk_id'                 => 'required|integer|exists:tuks,id',
            'recipient_name'         => 'required|string|max:255',
            'recipient_address'      => 'nullable|string',
            'items'                  => 'required|array|min:1',
            'items.*.skema_id'       => 'required|integer',
            'items.*.skema_name'     => 'required|string',
            'items.*.jumlah'         => 'required|integer|min:1',
            'items.*.harga_satuan'   => 'required|numeric|min:0',
            'notes'                  => 'nullable|string',
        ]);

        $tuk = \App\Models\Tuk::findOrFail($request->tuk_id);

        $items = collect($request->items)->map(function ($item) {
            $item['subtotal'] = (float) $item['harga_satuan'] * (int) $item['jumlah'];
            return $item;
        })->toArray();

        $invoice = DB::transaction(function () use ($request, $items, $tuk) {
            $n = Invoice::generateNumber();
            return Invoice::create([
                'invoice_number'    => $n['invoice_number'],
                'sequence_number'   => $n['sequence_number'],
                'invoice_year'      => $n['invoice_year'],
                'batch_ids'         => $request->batch_ids,
                'tuk_id'            => $tuk->id,
                'issued_by'         => Auth::id(),
                'issued_at'         => now(),
                'recipient_name'    => $request->recipient_name,
                'recipient_address' => $request->recipient_address,
                'items'             => $items,
                'total_amount'      => collect($items)->sum('subtotal'),
                'notes'             => $request->notes,
                'status'            => 'draft',
            ]);
        });

        return redirect()->route('bendahara.payments.kolektif.detail', $invoice)
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' berhasil dibuat.');
    }

    public function kolektifInvoiceUpdate(Request $request, Invoice $invoice)
    {
        abort_if($invoice->status !== 'draft', 403, 'Invoice sudah dikirim, tidak bisa diubah.');

        $request->validate([
            'recipient_name'         => 'required|string|max:255',
            'recipient_address'      => 'nullable|string',
            'items'                  => 'required|array|min:1',
            'items.*.skema_id'       => 'required|integer',
            'items.*.skema_name'     => 'required|string',
            'items.*.jumlah'         => 'required|integer|min:1',
            'items.*.harga_satuan'   => 'required|numeric|min:0',
            'notes'                  => 'nullable|string',
            'notes_kwitansi'         => 'nullable|string|max:500', // ← tambah
        ]);

        $items = collect($request->items)->map(function ($item) {
            $item['subtotal'] = (float) $item['harga_satuan'] * (int) $item['jumlah'];
            return $item;
        })->toArray();

        $invoice->update([
            'recipient_name'    => $request->recipient_name,
            'recipient_address' => $request->recipient_address,
            'items'             => $items,
            'total_amount'      => collect($items)->sum('subtotal'),
            'notes'             => $request->notes,
            'notes_kwitansi'    => $request->notes_kwitansi, // ← tambah
        ]);

        return redirect()->route('bendahara.payments.kolektif.detail', $invoice)
            ->with('success', 'Invoice berhasil diperbarui.');
    }
    
    public function kolektifInvoiceSend(Invoice $invoice)
    {
        abort_if($invoice->status !== 'draft', 403);
        $invoice->update(['status' => 'sent']);

        // Inject jurnal piutang kolektif
        try {
            if (!\App\Models\JournalEntry::existsFor(\App\Models\Invoice::class . '_piutang', $invoice->id)) {
                app(JournalService::class)->jurnalPiutangInvoice($invoice);
            }
        } catch (\Exception $e) {
            \Log::warning('Gagal buat jurnal piutang invoice: ' . $e->getMessage());
        }

        return redirect()->route('bendahara.payments.kolektif.detail', $invoice)
            ->with('success', 'Invoice telah dikirim ke TUK.');
    }

    public function kolektifInvoicePdf(Invoice $invoice)
    {
        $pdf      = Pdf::loadView('pdf.invoice-kolektif', compact('invoice'))->setPaper('A4');
        $filename = 'Invoice_' . str_replace('/', '-', $invoice->invoice_number) . '.pdf';
        return $pdf->download($filename);
    }

    public function kolektifKwitansi(Invoice $invoice, Request $request)
    {
        $versi             = $request->query('versi', 'kosong');
        $collectivePayment = $request->has('payment_id')
            ? CollectivePayment::findOrFail($request->payment_id)
            : null;

        $pdf      = Pdf::loadView('pdf.kwitansi-kolektif', compact('invoice', 'collectivePayment', 'versi'))
            ->setPaper('A4');
        $filename = 'Kwitansi_' . str_replace('/', '-', $invoice->invoice_number)
            . ($collectivePayment ? '-Angsuran' . $collectivePayment->installment_number : '')
            . '_' . $versi . '.pdf';

        return $pdf->download($filename);
    }

    public function kolektifAngsuranStore(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount'   => 'required|numeric|min:1',
            'due_date' => 'nullable|date',
            'notes'    => 'nullable|string',
        ]);

        $count = $invoice->collectivePayments()->count();

        if ($count >= 3) {
            return back()->with('error', 'Maksimal 3 angsuran per invoice.');
        }

        $allocated = $invoice->collectivePayments()->sum('amount');
        if (($allocated + $request->amount) > $invoice->total_amount) {
            return back()->with('error', 'Total angsuran melebihi total invoice.');
        }

        CollectivePayment::create([
            'invoice_id'         => $invoice->id,
            'tuk_id'             => $invoice->tuk_id,
            'installment_number' => $count + 1,
            'amount'             => $request->amount,
            'due_date'           => $request->due_date,
            'notes'              => $request->notes,
            'status'             => 'pending',
        ]);

        return redirect()->route('bendahara.payments.kolektif.detail', $invoice)
            ->with('success', 'Angsuran ke-' . ($count + 1) . ' berhasil ditambahkan.');
    }

    public function kolektifAngsuranVerify(Request $request, CollectivePayment $payment)
    {
        $request->validate(['action' => 'required|in:verify,reject', 'notes' => 'nullable|string']);
        abort_if($payment->status !== 'pending', 422, 'Sudah diproses.');
        abort_if(!$payment->proof_path, 422, 'Belum ada bukti bayar.');

        if ($request->action === 'verify') {
            $payment->update(['status' => 'verified', 'verified_by' => Auth::id(), 'verified_at' => now()]);

            // Inject jurnal pelunasan piutang
            try {
                if (!\App\Models\JournalEntry::existsFor(\App\Models\CollectivePayment::class, $payment->id)) {
                    app(JournalService::class)->jurnalPiutangInvoiceLunas($payment->load('invoice'));
                }
            } catch (\Exception $e) {
                \Log::warning('Gagal buat jurnal pelunasan angsuran: ' . $e->getMessage());
            }

            if ($payment->invoice->isFullyPaid()) {
                $payment->invoice->update(['status' => 'paid']);
            }
            $msg = 'Angsuran ke-' . $payment->installment_number . ' terverifikasi.';
        } else {
            $payment->update(['status' => 'rejected', 'rejection_notes' => $request->notes]);
            $msg = 'Angsuran ke-' . $payment->installment_number . ' ditolak.';
        }

        return redirect()->route('bendahara.payments.kolektif.detail', $payment->invoice)
            ->with('success', $msg);
    }

    public function kolektifBuktiBayar(CollectivePayment $payment)
    {
        abort_if(!$payment->proof_path, 404);
        return Storage::disk('private')->download($payment->proof_path);
    }

    /**
     * Download invoice individu (bendahara side)
     */
    public function downloadInvoiceIndividu(\App\Models\Payment $payment)
    {
        $payment->load(['asesmen.skema', 'asesmen.tuk']);
        $asesmen = $payment->asesmen;

        $issuedAt = $payment->verified_at ?? now();

        $invoiceNumber = str_pad($asesmen->id, 5, '0', STR_PAD_LEFT)
            . '/LSP-KAP/KU.00.01/'
            . \Carbon\Carbon::parse($issuedAt)->format('n') . '/'
            . \Carbon\Carbon::parse($issuedAt)->format('Y');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice-individu', compact(
            'asesmen',
            'payment',
            'invoiceNumber'
        ))->setPaper('A4', 'portrait');

        return $pdf->download('Invoice_' . $asesmen->full_name . '_' . date('Ymd') . '.pdf');
    }

    /**
     * Download kwitansi individu (bendahara side)
     * ?versi=kosong atau ?versi=berisi
     */
    public function kwitansiIndividu(\App\Models\Payment $payment, \Illuminate\Http\Request $request)
    {
        abort_if($payment->status !== 'verified', 403, 'Pembayaran belum terverifikasi.');

        $payment->load(['asesmen.skema', 'asesmen.tuk']);
        $asesmen = $payment->asesmen;
        $versi   = $request->query('versi', 'berisi');

        $issuedAt = $payment->verified_at ?? now();

        $kwitansiNumber = str_pad($payment->id, 5, '0', STR_PAD_LEFT)
            . '/LSP-KAP/KEU.KM/'
            . \Carbon\Carbon::parse($issuedAt)->format('n') . '/'
            . \Carbon\Carbon::parse($issuedAt)->format('Y');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.kwitansi-individu', compact(
            'asesmen',
            'payment',
            'kwitansiNumber',
            'versi'
        ))->setPaper('A4', 'portrait');

        $filename = 'Kwitansi_' . $asesmen->full_name . '_' . $versi . '_' . date('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}