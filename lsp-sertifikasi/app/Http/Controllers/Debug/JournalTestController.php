<?php
// app/Http/Controllers/Debug/JournalTestController.php

namespace App\Http\Controllers\Debug;

use App\Http\Controllers\Controller;
use App\Models\BiayaOperasional;
use App\Models\ChartOfAccount;
use App\Models\HonorPayment;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalTestController extends Controller
{
    public function index()
    {
        $payments = Payment::with('asesmen.skema')
            ->where('status', 'verified')
            ->latest()->take(20)->get();

        $honors = HonorPayment::with('asesor')
            ->whereIn('status', ['sudah_dibayar', 'dikonfirmasi'])
            ->latest()->take(20)->get();

        $honors_semua = HonorPayment::with('asesor')
            ->latest()->take(20)->get();

        $biayaOps = BiayaOperasional::latest()->take(20)->get();

        $entries = JournalEntry::with(['lines.akun'])
            ->latest()->take(30)->get();

        $coas = ChartOfAccount::withCount('lines')
            ->orderBy('urutan')->orderBy('kode')->get();
        $invoices_sent = \App\Models\Invoice::with('tuk')
            ->where('status', 'sent')
            ->latest()->take(20)->get();

        $angsuran_pending = \App\Models\CollectivePayment::with('invoice.tuk')
            ->where('status', 'verified')
            ->latest()->take(20)->get();
        $totalEntries = JournalEntry::count();
        $totalLines   = JournalEntryLine::count();
        $payments_semua = Payment::with('asesmen.skema')
            ->latest()
            ->take(20)
            ->get();

        return view('debug.journal-test', compact(
            'payments', 'honors', 'honors_semua', 'biayaOps',
            'payments_semua', 'invoices_sent', 'angsuran_pending',
            'entries', 'coas', 'totalEntries', 'totalLines'
        ));
    }

    public function test(Request $request)
    {
        $action  = $request->input('action');
        $service = app(JournalService::class);

        try {
            switch ($action) {

            case 'piutang_invoice':
    if (!$request->invoice_id) {
        return back()->with('result', ['status'=>'error','message'=>'Pilih invoice dulu.']);
    }
    $invoice = \App\Models\Invoice::findOrFail($request->invoice_id);

    if (JournalEntry::existsFor(\App\Models\Invoice::class . '_piutang', $invoice->id)) {
        return back()->with('result', [
            'status'  => 'error',
            'message' => "Jurnal piutang invoice #{$invoice->id} sudah ada.",
        ]);
    }

    $entry = $service->jurnalPiutangInvoice($invoice);
    return back()->with('result', [
        'status'  => 'success',
        'message' => "✅ {$entry->nomor} — Piutang Invoice {$invoice->invoice_number}",
        'detail'  => "Dr. 1-003 Piutang Asesi | Cr. 4-001 Pendapatan\nTotal: Rp " . number_format($invoice->total_amount, 0, ',', '.'),
    ]);

case 'piutang_invoice_lunas':
    if (!$request->angsuran_id) {
        return back()->with('result', ['status'=>'error','message'=>'Pilih angsuran dulu.']);
    }
    $angsuran = \App\Models\CollectivePayment::with('invoice')->findOrFail($request->angsuran_id);

    if (JournalEntry::existsFor(\App\Models\CollectivePayment::class, $angsuran->id)) {
        return back()->with('result', [
            'status'  => 'error',
            'message' => "Jurnal pelunasan angsuran #{$angsuran->id} sudah ada.",
        ]);
    }

    // Auto-buat piutang invoice dulu kalau belum ada
    if (!JournalEntry::existsFor(\App\Models\Invoice::class . '_piutang', $angsuran->invoice_id)) {
        $service->jurnalPiutangInvoice($angsuran->invoice);
    }

    $entry = $service->jurnalPiutangInvoiceLunas($angsuran);
    return back()->with('result', [
        'status'  => 'success',
        'message' => "✅ {$entry->nomor} — Angsuran ke-{$angsuran->installment_number} {$angsuran->invoice->invoice_number}",
        'detail'  => "Dr. 1-002 Bank | Cr. 1-003 Piutang Asesi\nTotal: Rp " . number_format($angsuran->amount, 0, ',', '.'),
    ]);

                case 'payment':
                    if (!$request->payment_id) {
                        return back()->with('result', ['status'=>'error','message'=>'Pilih payment dulu.']);
                    }
                    $payment = Payment::with('asesmen.skema')->findOrFail($request->payment_id);

                    if (JournalEntry::existsFor(Payment::class, $payment->id)) {
                        return back()->with('result', [
                            'status'  => 'error',
                            'message' => "Jurnal untuk Payment #{$payment->id} sudah ada — skip.",
                        ]);
                    }

                    $entry = $service->jurnalPaymentVerified($payment);
                    return back()->with('result', [
                        'status'  => 'success',
                        'message' => "✅ Jurnal {$entry->nomor} berhasil untuk Payment #{$payment->id}",
                        'detail'  => "Dr. 1-002 Bank | Cr. 4-001 Pendapatan\nTotal: Rp " . number_format($payment->amount, 0, ',', '.'),
                    ]);

                case 'honor_dibuat':
                    if (!$request->honor_dibuat_id) {
                        return back()->with('result', ['status'=>'error','message'=>'Pilih honor dulu.']);
                    }
                    $honor = HonorPayment::with('asesor')->findOrFail($request->honor_dibuat_id);

                    if (JournalEntry::existsFor(HonorPayment::class . '_dibuat', $honor->id)) {
                        return back()->with('result', [
                            'status'  => 'error',
                            'message' => "Jurnal honor dibuat #{$honor->id} sudah ada.",
                        ]);
                    }

                    $entry = $service->jurnalHonorDibuat($honor->fresh(['asesor']));
                    return back()->with('result', [
                        'status'  => 'success',
                        'message' => "✅ Jurnal {$entry->nomor} — Honor Dibuat {$honor->nomor_kwitansi}",
                        'detail'  => "Dr. 5-001 Beban Honor | Cr. 2-001 Utang Honor\nTotal: Rp " . number_format($honor->total, 0, ',', '.'),
                    ]);

                case 'honor':
                    if (!$request->honor_id) {
                        return back()->with('result', ['status'=>'error','message'=>'Pilih honor dulu.']);
                    }
                    $honor = HonorPayment::with('asesor')->findOrFail($request->honor_id);

                    if (JournalEntry::existsFor(HonorPayment::class, $honor->id)) {
                        return back()->with('result', [
                            'status'  => 'error',
                            'message' => "Jurnal untuk Honor #{$honor->id} sudah ada — skip.",
                        ]);
                    }

                    if (!$honor->dibayar_at) {
                        $honor->dibayar_at = now();
                    }

                    $entry = $service->jurnalHonorDibayar($honor->fresh(['asesor']));
                    return back()->with('result', [
                        'status'  => 'success',
                        'message' => "✅ Jurnal {$entry->nomor} berhasil untuk Honor {$honor->nomor_kwitansi}",
                        'detail'  => "Dr. 2-001 Utang Honor | Cr. 1-002 Bank\nTotal: Rp " . number_format($honor->total, 0, ',', '.'),
                    ]);

                case 'biaya_ops':
                    if (!$request->biaya_id) {
                        return back()->with('result', ['status'=>'error','message'=>'Pilih biaya operasional dulu.']);
                    }
                    $biaya = BiayaOperasional::findOrFail($request->biaya_id);

                    if (JournalEntry::existsFor(BiayaOperasional::class, $biaya->id)) {
                        return back()->with('result', [
                            'status'  => 'error',
                            'message' => "Jurnal untuk Biaya Ops #{$biaya->id} sudah ada — skip.",
                        ]);
                    }

                    $entry = $service->jurnalBiayaOperasional($biaya);
                    return back()->with('result', [
                        'status'  => 'success',
                        'message' => "✅ Jurnal {$entry->nomor} berhasil untuk {$biaya->nomor}",
                        'detail'  => "Dr. 5-002 Beban Ops | Cr. 1-002 Bank\nTotal: Rp " . number_format($biaya->total, 0, ',', '.'),
                    ]);

                case 'check_balance':
                    $allEntries = JournalEntry::with('lines')->get();
                    $errors = [];
                    $ok     = 0;

                    foreach ($allEntries as $e) {
                        $d = $e->lines->sum('debit');
                        $k = $e->lines->sum('kredit');
                        if ($d !== $k) {
                            $errors[] = "{$e->nomor}: debit={$d}, kredit={$k}";
                        } else {
                            $ok++;
                        }
                    }

                    if (empty($errors)) {
                        return back()->with('result', [
                            'status'  => 'success',
                            'message' => "✅ Semua {$ok} jurnal BALANCE.",
                        ]);
                    }
                    return back()->with('result', [
                        'status'  => 'error',
                        'message' => count($errors) . " jurnal TIDAK BALANCE:",
                        'detail'  => implode("\n", $errors),
                    ]);

                    case 'piutang_asesi':
    if (!$request->piutang_payment_id) {
        return back()->with('result', ['status'=>'error','message'=>'Pilih payment dulu.']);
    }
    $payment = Payment::with('asesmen.skema')->findOrFail($request->piutang_payment_id);

    if (JournalEntry::existsFor(Payment::class . '_piutang', $payment->id)) {
        return back()->with('result', ['status'=>'error','message'=>"Jurnal piutang #{$payment->id} sudah ada."]);
    }

    $entry = $service->jurnalPiutangAsesi($payment);
    return back()->with('result', [
        'status'  => 'success',
        'message' => "✅ {$entry->nomor} — Piutang Asesi #{$payment->id}",
        'detail'  => "Dr. 1-003 Piutang Asesi | Cr. 4-001 Pendapatan\nTotal: Rp " . number_format($payment->amount, 0, ',', '.'),
    ]);

case 'piutang_lunas':
    if (!$request->lunas_payment_id) {
        return back()->with('result', ['status'=>'error','message'=>'Pilih payment dulu.']);
    }
    $payment = Payment::with('asesmen.skema')->findOrFail($request->lunas_payment_id);

    if (JournalEntry::existsFor(Payment::class, $payment->id)) {
        return back()->with('result', ['status'=>'error','message'=>"Jurnal pelunasan #{$payment->id} sudah ada."]);
    }

    // Auto-buat piutang dulu kalau belum ada
    if (!JournalEntry::existsFor(Payment::class . '_piutang', $payment->id)) {
        $service->jurnalPiutangAsesi($payment->fresh(['asesmen.skema']));
    }

    $entry = $service->jurnalPiutangLunas($payment->fresh(['asesmen.skema']));
    return back()->with('result', [
        'status'  => 'success',
        'message' => "✅ {$entry->nomor} — Piutang Lunas #{$payment->id}",
        'detail'  => "Dr. 1-002 Bank | Cr. 1-003 Piutang Asesi\nTotal: Rp " . number_format($payment->amount, 0, ',', '.'),
    ]);
                case 'clear_all':
                    if (!app()->isLocal()) {
                        return back()->with('result', ['status'=>'error','message'=>'Clear hanya bisa di environment local.']);
                    }
                    $count = JournalEntry::count();
                    JournalEntryLine::query()->delete();
                    JournalEntry::query()->delete();
                    return back()->with('result', [
                        'status'  => 'success',
                        'message' => "🗑 {$count} jurnal berhasil dihapus.",
                    ]);

                default:
                    return back()->with('result', ['status'=>'error','message'=>"Action tidak dikenal: {$action}"]);
            }

        } catch (\Exception $e) {
            return back()->with('result', [
                'status'  => 'error',
                'message' => "❌ Error: " . $e->getMessage(),
                'detail'  => $e->getTraceAsString(),
            ]);
        }
    }
}