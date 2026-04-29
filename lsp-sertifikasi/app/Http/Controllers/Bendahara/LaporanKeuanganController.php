<?php
// app/Http/Controllers/Bendahara/LaporanKeuanganController.php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\AccountBalance;
use App\Models\Payment;
use App\Models\HonorPayment;
use App\Models\BiayaOperasional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanKeuanganController extends Controller
{
    // ── Index: pilih laporan ──────────────────────────────────────────────
    public function index(Request $request)
    {
        $tahun  = (int)($request->get('tahun', now()->year));
        $balance = AccountBalance::forTahun($tahun);
        $tahunList = $this->tahunList();

        return view('bendahara.laporan-keuangan.index', compact('tahun', 'tahunList', 'balance'));
    }

    // ── Form edit saldo manual ────────────────────────────────────────────
    public function editSaldo(Request $request)
    {
        $tahun   = (int)($request->get('tahun', now()->year));
        $balance = AccountBalance::forTahun($tahun);
        $tahunList = $this->tahunList();

        return view('bendahara.laporan-keuangan.edit-saldo', compact('tahun', 'tahunList', 'balance'));
    }

    public function updateSaldo(Request $request)
    {
        $tahun = (int)($request->get('tahun', now()->year));

        $validated = $request->validate([
            'kas'               => 'required|integer|min:0',
            'bank'              => 'required|integer|min:0',
            'perlengkapan'      => 'required|integer|min:0',
            'utang_operasional' => 'required|integer|min:0',
            'saldo_dana'        => 'required|integer|min:0',
        ]);

        $balance = AccountBalance::forTahun($tahun);
        $balance->update(array_merge($validated, ['diupdate_oleh' => auth()->id()]));

        return redirect()->route('bendahara.laporan-keuangan.index', ['tahun' => $tahun])
            ->with('success', 'Saldo berhasil diperbarui.');
    }

    // ── Laba Rugi ─────────────────────────────────────────────────────────
    public function labaRugi(Request $request)
    {
        $tahun   = (int)($request->get('tahun', now()->year));
        $balance = AccountBalance::forTahun($tahun);
        $tahunList = $this->tahunList();

        // Breakdown pendapatan per bulan
        $pendapatanBulan = Payment::selectRaw('MONTH(verified_at) as bulan, SUM(amount) as total')
            ->where('status', 'verified')
            ->whereYear('verified_at', $tahun)
            ->groupByRaw('MONTH(verified_at)')
            ->pluck('total', 'bulan')->toArray();

        // Breakdown beban per bulan
        $honorBulan = HonorPayment::selectRaw('MONTH(dibayar_at) as bulan, SUM(total) as total')
            ->whereIn('status', ['sudah_dibayar', 'dikonfirmasi'])
            ->whereYear('dibayar_at', $tahun)
            ->whereNotNull('dibayar_at')
            ->groupByRaw('MONTH(dibayar_at)')
            ->pluck('total', 'bulan')->toArray();

        $opsBulan = BiayaOperasional::selectRaw('MONTH(tanggal) as bulan, SUM(total) as total')
            ->whereYear('tanggal', $tahun)
            ->groupByRaw('MONTH(tanggal)')
            ->pluck('total', 'bulan')->toArray();

        // Breakdown pendapatan per skema
        $pendapatanSkema = Payment::selectRaw('skemas.name as skema, SUM(payments.amount) as total')
            ->join('asesmens', 'asesmens.id', '=', 'payments.asesmen_id')
            ->join('skemas', 'skemas.id', '=', 'asesmens.skema_id')
            ->where('payments.status', 'verified')
            ->whereYear('payments.verified_at', $tahun)
            ->groupBy('skemas.id', 'skemas.name')
            ->get();

        // Breakdown beban ops per uraian
        $bebanOpsDetail = BiayaOperasional::whereYear('tanggal', $tahun)
            ->orderByDesc('total')
            ->get();

        if ($request->get('export') === 'pdf') {
            return $this->exportLabaRugiPdf($tahun, $balance, $pendapatanSkema, $bebanOpsDetail);
        }

        return view('bendahara.laporan-keuangan.laba-rugi', compact(
            'tahun', 'tahunList', 'balance',
            'pendapatanBulan', 'honorBulan', 'opsBulan',
            'pendapatanSkema', 'bebanOpsDetail'
        ));
    }

    // ── Neraca ────────────────────────────────────────────────────────────
    public function neraca(Request $request)
    {
        $tahun   = (int)($request->get('tahun', now()->year));
        $balance = AccountBalance::forTahun($tahun);
        $tahunList = $this->tahunList();

        if ($request->get('export') === 'pdf') {
            return $this->exportNeracaPdf($tahun, $balance);
        }

        return view('bendahara.laporan-keuangan.neraca', compact('tahun', 'tahunList', 'balance'));
    }

    // ── Arus Kas ──────────────────────────────────────────────────────────
    public function arusKas(Request $request)
    {
        $tahun   = (int)($request->get('tahun', now()->year));
        $balance = AccountBalance::forTahun($tahun);
        $tahunList = $this->tahunList();

        // Aktivitas Operasi - Penerimaan
        $penerimaanSertifikasi = $balance->pendapatan;

        // Aktivitas Operasi - Pengeluaran
        $pembayaranHonor = $balance->beban_honor;
        $pembayaranOps   = $balance->beban_operasional;
        $pembayaranDistr = $balance->distribusi_yayasan;

        $kasOperasi = $penerimaanSertifikasi - $pembayaranHonor - $pembayaranOps - $pembayaranDistr;

        // Saldo kas awal & akhir
        // Cari saldo tahun lalu
        $balanceLalu = AccountBalance::where('tahun', $tahun - 1)->first();
        $kasAwal     = $balanceLalu ? ($balanceLalu->kas + $balanceLalu->bank) : 0;
        $kasAkhir    = $kasAwal + $kasOperasi;

        if ($request->get('export') === 'pdf') {
            return $this->exportArusKasPdf($tahun, $balance, $kasAwal, $kasAkhir, $kasOperasi,
                $penerimaanSertifikasi, $pembayaranHonor, $pembayaranOps, $pembayaranDistr);
        }

        return view('bendahara.laporan-keuangan.arus-kas', compact(
            'tahun', 'tahunList', 'balance',
            'kasAwal', 'kasAkhir', 'kasOperasi',
            'penerimaanSertifikasi', 'pembayaranHonor', 'pembayaranOps', 'pembayaranDistr'
        ));
    }

    // ── Perubahan Modal ───────────────────────────────────────────────────
    public function perubahanModal(Request $request)
    {
        $tahun     = (int)($request->get('tahun', now()->year));
        $balance   = AccountBalance::forTahun($tahun);
        $tahunList = $this->tahunList();

        $balanceLalu   = AccountBalance::where('tahun', $tahun - 1)->first();
        $saldoAwal     = $balanceLalu ? $balanceLalu->saldo_dana + ($balanceLalu->surplus - $balanceLalu->distribusi_yayasan) : $balance->saldo_dana;
        $surplus       = $balance->surplus;
        $distribusi    = $balance->distribusi_yayasan;
        $saldoAkhir    = $saldoAwal + $surplus - $distribusi;

        if ($request->get('export') === 'pdf') {
            return $this->exportPerubahanModalPdf($tahun, $balance, $saldoAwal, $surplus, $distribusi, $saldoAkhir);
        }

        return view('bendahara.laporan-keuangan.perubahan-modal', compact(
            'tahun', 'tahunList', 'balance',
            'saldoAwal', 'surplus', 'distribusi', 'saldoAkhir'
        ));
    }

    // ── Distribusi Yayasan ────────────────────────────────────────────────
    public function distribusi(Request $request)
    {
        $tahun     = (int)($request->get('tahun', now()->year));
        $balance   = AccountBalance::forTahun($tahun);
        $tahunList = $this->tahunList();

        return view('bendahara.laporan-keuangan.distribusi', compact('tahun', 'tahunList', 'balance'));
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

        return back()->with('success', 'Data distribusi berhasil disimpan.');
    }

    public function jurnalBalik(Request $request)
    {
        $tahun   = (int)($request->get('tahun', now()->year));
        $balance = AccountBalance::forTahun($tahun);

        if ($balance->jurnal_balik_done) {
            return back()->with('error', 'Jurnal balik untuk tahun ' . $tahun . ' sudah dilakukan.');
        }

        // Jurnal balik: reset distribusi ke hutang distribusi
        $balance->update([
            'hutang_distribusi'  => $balance->distribusi_yayasan,
            'distribusi_yayasan' => 0,
            'jurnal_balik_done'  => true,
            'diupdate_oleh'      => auth()->id(),
        ]);

        return back()->with('success', 'Jurnal balik distribusi berhasil dilakukan.');
    }

    // ── Transaksi Harian ──────────────────────────────────────────────────
    public function transaksiHarian(Request $request)
    {
        $tanggal   = $request->get('tanggal', today()->toDateString());
        $tahunList = $this->tahunList();

        // Gabung semua transaksi hari itu
        $pemasukan = Payment::with(['asesmen.tuk', 'asesmen.skema'])
            ->where('status', 'verified')
            ->whereDate('verified_at', $tanggal)
            ->get()
            ->map(fn($p) => [
                'waktu'      => $p->verified_at->format('H:i'),
                'tipe'       => 'pemasukan',
                'akun_debit' => 'Kas/Bank',
                'akun_kredit'=> 'Pendapatan Sertifikasi',
                'keterangan' => ($p->asesmen->full_name ?? '-') . ' — ' . ($p->asesmen->skema->name ?? '-'),
                'debit'      => $p->amount,
                'kredit'     => 0,
            ]);

        $honor = HonorPayment::with('asesor')
            ->whereIn('status', ['sudah_dibayar', 'dikonfirmasi'])
            ->whereDate('dibayar_at', $tanggal)
            ->get()
            ->map(fn($h) => [
                'waktu'      => $h->dibayar_at->format('H:i'),
                'tipe'       => 'honor',
                'akun_debit' => 'Beban Honor Asesor',
                'akun_kredit'=> 'Kas/Bank',
                'keterangan' => 'Honor ' . ($h->asesor->nama ?? '-') . ' — ' . $h->nomor_kwitansi,
                'debit'      => 0,
                'kredit'     => $h->total,
            ]);

        $ops = BiayaOperasional::whereDate('tanggal', $tanggal)
            ->get()
            ->map(fn($b) => [
                'waktu'      => $b->created_at->format('H:i'),
                'tipe'       => 'biaya_ops',
                'akun_debit' => 'Beban Operasional',
                'akun_kredit'=> 'Kas/Bank',
                'keterangan' => $b->uraian . ' — ' . $b->nama_penerima,
                'debit'      => 0,
                'kredit'     => $b->total,
            ]);

        $transaksi = $pemasukan->concat($honor)->concat($ops)->sortBy('waktu')->values();

        $totalDebit  = $transaksi->sum('debit');
        $totalKredit = $transaksi->sum('kredit');

        return view('bendahara.laporan-keuangan.transaksi-harian', compact(
            'tanggal', 'tahunList', 'transaksi', 'totalDebit', 'totalKredit'
        ));
    }

    // ── Buku Besar ────────────────────────────────────────────────────────
    public function bukuBesar(Request $request)
    {
        $tahun   = (int)($request->get('tahun', now()->year));
        $akun    = $request->get('akun', 'pendapatan_sertifikasi');
        $tahunList = $this->tahunList();

        $akunList = [
            'pendapatan_sertifikasi' => 'Pendapatan Sertifikasi',
            'beban_honor'            => 'Beban Honor Asesor',
            'beban_operasional'      => 'Beban Operasional',
            'kas_bank'               => 'Kas / Bank',
            'piutang_asesi'          => 'Piutang Asesi',
            'utang_honor'            => 'Utang Honor Asesor',
            'distribusi_yayasan'     => 'Distribusi ke Yayasan',
        ];

        $entries = $this->getBukuBesarEntries($akun, $tahun);

        return view('bendahara.laporan-keuangan.buku-besar', compact(
            'tahun', 'tahunList', 'akun', 'akunList', 'entries'
        ));
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function getBukuBesarEntries(string $akun, int $tahun): \Illuminate\Support\Collection
    {
        return match($akun) {
            'pendapatan_sertifikasi' => Payment::with(['asesmen.skema'])
                ->where('status', 'verified')
                ->whereYear('verified_at', $tahun)
                ->orderBy('verified_at')
                ->get()
                ->map(fn($p) => [
                    'tanggal'    => $p->verified_at->format('d/m/Y'),
                    'keterangan' => ($p->asesmen->full_name ?? '-') . ' — ' . ($p->asesmen->skema->name ?? '-'),
                    'debit'      => 0,
                    'kredit'     => $p->amount,
                    'saldo'      => 0,
                ]),

            'beban_honor' => HonorPayment::with('asesor')
                ->whereIn('status', ['sudah_dibayar', 'dikonfirmasi'])
                ->whereYear('dibayar_at', $tahun)
                ->whereNotNull('dibayar_at')
                ->orderBy('dibayar_at')
                ->get()
                ->map(fn($h) => [
                    'tanggal'    => $h->dibayar_at->format('d/m/Y'),
                    'keterangan' => 'Honor ' . ($h->asesor->nama ?? '-') . ' — ' . $h->nomor_kwitansi,
                    'debit'      => $h->total,
                    'kredit'     => 0,
                    'saldo'      => 0,
                ]),

            'beban_operasional' => BiayaOperasional::whereYear('tanggal', $tahun)
                ->orderBy('tanggal')
                ->get()
                ->map(fn($b) => [
                    'tanggal'    => $b->tanggal->format('d/m/Y'),
                    'keterangan' => $b->uraian . ' — ' . $b->nama_penerima,
                    'debit'      => $b->total,
                    'kredit'     => 0,
                    'saldo'      => 0,
                ]),

            'piutang_asesi' => Payment::with(['asesmen.skema'])
                ->whereIn('status', ['pending', 'uploaded'])
                ->whereYear('created_at', $tahun)
                ->orderBy('created_at')
                ->get()
                ->map(fn($p) => [
                    'tanggal'    => $p->created_at->format('d/m/Y'),
                    'keterangan' => ($p->asesmen->full_name ?? '-') . ' — belum lunas',
                    'debit'      => $p->amount,
                    'kredit'     => 0,
                    'saldo'      => 0,
                ]),

            'utang_honor' => HonorPayment::with('asesor')
                ->where('status', 'menunggu_pembayaran')
                ->whereYear('created_at', $tahun)
                ->orderBy('created_at')
                ->get()
                ->map(fn($h) => [
                    'tanggal'    => $h->created_at->format('d/m/Y'),
                    'keterangan' => 'Honor terutang ' . ($h->asesor->nama ?? '-'),
                    'debit'      => 0,
                    'kredit'     => $h->total,
                    'saldo'      => 0,
                ]),

            default => collect(),
        };
    }

    private function tahunList(): \Illuminate\Support\Collection
    {
        $tahunDb = AccountBalance::orderByDesc('tahun')->pluck('tahun');
        $tahunPay = Payment::selectRaw('YEAR(verified_at) as tahun')
            ->whereNotNull('verified_at')->distinct()->pluck('tahun');
        return $tahunDb->concat($tahunPay)->unique()->sortDesc()->values()
            ->whenEmpty(fn($c) => collect([now()->year]));
    }

    // ── PDF exports ───────────────────────────────────────────────────────

    private function exportLabaRugiPdf($tahun, $balance, $pendapatanSkema, $bebanOpsDetail)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('bendahara.laporan-keuangan.pdf.laba-rugi', compact(
            'tahun', 'balance', 'pendapatanSkema', 'bebanOpsDetail'
        ))->setPaper('A4', 'portrait');
        return $pdf->download("Laporan_Laba_Rugi_{$tahun}.pdf");
    }

    private function exportNeracaPdf($tahun, $balance)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('bendahara.laporan-keuangan.pdf.neraca', compact(
            'tahun', 'balance'
        ))->setPaper('A4', 'landscape');
        return $pdf->download("Neraca_{$tahun}.pdf");
    }

    private function exportArusKasPdf($tahun, $balance, $kasAwal, $kasAkhir, $kasOperasi,
        $penerimaanSertifikasi, $pembayaranHonor, $pembayaranOps, $pembayaranDistr)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('bendahara.laporan-keuangan.pdf.arus-kas', compact(
            'tahun', 'balance', 'kasAwal', 'kasAkhir', 'kasOperasi',
            'penerimaanSertifikasi', 'pembayaranHonor', 'pembayaranOps', 'pembayaranDistr'
        ))->setPaper('A4', 'portrait');
        return $pdf->download("Arus_Kas_{$tahun}.pdf");
    }

    private function exportPerubahanModalPdf($tahun, $balance, $saldoAwal, $surplus, $distribusi, $saldoAkhir)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('bendahara.laporan-keuangan.pdf.perubahan-modal', compact(
            'tahun', 'balance', 'saldoAwal', 'surplus', 'distribusi', 'saldoAkhir'
        ))->setPaper('A4', 'portrait');
        return $pdf->download("Perubahan_Modal_{$tahun}.pdf");
    }
}