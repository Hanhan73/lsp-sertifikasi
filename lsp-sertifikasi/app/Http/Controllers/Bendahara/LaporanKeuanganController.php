<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\AccountBalance;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\HonorPayment;
use App\Models\BiayaOperasional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\JournalService;

class LaporanKeuanganController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $tahun     = (int)($request->get('tahun', now()->year));
        $balance   = AccountBalance::forTahun($tahun);
        $tahunList = $this->tahunList();

        // Summary dari jurnal
        $summary = $this->summaryDariJurnal($tahun, $balance);

        return view('bendahara.laporan-keuangan.index', compact(
            'tahun', 'tahunList', 'balance', 'summary'
        ));
    }

    // ── Edit Saldo Manual ─────────────────────────────────────────────────
    public function editSaldo(Request $request)
    {
        $tahun     = (int)($request->get('tahun', now()->year));
        $balance   = AccountBalance::forTahun($tahun);
        $tahunList = $this->tahunList();
        $summary   = $this->summaryDariJurnal($tahun, $balance);

        return view('bendahara.laporan-keuangan.edit-saldo', compact(
            'tahun', 'tahunList', 'balance', 'summary'
        ));
    }

    public function updateSaldo(Request $request)
    {
        $tahun = (int)($request->get('tahun', now()->year));

        $validated = $request->validate([
            'kas'               => 'required|integer|min:0',
            'bank'              => 'required|integer|min:0',
            'saldo_awal_bank'   => 'required|integer|min:0',
            'perlengkapan'      => 'required|integer|min:0',
            'utang_operasional' => 'required|integer|min:0',
            'saldo_dana'        => 'required|integer|min:0',
        ]);

        $balance = AccountBalance::forTahun($tahun);
        $balance->update(array_merge($validated, ['diupdate_oleh' => auth()->id()]));

        return redirect()->route('bendahara.laporan-keuangan.index', ['tahun' => $tahun])
            ->with('success', 'Saldo berhasil diperbarui.');
    }

    // ── Laba Rugi — dari jurnal ───────────────────────────────────────────
    public function labaRugi(Request $request)
    {
        $tahun     = (int)($request->get('tahun', now()->year));
        $tahunList = $this->tahunList();
        $balance   = AccountBalance::forTahun($tahun);

        $summary = $this->summaryDariJurnal($tahun, $balance);

        // Breakdown pendapatan per skema (masih dari payments karena jurnal tidak menyimpan skema)
        $pendapatanIndividu = Payment::selectRaw('skemas.name as skema, SUM(payments.amount) as total')
            ->join('asesmens', 'asesmens.id', '=', 'payments.asesmen_id')
            ->join('skemas', 'skemas.id', '=', 'asesmens.skema_id')
            ->where('payments.status', 'verified')
            ->whereYear('payments.verified_at', $tahun)
            ->groupBy('skemas.id', 'skemas.name')
            ->get()
            ->keyBy('skema');

        // Pendapatan kolektif per skema (invoice sent/paid)
        $pendapatanKolektif = \App\Models\Invoice::whereIn('status', ['sent', 'paid'])
            ->whereYear('issued_at', $tahun)
            ->get()
            ->flatMap(fn($inv) => collect($inv->items))
            ->groupBy('skema_name')
            ->map(fn($items, $nama) => (object)[
                'skema' => $nama,
                'total' => $items->sum('subtotal'),
            ]);

        // Gabung kolektif + individu
        $pendapatanSkema = $pendapatanKolektif->map(function ($kol) use ($pendapatanIndividu) {
            $ind = $pendapatanIndividu->get($kol->skema);
            return (object)[
                'skema' => $kol->skema,
                'total' => $kol->total + ($ind ? $ind->total : 0),
            ];
        });

        // Tambah skema individu yang tidak ada di kolektif
        foreach ($pendapatanIndividu as $nama => $ind) {
            if (!$pendapatanSkema->has($nama)) {
                $pendapatanSkema->put($nama, (object)['skema' => $nama, 'total' => $ind->total]);
            }
        }

        $pendapatanSkema = $pendapatanSkema->sortByDesc('total')->values();

        // Breakdown beban ops per item
        $bebanOpsDetail = BiayaOperasional::whereYear('tanggal', $tahun)
            ->orderByDesc('total')->get();

        // Breakdown per bulan dari jurnal
        $pendapatanPerBulan = $this->saldoAkunPerBulan('4-001', $tahun);
        $honorPerBulan      = $this->saldoAkunPerBulan('5-001', $tahun);
        $opsPerBulan        = $this->saldoAkunPerBulan('5-002', $tahun);

        if ($request->get('export') === 'pdf') {
            return $this->exportLabaRugiPdf($tahun, $summary, $pendapatanSkema, $bebanOpsDetail);
        }

        return view('bendahara.laporan-keuangan.laba-rugi', compact(
            'tahun', 'tahunList', 'balance', 'summary',
            'pendapatanSkema', 'bebanOpsDetail',
            'pendapatanPerBulan', 'honorPerBulan', 'opsPerBulan'
        ));
    }

    // ── Neraca — dari jurnal + manual ─────────────────────────────────────
    public function neraca(Request $request)
    {
        $tahun     = (int)($request->get('tahun', now()->year));
        $balance   = AccountBalance::forTahun($tahun);
        $tahunList = $this->tahunList();
        $summary   = $this->summaryDariJurnal($tahun, $balance);
        $saldoAwalBank = (int)$balance->saldo_awal_bank;
        $mutasiBank    = $this->saldoAkun('1-002', $tahun);
        $bank          = $saldoAwalBank + $mutasiBank;

        $piutangAsesi = $this->saldoAkun('1-003', $tahun);

        // Utang honor dari jurnal akun 2-001
        $utangHonor = $this->saldoAkun('2-001', $tahun);

        // Surplus = pendapatan - beban (dari jurnal)
        $surplus = $summary['pendapatan'] - $summary['beban_honor'] - $summary['beban_ops'];

        // Total aset = manual (kas+bank+perlengkapan) + piutang dari jurnal
        $totalAset = $balance->kas + $bank + $balance->perlengkapan + $piutangAsesi;

        // Total kewajiban = utang honor + utang ops manual + hutang distribusi
        $totalKewajiban = $utangHonor + $balance->utang_operasional;
        // Total ekuitas = saldo dana + surplus - distribusi
        $totalEkuitas = $balance->saldo_dana + $surplus - $summary['distribusi'];

        $totalKewEkuitas = $totalKewajiban + $totalEkuitas;

        if ($request->get('export') === 'pdf') {
            return $this->exportNeracaPdf($tahun, $balance, compact(
                'piutangAsesi', 'utangHonor',
                'surplus', 'totalAset', 'totalKewajiban',
                'totalEkuitas', 'totalKewEkuitas', 'bank'
            ));
        }

        return view('bendahara.laporan-keuangan.neraca', compact(
            'tahun', 'tahunList', 'balance', 'summary',
            'piutangAsesi', 'utangHonor',
            'surplus', 'totalAset', 'totalKewajiban',
            'totalEkuitas', 'totalKewEkuitas',
            'bank', 'mutasiBank', 'saldoAwalBank' // tambah ini
        ));
    }

    // ── Arus Kas — dari jurnal ────────────────────────────────────────────
