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
        $tahun = (int) ($request->get('tahun', now()->year));
        $data  = $this->buildData($tahun);
        extract($data);

            $totalPemasukan = array_sum($dataPemasukan);
    $totalHonor     = array_sum($dataHonor);
    $totalBiayaOps  = array_sum($dataBiayaOps);
    $totalSaldo     = $totalPemasukan - $totalHonor - $totalBiayaOps;

        // ── 1. Pemasukan per bulan (dari payments verified) ───────────────
        $pemasukanPerBulan = Payment::query()
            ->selectRaw('MONTH(verified_at) as bulan, SUM(amount) as total')
            ->where('status', 'verified')
            ->whereYear('verified_at', $tahun)
            ->groupByRaw('MONTH(verified_at)')
            ->pluck('total', 'bulan')
            ->toArray();

        // ── 2. Honor asesor per bulan ─────────────────────────────────────
        $honorPerBulan = DB::table('honor_payments')
            ->selectRaw('MONTH(dibayar_at) as bulan, SUM(total) as total')
            ->whereYear('dibayar_at', $tahun)
            ->whereNotNull('dibayar_at')
            ->whereIn('status', ['sudah_dibayar', 'dikonfirmasi'])
            ->groupByRaw('MONTH(dibayar_at)')
            ->pluck('total', 'bulan')
            ->toArray();

        // ── 3. Biaya operasional per bulan ────────────────────────────────
        $biayaOpsPerBulan = BiayaOperasional::query()
            ->selectRaw('MONTH(tanggal) as bulan, SUM(total) as total')
            ->whereYear('tanggal', $tahun)
            ->groupByRaw('MONTH(tanggal)')
            ->pluck('total', 'bulan')
            ->toArray();

            // ── 4. Rincian per bulan ──────────────────────────────────────────
            $rincianPerBulan = [];

            for ($m = 1; $m <= 12; $m++) {
                // Pemasukan: payments verified bulan ini
                $pemasukanDetail = Payment::with(['asesmen.tuk', 'asesmen.skema'])
                    ->where('status', 'verified')
                    ->whereYear('verified_at', $tahun)
                    ->whereMonth('verified_at', $m)
                    ->get()
                    ->map(fn($p) => [
                        'tipe'        => 'pemasukan',
                        'tanggal'     => $p->verified_at?->format('d/m/Y'),
                        'keterangan'  => ($p->asesmen->full_name ?? '-') . ' — ' . ($p->asesmen->skema->name ?? '-'),
                        'sub'         => $p->asesmen->tuk->name ?? '-',
                        'jumlah'      => (int) $p->amount,
                    ]);

                // Honor: dibayar bulan ini
                $honorDetail = DB::table('honor_payments')
                    ->join('asesors', 'asesors.id', '=', 'honor_payments.asesor_id')
                    ->select('honor_payments.dibayar_at', 'honor_payments.nomor_kwitansi',
                            'honor_payments.total', 'asesors.nama')
                    ->whereIn('honor_payments.status', ['sudah_dibayar', 'dikonfirmasi'])
                    ->whereYear('honor_payments.dibayar_at', $tahun)
                    ->whereMonth('honor_payments.dibayar_at', $m)
                    ->get()
                    ->map(fn($r) => [
                        'tipe'        => 'honor',
                        'tanggal'     => \Carbon\Carbon::parse($r->dibayar_at)->format('d/m/Y'),
                        'keterangan'  => 'Honor Asesor — ' . $r->nama,
                        'sub'         => $r->nomor_kwitansi,
                        'jumlah'      => (int) $r->total,
                    ]);

                // Biaya operasional bulan ini
                $biayaOpsDetail = BiayaOperasional::whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $m)
                    ->get()
                    ->map(fn($b) => [
                        'tipe'        => 'biaya_ops',
                        'tanggal'     => $b->tanggal->format('d/m/Y'),
                        'keterangan'  => $b->uraian,
                        'sub'         => 'Penerima: ' . $b->nama_penerima,
                        'jumlah'      => (int) $b->total,
                    ]);

                $rincianPerBulan[$m] = $pemasukanDetail
                    ->concat($honorDetail)
                    ->concat($biayaOpsDetail)
                    ->sortBy('tanggal')
                    ->values();
            }
                    // ── 4. Bangun array 12 bulan ──────────────────────────────────────
        $bulanLabels   = [];
        $dataPemasukan = [];
        $dataHonor     = [];
        $dataBiayaOps  = [];
        $dataSaldo     = [];

        for ($m = 1; $m <= 12; $m++) {
            $bulanLabels[]   = \Carbon\Carbon::create()->month($m)->translatedFormat('M');
            $pemasukan       = $pemasukanPerBulan[$m] ?? 0;
            $honor           = $honorPerBulan[$m] ?? 0;
            $biayaOps        = $biayaOpsPerBulan[$m] ?? 0;
            $dataPemasukan[] = (int) $pemasukan;
            $dataHonor[]     = (int) $honor;
            $dataBiayaOps[]  = (int) $biayaOps;
            $dataSaldo[]     = (int) ($pemasukan - $honor - $biayaOps);
        }

        // ── 5. Totals keseluruhan tahun ───────────────────────────────────
        $totalPemasukan = array_sum($dataPemasukan);
        $totalHonor     = array_sum($dataHonor);
        $totalBiayaOps  = array_sum($dataBiayaOps);
        $totalSaldo     = $totalPemasukan - $totalHonor - $totalBiayaOps;

        // ── 6. Breakdown pemasukan: mandiri vs kolektif ───────────────────
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

        // ── 7. Breakdown per TUK ──────────────────────────────────────────
        $breakdownTuk = Payment::query()
            ->selectRaw('tuks.name as tuk_name, COUNT(*) as jumlah, SUM(payments.amount) as total')
            ->join('asesmens', 'asesmens.id', '=', 'payments.asesmen_id')
            ->join('tuks', 'tuks.id', '=', 'asesmens.tuk_id')
            ->where('payments.status', 'verified')
            ->whereYear('payments.verified_at', $tahun)
            ->groupBy('tuks.id', 'tuks.name')
            ->orderByDesc('total')
            ->get();

        // ── 8. Breakdown per Skema ────────────────────────────────────────
        $breakdownSkema = Payment::query()
            ->selectRaw('skemas.name as skema_name, COUNT(*) as jumlah, SUM(payments.amount) as total')
            ->join('asesmens', 'asesmens.id', '=', 'payments.asesmen_id')
            ->join('skemas', 'skemas.id', '=', 'asesmens.skema_id')
            ->where('payments.status', 'verified')
            ->whereYear('payments.verified_at', $tahun)
            ->groupBy('skemas.id', 'skemas.name')
            ->orderByDesc('total')
            ->get();

        // ── 9. Tabel transaksi terbaru (50 terakhir) ──────────────────────
        $transaksiTerbaru = Payment::with(['asesmen.tuk', 'asesmen.skema', 'asesmen'])
            ->where('status', 'verified')
            ->whereYear('verified_at', $tahun)
            ->orderByDesc('verified_at')
            ->limit(50)
            ->get();

        // ── 10. Tahun tersedia (untuk dropdown) ───────────────────────────
        $tahunList = Payment::selectRaw('YEAR(verified_at) as tahun')
            ->where('status', 'verified')
            ->whereNotNull('verified_at')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

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
        $pemasukanPerBulan = Payment::query()
            ->selectRaw('MONTH(verified_at) as bulan, SUM(amount) as total')
            ->where('status', 'verified')
            ->whereYear('verified_at', $tahun)
            ->groupByRaw('MONTH(verified_at)')
            ->pluck('total', 'bulan')->toArray();

        $honorPerBulan = DB::table('honor_payments')
            ->selectRaw('MONTH(dibayar_at) as bulan, SUM(total) as total')
            ->whereYear('dibayar_at', $tahun)
            ->whereNotNull('dibayar_at')
            ->whereIn('status', ['sudah_dibayar', 'dikonfirmasi'])
            ->groupByRaw('MONTH(dibayar_at)')
            ->pluck('total', 'bulan')->toArray();

        $biayaOpsPerBulan = BiayaOperasional::query()
            ->selectRaw('MONTH(tanggal) as bulan, SUM(total) as total')
            ->whereYear('tanggal', $tahun)
            ->groupByRaw('MONTH(tanggal)')
            ->pluck('total', 'bulan')->toArray();

        $bulanLabels = $dataPemasukan = $dataHonor = $dataBiayaOps = $dataSaldo = [];

        for ($m = 1; $m <= 12; $m++) {
            $bulanLabels[]   = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
            $in              = (int)($pemasukanPerBulan[$m] ?? 0);
            $hon             = (int)($honorPerBulan[$m] ?? 0);
            $ops             = (int)($biayaOpsPerBulan[$m] ?? 0);
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