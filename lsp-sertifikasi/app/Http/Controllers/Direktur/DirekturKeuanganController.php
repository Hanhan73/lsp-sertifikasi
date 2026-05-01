<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Bendahara\LaporanKeuanganController as BendaharaLaporanController;
use App\Models\AccountBalance;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\HonorPayment;
use App\Models\BiayaOperasional;
use App\Models\Invoice;
use App\Models\CollectivePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DirekturKeuanganController extends BendaharaLaporanController
{
    // ── Dashboard ─────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $tahun     = (int)($request->get('tahun', now()->year));
        $balance   = AccountBalance::forTahun($tahun);
        $tahunList = $this->tahunListPublic();
        $summary   = $this->summaryPublic($tahun, $balance);

        return view('direktur.keuangan.index', compact('tahun', 'tahunList', 'balance', 'summary'));
    }

    // ── Laba Rugi ─────────────────────────────────────────────────────────
    public function labaRugi(Request $request)
    {
        $tahun     = (int)($request->get('tahun', now()->year));
        $tahunList = $this->tahunListPublic();
        $balance   = AccountBalance::forTahun($tahun);
        $summary   = $this->summaryPublic($tahun, $balance);

        $pendapatanIndividu = Payment::selectRaw('skemas.name as skema, SUM(payments.amount) as total')
            ->join('asesmens', 'asesmens.id', '=', 'payments.asesmen_id')
            ->join('skemas', 'skemas.id', '=', 'asesmens.skema_id')
            ->where('payments.status', 'verified')
            ->whereYear('payments.verified_at', $tahun)
            ->groupBy('skemas.id', 'skemas.name')
            ->get()->keyBy('skema');

        $pendapatanKolektif = Invoice::whereIn('status', ['sent', 'paid'])
            ->whereYear('issued_at', $tahun)->get()
            ->flatMap(fn($inv) => collect($inv->items))
            ->groupBy('skema_name')
            ->map(fn($items, $nama) => (object)['skema' => $nama, 'total' => $items->sum('subtotal')]);

        $pendapatanSkema = $pendapatanKolektif->map(function ($kol) use ($pendapatanIndividu) {
            $ind = $pendapatanIndividu->get($kol->skema);
            return (object)['skema' => $kol->skema, 'total' => $kol->total + ($ind ? $ind->total : 0)];
        });
        foreach ($pendapatanIndividu as $nama => $ind) {
            if (!$pendapatanSkema->has($nama)) {
                $pendapatanSkema->put($nama, (object)['skema' => $nama, 'total' => $ind->total]);
            }
        }
        $pendapatanSkema = $pendapatanSkema->sortByDesc('total')->values();

        $bebanOpsDetail     = BiayaOperasional::whereYear('tanggal', $tahun)->orderByDesc('total')->get();
        $pendapatanPerBulan = $this->saldoAkunPublic('4-001', $tahun);
        $honorPerBulan      = $this->saldoAkunPublic('5-001', $tahun);
        $opsPerBulan        = $this->saldoAkunPublic('5-002', $tahun);

        if ($request->get('export') === 'pdf') {
            return $this->exportLabaRugiPdfPublic($tahun, $summary, $pendapatanSkema, $bebanOpsDetail);
        }

        return view('direktur.keuangan.laba-rugi', compact(
            'tahun', 'tahunList', 'balance', 'summary',
            'pendapatanSkema', 'bebanOpsDetail',
            'pendapatanPerBulan', 'honorPerBulan', 'opsPerBulan'
        ));
    }

    // ── Neraca ────────────────────────────────────────────────────────────
    public function neraca(Request $request)
    {
        $tahun         = (int)($request->get('tahun', now()->year));
        $balance       = AccountBalance::forTahun($tahun);
        $tahunList     = $this->tahunListPublic();
        $summary       = $this->summaryPublic($tahun, $balance);
        $saldoAwalBank = (int)$balance->saldo_awal_bank;
        $mutasiBank    = $this->saldoAkunPublic('1-002', $tahun);
        $bank          = $saldoAwalBank + $mutasiBank;
        $piutangAsesi  = $this->saldoAkunPublic('1-003', $tahun);
        $utangHonor    = $this->saldoAkunPublic('2-001', $tahun);
        $surplus       = $summary['pendapatan'] - $summary['beban_honor'] - $summary['beban_ops'];
        $totalAset     = $balance->kas + $bank + $balance->perlengkapan + $piutangAsesi;
        $totalKewajiban  = $utangHonor + $balance->utang_operasional;
        $totalEkuitas    = $balance->saldo_dana + $surplus - $summary['distribusi'];
        $totalKewEkuitas = $totalKewajiban + $totalEkuitas;

        if ($request->get('export') === 'pdf') {
            return $this->exportNeracaPdfPublic($tahun, $balance, compact(
                'piutangAsesi', 'utangHonor', 'surplus', 'totalAset',
                'totalKewajiban', 'totalEkuitas', 'totalKewEkuitas', 'bank'
            ));
        }

        return view('direktur.keuangan.neraca', compact(
            'tahun', 'tahunList', 'balance', 'summary',
            'piutangAsesi', 'utangHonor', 'surplus', 'totalAset',
            'totalKewajiban', 'totalEkuitas', 'totalKewEkuitas',
            'bank', 'mutasiBank', 'saldoAwalBank'
        ));
    }

    // ── Arus Kas ──────────────────────────────────────────────────────────
    public function arusKas(Request $request)
    {
        $tahun     = (int)($request->get('tahun', now()->year));
        $balance   = AccountBalance::forTahun($tahun);
        $tahunList = $this->tahunListPublic();
        $summary   = $this->summaryPublic($tahun, $balance);

        $penerimaanSertifikasi = $summary['pendapatan'];
        $akunBank = ChartOfAccount::where('kode', '1-002')->first();

        $pembayaranHonor = $akunBank ? (int)JournalEntryLine::where('chart_of_account_id', $akunBank->id)
            ->where('kredit', '>', 0)
            ->whereHas('entry', fn($q) => $q->whereYear('tanggal', $tahun)->where('ref_type', HonorPayment::class))
            ->sum('kredit') : 0;

        $pembayaranOps = $akunBank ? (int)JournalEntryLine::where('chart_of_account_id', $akunBank->id)
            ->where('kredit', '>', 0)
            ->whereHas('entry', fn($q) => $q->whereYear('tanggal', $tahun)->where('ref_type', BiayaOperasional::class))
            ->sum('kredit') : 0;

        $pembayaranDistr = $summary['distribusi'];
        $kasOperasi      = $penerimaanSertifikasi - $pembayaranHonor - $pembayaranOps - $pembayaranDistr;
        $balanceLalu     = AccountBalance::where('tahun', $tahun - 1)->first();
        $kasAwal         = $balanceLalu ? ($balanceLalu->kas + $balanceLalu->bank) : 0;
        $kasAkhir        = $kasAwal + $kasOperasi;

        if ($request->get('export') === 'pdf') {
            return $this->exportArusKasPdfPublic(
                $tahun, $balance, $kasAwal, $kasAkhir, $kasOperasi,
                $penerimaanSertifikasi, $pembayaranHonor, $pembayaranOps, $pembayaranDistr
            );
        }

        return view('direktur.keuangan.arus-kas', compact(
            'tahun', 'tahunList', 'balance', 'summary',
            'kasAwal', 'kasAkhir', 'kasOperasi',
            'penerimaanSertifikasi', 'pembayaranHonor', 'pembayaranOps', 'pembayaranDistr'
        ));
    }

    // ── Perubahan Modal ───────────────────────────────────────────────────
    public function perubahanModal(Request $request)
    {
        $tahun       = (int)($request->get('tahun', now()->year));
        $balance     = AccountBalance::forTahun($tahun);
        $tahunList   = $this->tahunListPublic();
        $summary     = $this->summaryPublic($tahun, $balance);
        $balanceLalu = AccountBalance::where('tahun', $tahun - 1)->first();
        $summaryLalu = $balanceLalu ? $this->summaryPublic($tahun - 1, $balanceLalu) : null;

        $saldoAwal  = $balanceLalu
            ? $balanceLalu->saldo_dana + ($summaryLalu['pendapatan'] - $summaryLalu['beban_honor'] - $summaryLalu['beban_ops'] - $summaryLalu['distribusi'])
            : $balance->saldo_dana;

        $surplus    = $summary['pendapatan'] - $summary['beban_honor'] - $summary['beban_ops'];
        $distribusi = $summary['distribusi'];
        $saldoAkhir = $saldoAwal + $surplus - $distribusi;

        if ($request->get('export') === 'pdf') {
            return $this->exportPerubahanModalPdfPublic($tahun, $balance, $saldoAwal, $surplus, $distribusi, $saldoAkhir);
        }

        return view('direktur.keuangan.perubahan-modal', compact(
            'tahun', 'tahunList', 'balance', 'summary',
            'saldoAwal', 'surplus', 'distribusi', 'saldoAkhir'
        ));
    }

    // ── Distribusi ────────────────────────────────────────────────────────
    public function distribusi(Request $request)
    {
        $tahun     = (int)($request->get('tahun', now()->year));
        $balance   = AccountBalance::forTahun($tahun);
        $tahunList = $this->tahunListPublic();
        $summary   = $this->summaryPublic($tahun, $balance);

        return view('direktur.keuangan.distribusi', compact('tahun', 'tahunList', 'balance', 'summary'));
    }

    // ── Transaksi Harian ──────────────────────────────────────────────────
    public function transaksiHarian(Request $request)
    {
        $tanggal   = $request->get('tanggal', today()->toDateString());
        $tahunList = $this->tahunListPublic();

        $entries = JournalEntry::with(['lines.akun'])
            ->whereDate('tanggal', $tanggal)
            ->orderBy('created_at')->get();

        $transaksi = collect();
        foreach ($entries as $entry) {
            $debitLines  = $entry->lines->where('debit', '>', 0);
            $kreditLines = $entry->lines->where('kredit', '>', 0);
            $transaksi->push([
                'waktu'       => $entry->created_at->format('H:i'),
                'nomor'       => $entry->nomor,
                'tipe'        => $this->getTipeTransaksiPublic($entry->ref_type ?? ''),
                'akun_debit'  => $debitLines->map(fn($l) => $l->akun->kode . ' ' . $l->akun->nama)->implode(', '),
                'akun_kredit' => $kreditLines->map(fn($l) => $l->akun->kode . ' ' . $l->akun->nama)->implode(', '),
                'keterangan'  => $entry->keterangan,
                'debit'       => (int)$entry->lines->sum('debit'),
                'kredit'      => (int)$entry->lines->sum('kredit'),
            ]);
        }

        $totalDebit  = $transaksi->sum('debit');
        $totalKredit = $transaksi->sum('kredit');

        return view('direktur.keuangan.transaksi-harian', compact(
            'tanggal', 'tahunList', 'transaksi', 'totalDebit', 'totalKredit'
        ));
    }

    // ── Buku Besar ────────────────────────────────────────────────────────
    public function bukuBesar(Request $request)
    {
        $tahun     = (int)($request->get('tahun', now()->year));
        $akunId    = $request->get('akun_id');
        $tahunList = $this->tahunListPublic();
        $akunList  = ChartOfAccount::active()->orderBy('urutan')->orderBy('kode')->get();

        $selectedAkun = $akunId ? $akunList->firstWhere('id', $akunId) : $akunList->first();
        $entries      = $selectedAkun ? $this->getBukuBesarPublic($selectedAkun, $tahun) : collect();

        return view('direktur.keuangan.buku-besar', compact(
            'tahun', 'tahunList', 'akunList', 'selectedAkun', 'entries'
        ));
    }

    // ── Export Transaksi Harian ───────────────────────────────────────────
    public function exportTransaksiHarian(Request $request)
    {
        return parent::exportTransaksiHarian($request);
    }

    // ── Export Buku Besar ─────────────────────────────────────────────────
    public function exportBukuBesar(Request $request)
    {
        return parent::exportBukuBesar($request);
    }

    // ── CoA ───────────────────────────────────────────────────────────────
    public function coa(Request $request)
    {
        $tipeList = ChartOfAccount::tipeList();
        $akuns    = ChartOfAccount::when($request->tipe, fn($q) => $q->where('tipe', $request->tipe))
            ->orderBy('urutan')->orderBy('kode')->get();
        $grouped  = $akuns->groupBy('tipe');

        return view('direktur.keuangan.coa', compact('tipeList', 'akuns', 'grouped'));
    }

    // ── Rekap Pendapatan ──────────────────────────────────────────────────
    public function rekapPendapatan(Request $request)
    {
        $tahun = (int)($request->get('tahun', now()->year));

        // Ambil data dari buildData via reflection
        $rekapCtrl = app(\App\Http\Controllers\Bendahara\RekapPendapatanController::class);
        $ref = new \ReflectionMethod($rekapCtrl, 'buildData');
        $ref->setAccessible(true);
        $data = $ref->invoke($rekapCtrl, $tahun);
        extract($data);

        $totalPemasukan = array_sum($dataPemasukan);
        $totalHonor     = array_sum($dataHonor);
        $totalBiayaOps  = array_sum($dataBiayaOps);
        $totalSaldo     = $totalPemasukan - $totalHonor - $totalBiayaOps;

        // Rincian per bulan
        $rincianPerBulan = [];
        for ($m = 1; $m <= 12; $m++) {
            $pemasukanDetail = Payment::with(['asesmen.tuk', 'asesmen.skema'])
                ->where('status', 'verified')
                ->whereYear('verified_at', $tahun)->whereMonth('verified_at', $m)
                ->get()->map(fn($p) => [
                    'tipe'       => 'pemasukan',
                    'tanggal'    => $p->verified_at?->format('d/m/Y'),
                    'keterangan' => ($p->asesmen->full_name ?? '-') . ' — ' . ($p->asesmen->skema->name ?? '-'),
                    'sub'        => $p->asesmen->tuk->name ?? '-',
                    'jumlah'     => (int)$p->amount,
                ]);

            $invoiceDetail = Invoice::whereIn('status', ['sent', 'paid'])
                ->whereYear('issued_at', $tahun)->whereMonth('issued_at', $m)
                ->get()->map(fn($inv) => [
                    'tipe'       => 'pemasukan',
                    'tanggal'    => $inv->issued_at->format('d/m/Y'),
                    'keterangan' => 'Invoice Kolektif — ' . $inv->recipient_name,
                    'sub'        => $inv->invoice_number,
                    'jumlah'     => (int)$inv->total_amount,
                ]);

            $honorDetail = DB::table('honor_payments')
                ->join('asesors', 'asesors.id', '=', 'honor_payments.asesor_id')
                ->select('honor_payments.dibayar_at', 'honor_payments.nomor_kwitansi', 'honor_payments.total', 'asesors.nama')
                ->whereIn('honor_payments.status', ['sudah_dibayar', 'dikonfirmasi'])
                ->whereYear('honor_payments.dibayar_at', $tahun)->whereMonth('honor_payments.dibayar_at', $m)
                ->get()->map(fn($r) => [
                    'tipe'       => 'honor',
                    'tanggal'    => \Carbon\Carbon::parse($r->dibayar_at)->format('d/m/Y'),
                    'keterangan' => 'Honor Asesor — ' . $r->nama,
                    'sub'        => $r->nomor_kwitansi,
                    'jumlah'     => (int)$r->total,
                ]);

            $biayaOpsDetail = BiayaOperasional::whereYear('tanggal', $tahun)->whereMonth('tanggal', $m)
                ->get()->map(fn($b) => [
                    'tipe'       => 'biaya_ops',
                    'tanggal'    => $b->tanggal->format('d/m/Y'),
                    'keterangan' => $b->uraian,
                    'sub'        => 'Penerima: ' . $b->nama_penerima,
                    'jumlah'     => (int)$b->total,
                ]);

            $rincianPerBulan[$m] = $pemasukanDetail
                ->concat($invoiceDetail)->concat($honorDetail)->concat($biayaOpsDetail)
                ->sortBy('tanggal')->values();
        }

        $breakdownJenis = Payment::query()
            ->selectRaw('SUM(CASE WHEN asesmens.is_collective = 0 THEN payments.amount ELSE 0 END) as mandiri,
                         SUM(CASE WHEN asesmens.is_collective = 1 THEN payments.amount ELSE 0 END) as kolektif,
                         COUNT(*) as total_transaksi')
            ->join('asesmens', 'asesmens.id', '=', 'payments.asesmen_id')
            ->where('payments.status', 'verified')
            ->whereYear('payments.verified_at', $tahun)->first();

        $breakdownTuk = Payment::query()
            ->selectRaw('tuks.name as tuk_name, COUNT(*) as jumlah, SUM(payments.amount) as total')
            ->join('asesmens', 'asesmens.id', '=', 'payments.asesmen_id')
            ->join('tuks', 'tuks.id', '=', 'asesmens.tuk_id')
            ->where('payments.status', 'verified')
            ->whereYear('payments.verified_at', $tahun)
            ->groupBy('tuks.id', 'tuks.name')->orderByDesc('total')->get();

        $breakdownSkema = Payment::query()
            ->selectRaw('skemas.name as skema_name, COUNT(*) as jumlah, SUM(payments.amount) as total')
            ->join('asesmens', 'asesmens.id', '=', 'payments.asesmen_id')
            ->join('skemas', 'skemas.id', '=', 'asesmens.skema_id')
            ->where('payments.status', 'verified')
            ->whereYear('payments.verified_at', $tahun)
            ->groupBy('skemas.id', 'skemas.name')->orderByDesc('total')->get();

        $transaksiTerbaru = Payment::with(['asesmen.tuk', 'asesmen.skema', 'asesmen'])
            ->where('status', 'verified')->whereYear('verified_at', $tahun)
            ->orderByDesc('verified_at')->limit(50)->get();

        $tahunList = JournalEntry::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()->orderByDesc('tahun')->pluck('tahun');
        if ($tahunList->isEmpty()) $tahunList = collect([now()->year]);

        return view('direktur.keuangan.rekap-pendapatan', compact(
            'tahun', 'tahunList',
            'bulanLabels', 'dataPemasukan', 'dataHonor', 'dataBiayaOps', 'dataSaldo',
            'totalPemasukan', 'totalHonor', 'totalBiayaOps', 'totalSaldo',
            'breakdownJenis', 'breakdownTuk', 'breakdownSkema',
            'transaksiTerbaru', 'rincianPerBulan'
        ));
    }

    // ── Biaya Operasional ─────────────────────────────────────────────────
    public function biayaOperasional(Request $request)
    {
        $query = BiayaOperasional::query();
        if ($request->bulan) $query->whereMonth('tanggal', $request->bulan);
        if ($request->tahun) $query->whereYear('tanggal', $request->tahun);

        $biayaOps  = $query->orderByDesc('tanggal')->paginate(30);
        $tahunList = BiayaOperasional::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()->orderByDesc('tahun')->pluck('tahun');

        return view('direktur.keuangan.biaya-operasional', compact('biayaOps', 'tahunList'));
    }

    // ── Pembayaran Mandiri ────────────────────────────────────────────────
    public function pembayaranMandiri(Request $request)
    {
        $query = Payment::with(['asesmen.skema', 'asesmen.tuk', 'asesmen.user', 'verifier'])
            ->whereHas('asesmen', fn($q) => $q->where('is_collective', false));

        if ($request->status) $query->where('status', $request->status);
        if ($request->search) {
            $search = $request->search;
            $query->whereHas('asesmen', fn($q) => $q
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('nik', 'like', "%{$search}%"));
        }

        $payments = $query->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'rejected' THEN 1 WHEN 'verified' THEN 2 END")
            ->orderBy('created_at', 'desc')->paginate(20);

        return view('direktur.keuangan.pembayaran-mandiri', compact('payments'));
    }

    // ── Pembayaran Kolektif ───────────────────────────────────────────────
    public function pembayaranKolektif()
    {
        $tuks = \App\Models\Asesmen::where('is_collective', true)
            ->select('tuk_id',
                DB::raw('COUNT(DISTINCT collective_batch_id) as jumlah_batch'),
                DB::raw('COUNT(*) as jumlah_asesi'))
            ->groupBy('tuk_id')->with('tuk')->get()
            ->map(function ($row) {
                $row->pending_invoice  = Invoice::where('tuk_id', $row->tuk_id)->where('status', 'draft')->count();
                $row->pending_angsuran = CollectivePayment::whereHas('invoice', fn($q) => $q->where('tuk_id', $row->tuk_id))
                    ->where('status', 'pending')->whereNotNull('proof_path')->count();
                return $row;
            });

        return view('direktur.keuangan.pembayaran-kolektif', compact('tuks'));
    }

    public function pembayaranKolektifTuk(\App\Models\Tuk $tuk)
    {
        $batches = \App\Models\Asesmen::where('is_collective', true)->where('tuk_id', $tuk->id)
            ->select('collective_batch_id', 'tuk_id',
                DB::raw('COUNT(*) as jumlah_asesi'),
                DB::raw('MIN(registration_date) as tanggal_daftar'))
            ->groupBy('collective_batch_id', 'tuk_id')->orderByDesc('tanggal_daftar')->get()
            ->map(function ($b) {
                $b->skema_names = \App\Models\Asesmen::where('collective_batch_id', $b->collective_batch_id)
                    ->with('skema')->get()->pluck('skema.name')->unique()->filter()->values();
                $b->invoice = Invoice::where('tuk_id', $b->tuk_id)
                    ->whereJsonContains('batch_ids', $b->collective_batch_id)
                    ->with('collectivePayments')->first();
                return $b;
            });

        $invoices = Invoice::where('tuk_id', $tuk->id)->with('collectivePayments')
            ->orderByDesc('issued_at')->get()
            ->map(function ($inv) {
                $inv->batch_count = count($inv->batch_ids);
                $inv->total_asesi = \App\Models\Asesmen::whereIn('collective_batch_id', $inv->batch_ids)->count();
                $inv->skema_names = \App\Models\Asesmen::whereIn('collective_batch_id', $inv->batch_ids)
                    ->with('skema')->get()->pluck('skema.name')->unique()->filter()->values();
                return $inv;
            });

        return view('direktur.keuangan.pembayaran-kolektif-tuk', compact('tuk', 'batches', 'invoices'));
    }

    public function pembayaranKolektifDetail(Invoice $invoice)
    {
        $invoice->load('collectivePayments', 'tuk');
        $tuk                = $invoice->tuk;
        $asesmens           = \App\Models\Asesmen::whereIn('collective_batch_id', $invoice->batch_ids)
            ->with(['skema', 'tuk'])->get();
        $collectivePayments = $invoice->collectivePayments;
        $skemaGroups        = collect($invoice->items);

        return view('direktur.keuangan.pembayaran-kolektif-detail', compact(
            'invoice', 'tuk', 'asesmens', 'skemaGroups', 'collectivePayments'
        ));
    }

    // ── Honor ─────────────────────────────────────────────────────────────
    public function honor()
    {
        $asesors = \App\Models\Asesor::whereHas('schedules', fn($q) => $q->whereHas('beritaAcara'))
            ->with(['schedules' => fn($q) => $q->whereHas('beritaAcara')->with(['skema', 'tuk', 'beritaAcara'])])
            ->orderBy('nama')->get();

        return view('direktur.keuangan.honor', compact('asesors'));
    }

    public function honorDetail(\App\Models\Asesor $asesor)
    {
        $riwayat = HonorPayment::where('asesor_id', $asesor->id)
            ->with(['details.schedule.skema', 'details.schedule.tuk'])->latest()->get();

        return view('direktur.keuangan.honor-detail', compact('asesor', 'riwayat'));
    }

    public function honorPayment(HonorPayment $honor)
    {
        $honor->load(['asesor.user', 'details.schedule.skema', 'details.schedule.tuk', 'dibuatOleh', 'dibayarOleh']);
        return view('direktur.keuangan.honor-payment', compact('honor'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // Reflection wrappers untuk private methods parent
    // ══════════════════════════════════════════════════════════════════════

    protected function summaryPublic(int $tahun, ?AccountBalance $balance = null): array
    {
        $ref = new \ReflectionMethod(parent::class, 'summaryDariJurnal');
        $ref->setAccessible(true);
        return $ref->invoke($this, $tahun, $balance);
    }

    protected function saldoAkunPublic(string $kode, int $tahun): int
    {
        $ref = new \ReflectionMethod(parent::class, 'saldoAkun');
        $ref->setAccessible(true);
        return $ref->invoke($this, $kode, $tahun);
    }

    protected function getBukuBesarPublic(ChartOfAccount $akun, int $tahun): \Illuminate\Support\Collection
    {
        $ref = new \ReflectionMethod(parent::class, 'getBukuBesarEntries');
        $ref->setAccessible(true);
        return $ref->invoke($this, $akun, $tahun);
    }

    protected function tahunListPublic(): \Illuminate\Support\Collection
    {
        $ref = new \ReflectionMethod(parent::class, 'tahunList');
        $ref->setAccessible(true);
        return $ref->invoke($this);
    }

    protected function getTipeTransaksiPublic(string $refType): string
    {
        $ref = new \ReflectionMethod(parent::class, 'getTipeTransaksi');
        $ref->setAccessible(true);
        return $ref->invoke($this, $refType);
    }

    protected function exportLabaRugiPdfPublic($tahun, $summary, $pendapatanSkema, $bebanOpsDetail)
    {
        $ref = new \ReflectionMethod(parent::class, 'exportLabaRugiPdf');
        $ref->setAccessible(true);
        return $ref->invoke($this, $tahun, $summary, $pendapatanSkema, $bebanOpsDetail);
    }

    protected function exportNeracaPdfPublic($tahun, $balance, $neraca)
    {
        $ref = new \ReflectionMethod(parent::class, 'exportNeracaPdf');
        $ref->setAccessible(true);
        return $ref->invoke($this, $tahun, $balance, $neraca);
    }

    protected function exportArusKasPdfPublic($tahun, $balance, $kasAwal, $kasAkhir, $kasOperasi, $penerimaanSertifikasi, $pembayaranHonor, $pembayaranOps, $pembayaranDistr)
    {
        $ref = new \ReflectionMethod(parent::class, 'exportArusKasPdf');
        $ref->setAccessible(true);
        return $ref->invoke($this, $tahun, $balance, $kasAwal, $kasAkhir, $kasOperasi, $penerimaanSertifikasi, $pembayaranHonor, $pembayaranOps, $pembayaranDistr);
    }

    protected function exportPerubahanModalPdfPublic($tahun, $balance, $saldoAwal, $surplus, $distribusi, $saldoAkhir)
    {
        $ref = new \ReflectionMethod(parent::class, 'exportPerubahanModalPdf');
        $ref->setAccessible(true);
        return $ref->invoke($this, $tahun, $balance, $saldoAwal, $surplus, $distribusi, $saldoAkhir);
    }
}