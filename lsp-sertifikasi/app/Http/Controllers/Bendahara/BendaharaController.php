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

    public function kolektifInvoiceStore(Request $request)
    {
        $request->validate([
            'batch_id'                 => 'required|string',
            'recipient_name'           => 'required|string|max:255',
            'recipient_address'        => 'nullable|string',
            'items'                    => 'required|array|min:1',
            'items.*.skema_id'         => 'required|integer',
            'items.*.skema_name'       => 'required|string',
            'items.*.jumlah'           => 'required|integer|min:1',
            'items.*.harga_satuan'     => 'required|numeric|min:0',
            'notes'                    => 'nullable|string',
        ]);

        abort_if(Invoice::where('batch_id', $request->batch_id)->exists(), 422, 'Invoice sudah ada.');

        $asesmens = Asesmen::where('collective_batch_id', $request->batch_id)->get();
        abort_if($asesmens->isEmpty(), 404);

        $items = collect($request->items)->map(function ($item) {
            $item['subtotal'] = (float) $item['harga_satuan'] * (int) $item['jumlah'];
            return $item;
        })->toArray();

        $invoice = DB::transaction(function () use ($request, $items, $asesmens) {
            $n = Invoice::generateNumber();
            return Invoice::create([
                'invoice_number'    => $n['invoice_number'],
                'sequence_number'   => $n['sequence_number'],
                'invoice_year'      => $n['invoice_year'],
                'batch_id'          => $request->batch_id,
                'tuk_id'            => $asesmens->first()->tuk_id,
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

        return redirect()->route('bendahara.payments.kolektif.detail', $request->batch_id)
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' berhasil dibuat.');
    }

    public function kolektifInvoiceUpdate(Request $request, Invoice $invoice)
    {
        abort_if($invoice->status !== 'draft', 403, 'Invoice sudah dikirim, tidak bisa diubah.');

        $request->validate([
            'recipient_name'           => 'required|string|max:255',
            'recipient_address'        => 'nullable|string',
            'items'                    => 'required|array|min:1',
            'items.*.skema_id'         => 'required|integer',
            'items.*.skema_name'       => 'required|string',
            'items.*.jumlah'           => 'required|integer|min:1',
            'items.*.harga_satuan'     => 'required|numeric|min:0',
            'notes'                    => 'nullable|string',
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
        ]);

        return redirect()->route('bendahara.payments.kolektif.invoice', $invoice->batch_id)
            ->with('success', 'Invoice berhasil diperbarui.');
    }

    public function kolektifInvoiceSend(Invoice $invoice)
    {
        abort_if($invoice->status !== 'draft', 403);
        $invoice->update(['status' => 'sent']);
        return redirect()->route('bendahara.payments.kolektif.invoice', $invoice->batch_id)
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
        abort_if($count >= 3, 422, 'Maksimal 3 angsuran.');

        $allocated = $invoice->collectivePayments()->sum('amount');
        abort_if(($allocated + $request->amount) > $invoice->total_amount, 422, 'Melebihi total invoice.');

        CollectivePayment::create([
            'invoice_id'         => $invoice->id,
            'batch_id'           => $invoice->batch_id,
            'tuk_id'             => $invoice->tuk_id,
            'installment_number' => $count + 1,
            'amount'             => $request->amount,
            'due_date'           => $request->due_date,
            'notes'              => $request->notes,
            'status'             => 'pending',
        ]);

        return redirect()->route('bendahara.payments.kolektif.invoice', $invoice->batch_id)
            ->with('success', 'Angsuran ke-' . ($count + 1) . ' berhasil ditambahkan.');
    }

    public function kolektifAngsuranVerify(Request $request, CollectivePayment $payment)
    {
        $request->validate(['action' => 'required|in:verify,reject', 'notes' => 'nullable|string']);
        abort_if($payment->status !== 'pending', 422, 'Sudah diproses.');
        abort_if(!$payment->proof_path, 422, 'Belum ada bukti bayar.');

        if ($request->action === 'verify') {
            $payment->update(['status' => 'verified', 'verified_by' => Auth::id(), 'verified_at' => now()]);
            if ($payment->invoice->isFullyPaid()) {
                $payment->invoice->update(['status' => 'paid']);
            }
            $msg = 'Angsuran ke-' . $payment->installment_number . ' terverifikasi.';
        } else {
            $payment->update(['status' => 'rejected', 'rejection_notes' => $request->notes]);
            $msg = 'Angsuran ke-' . $payment->installment_number . ' ditolak.';
        }

        return redirect()->route('bendahara.payments.kolektif.invoice', $payment->invoice->batch_id)
            ->with('success', $msg);
    }

    public function kolektifBuktiBayar(CollectivePayment $payment)
    {
        abort_if(!$payment->proof_path, 404);
        return Storage::disk('private')->download($payment->proof_path);
    }

    // Ganti method kolektif() yang lama dengan ini:
    public function kolektif()
    {
        $batches = Asesmen::where('is_collective', true)
            ->select(
                'collective_batch_id',
                'tuk_id',
                DB::raw('COUNT(*) as jumlah_asesi'),
                DB::raw('MIN(registration_date) as tanggal_daftar')
            )
            ->groupBy('collective_batch_id', 'tuk_id')
            ->with('tuk')
            ->orderByDesc('tanggal_daftar')
            ->get()
            ->map(function ($b) {
                $b->invoice = Invoice::where('batch_id', $b->collective_batch_id)
                ->with ('collectivePayments')
                ->first();
                return $b;
            });

        return view('bendahara.payments.kolektif', compact('batches'));
    }

    // Ganti method kolektifDetail() dengan ini:
    public function kolektifDetail($batchId)
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

        return view('bendahara.payments.kolektif-detail', compact(
            'asesmens',
            'tuk',
            'batchId',
            'invoice',
            'skemaGroups',
            'collectivePayments'
        ));
    }
}