public function arusKas(Request $request)
{
    $tahun     = (int)($request->get('tahun', now()->year));
    $balance   = AccountBalance::forTahun($tahun);
    $tahunList = $this->tahunList();
    $summary   = $this->summaryDariJurnal($tahun, $balance);

    $penerimaanSertifikasi = $summary['pendapatan'];

    // ── Kas keluar honor = kredit 1-002 dari jurnal pelunasan honor ────────
    // Bukan dari beban honor (5-001) karena beban timbul saat utang dibuat,
    // kas baru keluar saat honor dibayar
    $akunBank  = ChartOfAccount::where('kode', '1-002')->first();
    $pembayaranHonor = 0;
    if ($akunBank) {
        $pembayaranHonor = (int) JournalEntryLine::where('chart_of_account_id', $akunBank->id)
            ->where('kredit', '>', 0)
            ->whereHas('entry', function ($q) use ($tahun) {
                $q->whereYear('tanggal', $tahun)
                  ->where('ref_type', \App\Models\HonorPayment::class);
            })
            ->sum('kredit');
    }

    // ── Kas keluar ops = kredit 1-002 dari jurnal biaya operasional ─────────
    $pembayaranOps = 0;
    if ($akunBank) {
        $pembayaranOps = (int) JournalEntryLine::where('chart_of_account_id', $akunBank->id)
            ->where('kredit', '>', 0)
            ->whereHas('entry', function ($q) use ($tahun) {
                $q->whereYear('tanggal', $tahun)
                  ->where('ref_type', \App\Models\BiayaOperasional::class);
            })
            ->sum('kredit');
    }

    $pembayaranDistr = $summary['distribusi'];

    $kasOperasi = $penerimaanSertifikasi - $pembayaranHonor - $pembayaranOps - $pembayaranDistr;

    $balanceLalu = AccountBalance::where('tahun', $tahun - 1)->first();
    $kasAwal     = $balanceLalu ? ($balanceLalu->kas + $balanceLalu->bank) : 0;
    $kasAkhir    = $kasAwal + $kasOperasi;

    if ($request->get('export') === 'pdf') {
        return $this->exportArusKasPdf(
            $tahun, $balance, $kasAwal, $kasAkhir, $kasOperasi,
            $penerimaanSertifikasi, $pembayaranHonor, $pembayaranOps, $pembayaranDistr
        );
    }

    return view('bendahara.laporan-keuangan.arus-kas', compact(
        'tahun', 'tahunList', 'balance', 'summary',
        'kasAwal', 'kasAkhir', 'kasOperasi',
        'penerimaanSertifikasi', 'pembayaranHonor', 'pembayaranOps', 'pembayaranDistr'
    ));
}

    // ── Perubahan Modal — dari jurnal ─────────────────────────────────────
    public function perubahanModal(Request $request)
    {
        $tahun     = (int)($request->get('tahun', now()->year));
        $balance   = AccountBalance::forTahun($tahun);
        $tahunList = $this->tahunList();
        $summary   = $this->summaryDariJurnal($tahun, $balance);

        $balanceLalu = AccountBalance::where('tahun', $tahun - 1)->first();
        $summaryLalu = $balanceLalu ? $this->summaryDariJurnal($tahun - 1, $balanceLalu) : null;

        $saldoAwal  = $balanceLalu
            ? $balanceLalu->saldo_dana + ($summaryLalu['pendapatan'] - $summaryLalu['beban_honor'] - $summaryLalu['beban_ops'] - $summaryLalu['distribusi'])
            : $balance->saldo_dana;

        $surplus    = $summary['pendapatan'] - $summary['beban_honor'] - $summary['beban_ops'];
        $distribusi = $summary['distribusi'];
        $saldoAkhir = $saldoAwal + $surplus - $distribusi;

        if ($request->get('export') === 'pdf') {
            return $this->exportPerubahanModalPdf(
                $tahun, $balance, $saldoAwal, $surplus, $distribusi, $saldoAkhir
            );
        }

        return view('bendahara.laporan-keuangan.perubahan-modal', compact(
            'tahun', 'tahunList', 'balance', 'summary',
            'saldoAwal', 'surplus', 'distribusi', 'saldoAkhir'
        ));
    }

    // ── Distribusi Yayasan ────────────────────────────────────────────────
    public function distribusi(Request $request)
    {
        $tahun     = (int)($request->get('tahun', now()->year));
        $balance   = AccountBalance::forTahun($tahun);
        $tahunList = $this->tahunList();
        $summary   = $this->summaryDariJurnal($tahun, $balance);

        return view('bendahara.laporan-keuangan.distribusi', compact(
            'tahun', 'tahunList', 'balance', 'summary'
        ));
    }

    public function updateDistribusi(Request $request)
    {
        $tahun = (int)($request->get('tahun', now()->year));

        $validated = $request->validate([
            'distribusi_yayasan'  => 'required|integer|min:0',
            'hutang_distribusi'   => 'required|integer|min:0',
            'tanggal_distribusi'  => 'nullable|date',
            'catatan_distribusi'  => 'nullable|string|max:1000',
        ]);

        $balance = AccountBalance::forTahun($tahun);
        $balance->update(array_merge($validated, ['diupdate_oleh' => auth()->id()]));

        if ($validated['distribusi_yayasan'] > 0) {
            try {
                app(JournalService::class)->jurnalDistribusi(
                    $validated['distribusi_yayasan'], $tahun
                );
            } catch (\Exception $e) {
                \Log::warning('Gagal buat jurnal distribusi: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Data distribusi berhasil disimpan.');
    }

    public function jurnalBalik(Request $request)
    {
        $tahun   = (int)($request->get('tahun', now()->year));
        $balance = AccountBalance::forTahun($tahun);

        if ($balance->jurnal_balik_done) {
            return back()->with('error', 'Jurnal balik untuk tahun ' . $tahun . ' sudah dilakukan.');
        }

        $summary = $this->summaryDariJurnal($tahun, $balance);

        if ($summary['distribusi'] > 0) {
            try {
                app(JournalService::class)->jurnalBalikDistribusi(
                    $summary['distribusi'], $tahun
                );
            } catch (\Exception $e) {
                \Log::warning('Gagal buat jurnal balik: ' . $e->getMessage());
            }
        }

        $balance->update([
            'hutang_distribusi'  => $balance->distribusi_yayasan,
            'distribusi_yayasan' => 0,
            'jurnal_balik_done'  => true,
            'diupdate_oleh'      => auth()->id(),
        ]);

        return back()->with('success', 'Jurnal balik distribusi berhasil dilakukan.');
    }

    // ── Transaksi Harian — dari jurnal ────────────────────────────────────
    public function transaksiHarian(Request $request)
    {
        $tanggal   = $request->get('tanggal', today()->toDateString());
        $tahunList = $this->tahunList();

        // Ambil dari journal_entries per tanggal
        $entries = JournalEntry::with(['lines.akun'])
            ->whereDate('tanggal', $tanggal)
            ->orderBy('created_at')
            ->get();

        $transaksi = collect();

        foreach ($entries as $entry) {
            $debitLines  = $entry->lines->where('debit', '>', 0);
            $kreditLines = $entry->lines->where('kredit', '>', 0);

            $akunDebit  = $debitLines->map(fn($l) => $l->akun->kode . ' ' . $l->akun->nama)->implode(', ');
            $akunKredit = $kreditLines->map(fn($l) => $l->akun->kode . ' ' . $l->akun->nama)->implode(', ');

            $tipe = $this->getTipeTransaksi($entry->ref_type ?? '');

            $transaksi->push([
                'waktu'       => $entry->created_at->format('H:i'),
                'nomor'       => $entry->nomor,
                'tipe'        => $tipe,
                'akun_debit'  => $akunDebit,
                'akun_kredit' => $akunKredit,
                'keterangan'  => $entry->keterangan,
                'debit'       => (int) $entry->lines->sum('debit'),
                'kredit'      => (int) $entry->lines->sum('kredit'),
            ]);
        }

        $totalDebit  = $transaksi->sum('debit');
        $totalKredit = $transaksi->sum('kredit');

        return view('bendahara.laporan-keuangan.transaksi-harian', compact(
            'tanggal', 'tahunList', 'transaksi', 'totalDebit', 'totalKredit'
        ));
    }

    // ── Buku Besar — murni dari jurnal ────────────────────────────────────
    public function bukuBesar(Request $request)
    {
        $tahun     = (int)($request->get('tahun', now()->year));
        $akunId    = $request->get('akun_id');
        $tahunList = $this->tahunList();

        $akunList = ChartOfAccount::active()
            ->orderBy('urutan')->orderBy('kode')->get();

        $selectedAkun = $akunId
            ? $akunList->firstWhere('id', $akunId)
            : $akunList->first();

        $entries = $selectedAkun
            ? $this->getBukuBesarEntries($selectedAkun, $tahun)
            : collect();

        return view('bendahara.laporan-keuangan.buku-besar', compact(
            'tahun', 'tahunList', 'akunList', 'selectedAkun', 'entries'
        ));
    }

    // ════════════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Satu sumber kebenaran — semua laporan pakai ini.
     * Ambil total per kategori dari journal_entry_lines berdasarkan kode CoA.
     */
    private function summaryDariJurnal(int $tahun, ?AccountBalance $balance = null): array
    {
        $pendapatan = $this->saldoAkun('4-001', $tahun);
        $bebanHonor = $this->saldoAkun('5-001', $tahun);
        $bebanOps   = $this->saldoAkun('5-002', $tahun);

        // Gunakan balance yang sudah diambil caller, atau query baru kalau belum ada
        $balance    = $balance ?? AccountBalance::forTahun($tahun);
        $distribusi = (int)$balance->distribusi_yayasan;

        $surplus = $pendapatan - $bebanHonor - $bebanOps;

        return [
            'pendapatan'  => $pendapatan,
            'beban_honor' => $bebanHonor,
            'beban_ops'   => $bebanOps,
            'distribusi'  => $distribusi,
            'surplus'     => $surplus,
        ];
    }

    /**
     * Hitung saldo bersih satu akun dari journal_entry_lines untuk tahun tertentu.
     * Default: kredit - debit (untuk pendapatan/kewajiban/ekuitas).
     * Untuk beban/aset: debit - kredit.
     */
    private function saldoAkun(string $kode, int $tahun, string $sisi = null): int
    {
        $akun = ChartOfAccount::where('kode', $kode)->first();
        if (!$akun) return 0;

        $totals = JournalEntryLine::where('chart_of_account_id', $akun->id)
            ->whereHas('entry', fn($q) => $q->whereYear('tanggal', $tahun))
            ->selectRaw('SUM(debit) as total_debit, SUM(kredit) as total_kredit')
            ->first();

        $d = (int)($totals->total_debit ?? 0);
        $k = (int)($totals->total_kredit ?? 0);

        // Kalau sisi eksplisit diminta
        if ($sisi === 'debit')  return $d;
        if ($sisi === 'kredit') return $k;

        // Default: ikut tipe akun
        return in_array($akun->tipe, ['aset', 'beban'])
            ? $d - $k   // normal debit
            : $k - $d;  // normal kredit
    }

    /**
     * Saldo akun per bulan (untuk grafik laba rugi).
     */
    private function saldoAkunPerBulan(string $kode, int $tahun): array
    {
        $akun = ChartOfAccount::where('kode', $kode)->first();
        if (!$akun) return array_fill(1, 12, 0);

        $isDebitNormal = in_array($akun->tipe, ['aset', 'beban']);

        $rows = JournalEntryLine::where('chart_of_account_id', $akun->id)
            ->whereHas('entry', fn($q) => $q->whereYear('tanggal', $tahun))
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->selectRaw('MONTH(journal_entries.tanggal) as bulan, SUM(debit) as td, SUM(kredit) as tk')
            ->groupByRaw('MONTH(journal_entries.tanggal)')
            ->get()
            ->keyBy('bulan');

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $row = $rows->get($m);
            $d   = (int)($row->td ?? 0);
            $k   = (int)($row->tk ?? 0);
            $result[$m] = $isDebitNormal ? $d - $k : $k - $d;
        }

        return $result;
    }

    /**
     * Buku besar per akun — murni dari jurnal.
     */
    private function getBukuBesarEntries(ChartOfAccount $akun, int $tahun): \Illuminate\Support\Collection
    {
        $lines = JournalEntryLine::with('entry')
            ->where('chart_of_account_id', $akun->id)
            ->whereHas('entry', fn($q) => $q->whereYear('tanggal', $tahun))
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->orderBy('journal_entries.id')
            ->select('journal_entry_lines.*')
            ->get();

        $saldo = 0;
        return $lines->map(function ($line) use (&$saldo, $akun) {
            if (in_array($akun->tipe, ['aset', 'beban'])) {
                $saldo += $line->debit - $line->kredit;
            } else {
                $saldo += $line->kredit - $line->debit;
            }

            return [
                'nomor'      => $line->entry->nomor,
                'tanggal'    => $line->entry->tanggal->format('d/m/Y'),
                'keterangan' => $line->entry->keterangan
                    . ($line->keterangan ? ' — ' . $line->keterangan : ''),
                'debit'      => (int)$line->debit,
                'kredit'     => (int)$line->kredit,
                'saldo'      => $saldo,
            ];
        });
    }

    private function tahunList(): \Illuminate\Support\Collection
    {
        $tahunJurnal = JournalEntry::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()->orderByDesc('tahun')->pluck('tahun');
        $tahunDb = AccountBalance::orderByDesc('tahun')->pluck('tahun');

        return $tahunJurnal->concat($tahunDb)->unique()->sortDesc()->values()
            ->whenEmpty(fn($c) => collect([now()->year]));
    }

    // ── PDF exports ───────────────────────────────────────────────────────

    private function exportLabaRugiPdf($tahun, $summary, $pendapatanSkema, $bebanOpsDetail)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'bendahara.laporan-keuangan.pdf.laba-rugi',
            compact('tahun', 'summary', 'pendapatanSkema', 'bebanOpsDetail')
        )->setPaper('A4', 'portrait');
        return $pdf->download("Laporan_Laba_Rugi_{$tahun}.pdf");
    }

    private function exportNeracaPdf($tahun, $balance, $neraca)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'bendahara.laporan-keuangan.pdf.neraca',
            array_merge(compact('tahun', 'balance'), $neraca)
        )->setPaper('A4', 'landscape');
        return $pdf->download("Neraca_{$tahun}.pdf");
    }

    private function exportArusKasPdf(
        $tahun, $balance, $kasAwal, $kasAkhir, $kasOperasi,
        $penerimaanSertifikasi, $pembayaranHonor, $pembayaranOps, $pembayaranDistr
    ) {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'bendahara.laporan-keuangan.pdf.arus-kas',
            compact('tahun', 'balance', 'kasAwal', 'kasAkhir', 'kasOperasi',
                'penerimaanSertifikasi', 'pembayaranHonor', 'pembayaranOps', 'pembayaranDistr')
        )->setPaper('A4', 'portrait');
        return $pdf->download("Arus_Kas_{$tahun}.pdf");
    }

    private function exportPerubahanModalPdf($tahun, $balance, $saldoAwal, $surplus, $distribusi, $saldoAkhir)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'bendahara.laporan-keuangan.pdf.perubahan-modal',
            compact('tahun', 'balance', 'saldoAwal', 'surplus', 'distribusi', 'saldoAkhir')
        )->setPaper('A4', 'portrait');
        return $pdf->download("Perubahan_Modal_{$tahun}.pdf");
    }
    // ── Export Transaksi Harian ───────────────────────────────────────────────
