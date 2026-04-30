<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\BiayaOperasional;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RekapPendapatanController extends Controller
{
    public function index(Request $request)
{
    $tahun = (int)($request->get('tahun', now()->year));

    // Angka utama dari jurnal via buildData
    $data  = $this->buildData($tahun);
    extract($data);

    $totalPemasukan = array_sum($dataPemasukan);
    $totalHonor     = array_sum($dataHonor);
    $totalBiayaOps  = array_sum($dataBiayaOps);
    $totalSaldo     = $totalPemasukan - $totalHonor - $totalBiayaOps;

    // Rincian per bulan (detail transaksi untuk collapse)
    $rincianPerBulan = [];
    for ($m = 1; $m <= 12; $m++) {
        $pemasukanDetail = Payment::with(['asesmen.tuk', 'asesmen.skema'])
            ->where('status', 'verified')
            ->whereYear('verified_at', $tahun)
            ->whereMonth('verified_at', $m)
            ->get()
            ->map(fn($p) => [
                'tipe'       => 'pemasukan',
                'tanggal'    => $p->verified_at?->format('d/m/Y'),
                'keterangan' => ($p->asesmen->full_name ?? '-') . ' — ' . ($p->asesmen->skema->name ?? '-'),
                'sub'        => $p->asesmen->tuk->name ?? '-',
                'jumlah'     => (int)$p->amount,
            ]);

        $invoiceDetail = \App\Models\Invoice::whereIn('status', ['sent', 'paid'])
            ->whereYear('issued_at', $tahun)
            ->whereMonth('issued_at', $m)
            ->get()
            ->map(fn($inv) => [
                'tipe'       => 'pemasukan',
                'tanggal'    => $inv->issued_at->format('d/m/Y'),
                'keterangan' => 'Invoice Kolektif — ' . $inv->recipient_name,
                'sub'        => $inv->invoice_number,
                'jumlah'     => (int)$inv->total_amount,
            ]);

        $honorDetail = DB::table('honor_payments')
            ->join('asesors', 'asesors.id', '=', 'honor_payments.asesor_id')
            ->select('honor_payments.dibayar_at', 'honor_payments.nomor_kwitansi',
                     'honor_payments.total', 'asesors.nama')
            ->whereIn('honor_payments.status', ['sudah_dibayar', 'dikonfirmasi'])
            ->whereYear('honor_payments.dibayar_at', $tahun)
            ->whereMonth('honor_payments.dibayar_at', $m)
            ->get()
            ->map(fn($r) => [
                'tipe'       => 'honor',
                'tanggal'    => \Carbon\Carbon::parse($r->dibayar_at)->format('d/m/Y'),
                'keterangan' => 'Honor Asesor — ' . $r->nama,
                'sub'        => $r->nomor_kwitansi,
                'jumlah'     => (int)$r->total,
            ]);

        $biayaOpsDetail = BiayaOperasional::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $m)
            ->get()
            ->map(fn($b) => [
                'tipe'       => 'biaya_ops',
                'tanggal'    => $b->tanggal->format('d/m/Y'),
                'keterangan' => $b->uraian,
                'sub'        => 'Penerima: ' . $b->nama_penerima,
                'jumlah'     => (int)$b->total,
            ]);

        $rincianPerBulan[$m] = $pemasukanDetail
            ->concat($invoiceDetail)
            ->concat($honorDetail)
            ->concat($biayaOpsDetail)
            ->sortBy('tanggal')
            ->values();
    }

    // Breakdown (masih dari payments — hanya untuk info tambahan)
    $breakdownJenis = Payment::query()
        ->selectRaw('
            SUM(CASE WHEN asesmens.is_collective = 0 THEN payments.amount ELSE 0 END) as mandiri,
            SUM(CASE WHEN asesmens.is_collective = 1 THEN payments.amount ELSE 0 END) as kolektif,
            COUNT(*) as total_transaksi
        ')
        ->join('asesmens', 'asesmens.id', '=', 'payments.asesmen_id')
        ->where('payments.status', 'verified')
        ->whereYear('payments.verified_at', $tahun)
        ->first();

    $breakdownTuk = Payment::query()
        ->selectRaw('tuks.name as tuk_name, COUNT(*) as jumlah, SUM(payments.amount) as total')
        ->join('asesmens', 'asesmens.id', '=', 'payments.asesmen_id')
        ->join('tuks', 'tuks.id', '=', 'asesmens.tuk_id')
        ->where('payments.status', 'verified')
        ->whereYear('payments.verified_at', $tahun)
        ->groupBy('tuks.id', 'tuks.name')
        ->orderByDesc('total')
        ->get();

    $breakdownSkema = Payment::query()
        ->selectRaw('skemas.name as skema_name, COUNT(*) as jumlah, SUM(payments.amount) as total')
        ->join('asesmens', 'asesmens.id', '=', 'payments.asesmen_id')
        ->join('skemas', 'skemas.id', '=', 'asesmens.skema_id')
        ->where('payments.status', 'verified')
        ->whereYear('payments.verified_at', $tahun)
        ->groupBy('skemas.id', 'skemas.name')
        ->orderByDesc('total')
        ->get();

    $transaksiTerbaru = Payment::with(['asesmen.tuk', 'asesmen.skema', 'asesmen'])
        ->where('status', 'verified')
        ->whereYear('verified_at', $tahun)
        ->orderByDesc('verified_at')
        ->limit(50)
        ->get();

    $tahunList = \App\Models\JournalEntry::selectRaw('YEAR(tanggal) as tahun')
        ->distinct()->orderByDesc('tahun')->pluck('tahun');

    if ($tahunList->isEmpty()) {
        $tahunList = collect([now()->year]);
    }

    return view('bendahara.rekap-pendapatan.index', compact(
        'tahun', 'tahunList',
        'bulanLabels', 'dataPemasukan', 'dataHonor', 'dataBiayaOps', 'dataSaldo',
        'totalPemasukan', 'totalHonor', 'totalBiayaOps', 'totalSaldo',
        'breakdownJenis', 'breakdownTuk', 'breakdownSkema',
        'transaksiTerbaru', 'rincianPerBulan'
    ));
}

     public function export(Request $request)
    {
        $tahun  = (int) ($request->get('tahun', now()->year));
        $format = $request->get('format', 'pdf'); // 'pdf' atau 'excel'

        // ── Ambil data (sama persis dengan index) ─────────────────────────
        $data = $this->buildData($tahun);

        if ($format === 'excel') {
            return $this->exportExcel($tahun, $data);
        }

        return $this->exportPdf($tahun, $data);
    }

    // ── Shared data builder ───────────────────────────────────────────────
 private function buildData(int $tahun): array
{
    $akunBank  = \App\Models\ChartOfAccount::where('kode', '1-002')->first();
    $akun4001  = \App\Models\ChartOfAccount::where('kode', '4-001')->first();

    $bulanLabels = $dataPemasukan = $dataHonor = $dataBiayaOps = $dataSaldo = [];

    for ($m = 1; $m <= 12; $m++) {
        $bulanLabels[] = \Carbon\Carbon::create()->month($m)->translatedFormat('F');

        // Pemasukan = kredit akun 4-001 bulan ini
        $in = $akun4001 ? (int) \App\Models\JournalEntryLine::where('chart_of_account_id', $akun4001->id)
            ->where('kredit', '>', 0)
            ->whereHas('entry', fn($q) => $q->whereYear('tanggal', $tahun)->whereMonth('tanggal', $m))
            ->sum('kredit') : 0;

        // Honor keluar = kredit 1-002 dari jurnal HonorPayment
        $hon = $akunBank ? (int) \App\Models\JournalEntryLine::where('chart_of_account_id', $akunBank->id)
            ->where('kredit', '>', 0)
            ->whereHas('entry', fn($q) => $q
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $m)
                ->where('ref_type', \App\Models\HonorPayment::class))
            ->sum('kredit') : 0;

        // Biaya ops keluar = kredit 1-002 dari jurnal BiayaOperasional
        $ops = $akunBank ? (int) \App\Models\JournalEntryLine::where('chart_of_account_id', $akunBank->id)
            ->where('kredit', '>', 0)
            ->whereHas('entry', fn($q) => $q
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $m)
                ->where('ref_type', \App\Models\BiayaOperasional::class))
            ->sum('kredit') : 0;

        $dataPemasukan[] = $in;
        $dataHonor[]     = $hon;
        $dataBiayaOps[]  = $ops;
        $dataSaldo[]     = $in - $hon - $ops;
    }

    return compact(
        'bulanLabels', 'dataPemasukan', 'dataHonor', 'dataBiayaOps', 'dataSaldo'
    );
}

    // ── Export PDF ────────────────────────────────────────────────────────
    private function exportPdf(int $tahun, array $data)
    {
        extract($data);

        $totalPemasukan = array_sum($dataPemasukan);
        $totalHonor     = array_sum($dataHonor);
        $totalBiayaOps  = array_sum($dataBiayaOps);
        $totalSaldo     = $totalPemasukan - $totalHonor - $totalBiayaOps;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('bendahara.rekap-pendapatan.pdf', compact(
            'tahun', 'bulanLabels',
            'dataPemasukan', 'dataHonor', 'dataBiayaOps', 'dataSaldo',
            'totalPemasukan', 'totalHonor', 'totalBiayaOps', 'totalSaldo'
        ))->setPaper('A4', 'landscape');

        return $pdf->download("Rekap_Pendapatan_{$tahun}.pdf");
    }

    // ── Export Excel ──────────────────────────────────────────────────────
    private function exportExcel(int $tahun, array $data)
    {
        extract($data);

        $totalPemasukan = array_sum($dataPemasukan);
        $totalHonor     = array_sum($dataHonor);
        $totalBiayaOps  = array_sum($dataBiayaOps);
        $totalSaldo     = $totalPemasukan - $totalHonor - $totalBiayaOps;

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap ' . $tahun);

        // ── Judul ─────────────────────────────────────────────────────────
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'REKAP PENDAPATAN & KEUANGAN TAHUN ' . $tahun);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'LSP-KAP — Dicetak ' . now()->translatedFormat('d F Y'));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 10, 'color' => ['rgb' => '666666']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Header tabel ──────────────────────────────────────────────────
        $headers = ['Bulan', 'Pemasukan (Rp)', 'Honor Asesor (Rp)', 'Biaya Ops (Rp)', 'Total Keluar (Rp)', 'Saldo Bersih (Rp)'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F'];
        $headerRow = 4;

        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i] . $headerRow, $h);
        }

        $sheet->getStyle("A{$headerRow}:F{$headerRow}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);

        // ── Data per bulan ────────────────────────────────────────────────
        foreach ($bulanLabels as $i => $bln) {
            $row      = $headerRow + 1 + $i;
            $in       = $dataPemasukan[$i];
            $hon      = $dataHonor[$i];
            $ops      = $dataBiayaOps[$i];
            $sal      = $dataSaldo[$i];
            $keluar   = $hon + $ops;
            $isEmpty  = $in == 0 && $hon == 0 && $ops == 0;

            $sheet->setCellValue("A{$row}", $bln);
            $sheet->setCellValue("B{$row}", $in);
            $sheet->setCellValue("C{$row}", $hon);
            $sheet->setCellValue("D{$row}", $ops);
            $sheet->setCellValue("E{$row}", $keluar);
            $sheet->setCellValue("F{$row}", $sal);

            // Format angka
            $numFmt = '#,##0';
            foreach (['B', 'C', 'D', 'E', 'F'] as $col) {
                $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode($numFmt);
                $sheet->getStyle("{$col}{$row}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }

            // Warna baris zebra
            $bgColor = $isEmpty ? 'F5F5F5' : ($i % 2 === 0 ? 'FFFFFF' : 'F9F9F9');
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
            ]);

            // Warna saldo negatif merah
            if ($sal < 0) {
                $sheet->getStyle("F{$row}")->getFont()->getColor()->setRGB('C0392B');
                $sheet->getStyle("F{$row}")->getFont()->setBold(true);
            } elseif ($sal > 0) {
                $sheet->getStyle("F{$row}")->getFont()->getColor()->setRGB('27AE60');
                $sheet->getStyle("F{$row}")->getFont()->setBold(true);
            }
        }

        // ── Row total ─────────────────────────────────────────────────────
        $totalRow = $headerRow + 13;
        $sheet->setCellValue("A{$totalRow}", 'TOTAL');
        $sheet->setCellValue("B{$totalRow}", $totalPemasukan);
        $sheet->setCellValue("C{$totalRow}", $totalHonor);
        $sheet->setCellValue("D{$totalRow}", $totalBiayaOps);
        $sheet->setCellValue("E{$totalRow}", $totalHonor + $totalBiayaOps);
        $sheet->setCellValue("F{$totalRow}", $totalSaldo);

        $sheet->getStyle("A{$totalRow}:F{$totalRow}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);

        foreach (['B', 'C', 'D', 'E', 'F'] as $col) {
            $sheet->getStyle("{$col}{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("{$col}{$totalRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // ── Lebar kolom ───────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(16);
        foreach (['B', 'C', 'D', 'E', 'F'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(22);
        }

        // ── Output ────────────────────────────────────────────────────────
        $filename = "Rekap_Pendapatan_{$tahun}.xlsx";
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}