public function exportTransaksiHarian(Request $request)
{
    $tanggal = $request->get('tanggal', today()->toDateString());
    $format  = $request->get('format', 'pdf');

    $entries = JournalEntry::with(['lines.akun'])
        ->whereDate('tanggal', $tanggal)
        ->orderBy('created_at')
        ->get();

    $transaksi = collect();
    foreach ($entries as $entry) {
        $debitLines  = $entry->lines->where('debit', '>', 0);
        $kreditLines = $entry->lines->where('kredit', '>', 0);

        $tipe = $this->getTipeTransaksi($entry->ref_type ?? '');

        $transaksi->push([
            'waktu'       => $entry->created_at->format('H:i'),
            'nomor'       => $entry->nomor,
            'tipe'        => $tipe,
            'akun_debit'  => $debitLines->map(fn($l) => $l->akun->kode . ' ' . $l->akun->nama)->implode(', '),
            'akun_kredit' => $kreditLines->map(fn($l) => $l->akun->kode . ' ' . $l->akun->nama)->implode(', '),
            'keterangan'  => $entry->keterangan,
            'debit'       => (int)$entry->lines->sum('debit'),
            'kredit'      => (int)$entry->lines->sum('kredit'),
        ]);
    }

    $totalDebit  = $transaksi->sum('debit');
    $totalKredit = $transaksi->sum('kredit');

    if ($format === 'excel') {
        return $this->exportTransaksiHarianExcel($tanggal, $transaksi, $totalDebit, $totalKredit);
    }

    return $this->exportTransaksiHarianPdf($tanggal, $transaksi, $totalDebit, $totalKredit);
}

// ── Export Buku Besar ─────────────────────────────────────────────────────
public function exportBukuBesar(Request $request)
{
    $tahun    = (int)($request->get('tahun', now()->year));
    $akunId   = $request->get('akun_id');
    $format   = $request->get('format', 'pdf');

    $akunList     = ChartOfAccount::active()->orderBy('urutan')->orderBy('kode')->get();
    $selectedAkun = $akunId ? $akunList->firstWhere('id', $akunId) : $akunList->first();

    abort_if(!$selectedAkun, 404, 'Akun tidak ditemukan.');

    $entries     = $this->getBukuBesarEntries($selectedAkun, $tahun);
    $totalDebit  = $entries->sum('debit');
    $totalKredit = $entries->sum('kredit');
    $saldoAkhir  = $entries->last()['saldo'] ?? 0;

    if ($format === 'excel') {
        return $this->exportBukuBesarExcel($tahun, $selectedAkun, $entries, $totalDebit, $totalKredit, $saldoAkhir);
    }

    return $this->exportBukuBesarPdf($tahun, $selectedAkun, $entries, $totalDebit, $totalKredit, $saldoAkhir);
}

// ── Private: Excel Transaksi Harian ──────────────────────────────────────
private function exportTransaksiHarianExcel($tanggal, $transaksi, $totalDebit, $totalKredit)
{
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Transaksi Harian');

    $tgl = \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y');

    // Judul
    $sheet->mergeCells('A1:H1');
    $sheet->setCellValue('A1', 'JURNAL TRANSAKSI HARIAN');
    $sheet->mergeCells('A2:H2');
    $sheet->setCellValue('A2', 'Tanggal: ' . $tgl . '   |   LSP-KAP');

    foreach (['A1', 'A2'] as $cell) {
        $sheet->getStyle($cell)->applyFromArray([
            'font'      => ['bold' => true, 'size' => $cell === 'A1' ? 13 : 10],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '1a5276']],
            'font'      => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true,
                            'size' => $cell === 'A1' ? 13 : 10],
        ]);
    }

    // Header
    $headers = ['Waktu', 'No. Jurnal', 'Keterangan', 'Tipe', 'Akun Debit', 'Akun Kredit', 'Debit (Rp)', 'Kredit (Rp)'];
    $cols    = ['A','B','C','D','E','F','G','H'];
    foreach ($headers as $i => $h) {
        $sheet->setCellValue($cols[$i] . '4', $h);
    }
    $sheet->getStyle('A4:H4')->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2C3E50']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
    ]);

    // Data
    foreach ($transaksi as $i => $t) {
        $row = 5 + $i;
        $sheet->setCellValue("A{$row}", $t['waktu']);
        $sheet->setCellValue("B{$row}", $t['nomor']);
        $sheet->setCellValue("C{$row}", $t['keterangan']);
        $sheet->setCellValue("D{$row}", match($t['tipe']) {
            'pemasukan'  => 'Masuk',
            'piutang'    => 'Piutang',
            'honor'      => 'Honor Bayar',
            'beban'      => 'Beban Honor',
            'biaya_ops'  => 'Operasional',
            'distribusi' => 'Distribusi',
            default      => 'Umum',
        });
        $sheet->setCellValue("E{$row}", $t['akun_debit']);
        $sheet->setCellValue("F{$row}", $t['akun_kredit']);
        $sheet->setCellValue("G{$row}", $t['debit']);
        $sheet->setCellValue("H{$row}", $t['kredit']);

        $sheet->getStyle("G{$row}:H{$row}")->getNumberFormat()
            ->setFormatCode('#,##0');
        $sheet->getStyle("G{$row}")->getFont()->getColor()->setRGB('27AE60');
        $sheet->getStyle("H{$row}")->getFont()->getColor()->setRGB('C0392B');

        $bg = $i % 2 === 0 ? 'FFFFFF' : 'F9F9F9';
        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
            'fill'    => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                          'startColor' => ['rgb' => $bg]],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                           'color' => ['rgb' => 'DDDDDD']]],
        ]);
    }

    // Total
    $totalRow = 5 + $transaksi->count();
    $sheet->setCellValue("F{$totalRow}", 'TOTAL');
    $sheet->setCellValue("G{$totalRow}", $totalDebit);
    $sheet->setCellValue("H{$totalRow}", $totalKredit);
    $sheet->getStyle("A{$totalRow}:H{$totalRow}")->applyFromArray([
        'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill'    => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                      'startColor' => ['rgb' => '2C3E50']],
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
    ]);
    foreach (['G', 'H'] as $col) {
        $sheet->getStyle("{$col}{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
    }

    // Lebar kolom
    $widths = ['A'=>8,'B'=>16,'C'=>40,'D'=>12,'E'=>28,'F'=>28,'G'=>18,'H'=>18];
    foreach ($widths as $col => $w) {
        $sheet->getColumnDimension($col)->setWidth($w);
    }

    $filename = 'Transaksi_Harian_' . $tanggal . '.xlsx';
    $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

    return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
    }, $filename, [
        'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);
}

// ── Private: PDF Transaksi Harian ─────────────────────────────────────────
private function exportTransaksiHarianPdf($tanggal, $transaksi, $totalDebit, $totalKredit)
{
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
        'bendahara.laporan-keuangan.pdf.transaksi-harian',
        compact('tanggal', 'transaksi', 'totalDebit', 'totalKredit')
    )->setPaper('A4', 'landscape');

    return $pdf->download('Transaksi_Harian_' . $tanggal . '.pdf');
}

// ── Private: Excel Buku Besar ─────────────────────────────────────────────
private function exportBukuBesarExcel($tahun, $selectedAkun, $entries, $totalDebit, $totalKredit, $saldoAkhir)
{
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Buku Besar');

    // Judul
    $sheet->mergeCells('A1:E1');
    $sheet->setCellValue('A1', 'BUKU BESAR — ' . $selectedAkun->kode . ' ' . $selectedAkun->nama);
    $sheet->mergeCells('A2:E2');
    $sheet->setCellValue('A2', 'Periode 1 Januari ' . $tahun . ' — 31 Desember ' . $tahun . '   |   LSP-KAP');

    foreach (['A1','A2'] as $cell) {
        $sheet->getStyle($cell)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'],
                            'size' => $cell === 'A1' ? 13 : 10],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '1a5276']],
        ]);
    }

    // Info akun
    $sheet->mergeCells('A3:E3');
    $sheet->setCellValue('A3', 'Tipe: ' . ($selectedAkun->tipe_label ?? $selectedAkun->tipe)
        . '   |   Saldo Normal: '
        . (in_array($selectedAkun->tipe, ['aset','beban']) ? 'Debit' : 'Kredit'));
    $sheet->getStyle('A3')->applyFromArray([
        'font'      => ['italic' => true, 'color' => ['rgb' => '666666'], 'size' => 9],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    ]);

    // Header
    $headers = ['No. Jurnal', 'Tanggal', 'Keterangan', 'Debit (Rp)', 'Kredit (Rp)', 'Saldo (Rp)'];
    $cols    = ['A','B','C','D','E','F'];
    foreach ($headers as $i => $h) {
        $sheet->setCellValue($cols[$i] . '5', $h);
    }
    $sheet->getStyle('A5:F5')->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2C3E50']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
    ]);

    // Data
    foreach ($entries as $i => $e) {
        $row = 6 + $i;
        $sheet->setCellValue("A{$row}", $e['nomor']);
        $sheet->setCellValue("B{$row}", $e['tanggal']);
        $sheet->setCellValue("C{$row}", $e['keterangan']);
        $sheet->setCellValue("D{$row}", $e['debit'] > 0 ? $e['debit'] : '');
        $sheet->setCellValue("E{$row}", $e['kredit'] > 0 ? $e['kredit'] : '');
        $sheet->setCellValue("F{$row}", $e['saldo']);

        foreach (['D','E','F'] as $col) {
            $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("{$col}{$row}")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        }
        if ($e['saldo'] < 0) {
            $sheet->getStyle("F{$row}")->getFont()->getColor()->setRGB('C0392B');
        }

        $bg = $i % 2 === 0 ? 'FFFFFF' : 'F9F9F9';
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
            'fill'    => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                          'startColor' => ['rgb' => $bg]],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                           'color' => ['rgb' => 'DDDDDD']]],
        ]);
    }

    // Total
    $totalRow = 6 + $entries->count();
    $sheet->setCellValue("C{$totalRow}", 'TOTAL');
    $sheet->setCellValue("D{$totalRow}", $totalDebit);
    $sheet->setCellValue("E{$totalRow}", $totalKredit);
    $sheet->setCellValue("F{$totalRow}", $saldoAkhir);
    $sheet->getStyle("A{$totalRow}:F{$totalRow}")->applyFromArray([
        'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill'    => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                      'startColor' => ['rgb' => '2C3E50']],
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
    ]);
    foreach (['D','E','F'] as $col) {
        $sheet->getStyle("{$col}{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
    }

    // Lebar kolom
    $widths = ['A'=>16,'B'=>12,'C'=>50,'D'=>18,'E'=>18,'F'=>18];
    foreach ($widths as $col => $w) {
        $sheet->getColumnDimension($col)->setWidth($w);
    }

    $filename = 'Buku_Besar_' . $selectedAkun->kode . '_' . $tahun . '.xlsx';
    $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

    return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
    }, $filename, [
        'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);
}

// ── Private: PDF Buku Besar ───────────────────────────────────────────────
private function exportBukuBesarPdf($tahun, $selectedAkun, $entries, $totalDebit, $totalKredit, $saldoAkhir)
{
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
        'bendahara.laporan-keuangan.pdf.buku-besar',
        compact('tahun', 'selectedAkun', 'entries', 'totalDebit', 'totalKredit', 'saldoAkhir')
    )->setPaper('A4', 'landscape');

    return $pdf->download('Buku_Besar_' . $selectedAkun->kode . '_' . $tahun . '.pdf');
}

private function getTipeTransaksi(string $refType): string
{
    return match(true) {
        str_contains($refType, 'Payment_piutang')   => 'piutang',
        str_contains($refType, 'Invoice_piutang')   => 'piutang',
        str_contains($refType, 'CollectivePayment') => 'pemasukan',
        str_contains($refType, 'Payment')           => 'pemasukan',
        str_contains($refType, 'Honor_dibuat')      => 'beban',
        str_contains($refType, 'Honor')             => 'honor',
        str_contains($refType, 'BiayaOperasional')  => 'biaya_ops',
        str_contains($refType, 'distribusi')        => 'distribusi',
        default                                     => 'umum',
    };
}
}