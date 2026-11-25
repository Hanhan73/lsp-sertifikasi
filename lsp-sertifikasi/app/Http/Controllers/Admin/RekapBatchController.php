<?php

namespace App\Http\Controllers\Admin;

use App\Models\Skema;
use App\Models\Tuk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Laporan Rekap Per Batch — jumlah asesi per batch beserta asal lembaga & TUK-nya.
 *
 * Memakai baseQuery() yang sama dengan Rekap Asesi (exclude dummy, fallback hasil
 * dari berita acara, penentuan TUK) sehingga angkanya konsisten antar halaman.
 *
 *  - Baris batch   : satu per collective_batch_id.
 *  - Baris Mandiri : asesi tanpa batch, satu baris per TUK (total tetap = Daftar).
 *  - Asal lembaga  : schedules.institution_name (diisi admin/direktur) yang paling
 *                    sering → fallback isian asesi (asesmens.institution) terbanyak.
 */
class RekapBatchController extends RekapAsesiController
{
    /** Kunci kelompok: id batch, atau "#M<tuk_id>" untuk asesi mandiri per TUK */
    private const GROUP_KEY = "COALESCE(r.collective_batch_id, CONCAT('#M', COALESCE(r.tuk_id, 0)))";

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $data    = $this->buildBatchRekap($filters);

        $skemas = Skema::orderBy('name')->get(['id', 'code', 'name']);
        $tuks   = Tuk::where(fn($q) => $q->whereNull('user_id')
                ->orWhereHas('user', fn($u) => $u->where('is_dummy', false)))
            ->orderBy('name')->get(['id', 'name']);

        $periodeText = $this->periodeText($filters);

        return view('admin.rekap-batch.index', compact('filters', 'data', 'skemas', 'tuks', 'periodeText'));
    }

    public function export(Request $request)
    {
        $filters     = $this->filters($request);
        $data        = $this->buildBatchRekap($filters);
        $periodeText = $this->periodeText($filters);
        $filterText  = $this->filterText($filters);
        $fileSuffix  = $this->fileSuffix($filters);

        if ($request->get('format') === 'excel') {
            return $this->exportBatchExcel($data, $periodeText, $filterText, $fileSuffix);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.rekap-batch.pdf', compact(
            'data', 'periodeText', 'filterText'
        ))->setPaper('A4', 'landscape');

        return $pdf->download("Rekap_Per_Batch_{$fileSuffix}.pdf");
    }

    // ─────────────────────────────────────────────────────────────────────
    // Data
    // ─────────────────────────────────────────────────────────────────────

    public function buildBatchRekap(array $filters): array
    {
        $base = $this->baseQuery($filters);
        $key  = self::GROUP_KEY;

        // ── 1. Angka per batch (1 query) ──
        $rows = DB::query()->fromSub($base, 'r')
            ->leftJoin('tuks as tk', 'tk.id', '=', 'r.tuk_id')
            ->leftJoin('skemas as sk', 'sk.id', '=', 'r.skema_id')
            ->selectRaw("
                {$key} AS gkey,
                MAX(r.collective_batch_id) AS batch_id,
                MAX(r.tuk_id) AS tuk_id,
                GROUP_CONCAT(DISTINCT tk.name ORDER BY tk.name SEPARATOR ', ') AS tuk,
                GROUP_CONCAT(DISTINCT sk.name ORDER BY sk.name SEPARATOR ', ') AS skema,
                COUNT(DISTINCT r.skema_id) AS n_skema,
                COUNT(DISTINCT r.tuk_id)   AS n_tuk,
                MIN(r.assessment_date) AS tgl_awal,
                MAX(r.assessment_date) AS tgl_akhir,
                {$this->aggregateSelect()}
            ")
            ->groupByRaw($key)
            ->get();

        // ── 2. Asal lembaga (2 query agregat, PHP hanya memilih yang terbanyak) ──
        $fromSchedule = $this->institutionCounts($base, 'TRIM(r.institution_name)');
        $fromAsesi    = $this->institutionCounts($base, 'UPPER(TRIM(r.institution))');

        $fmt = fn($d) => $d ? Carbon::parse($d)->translatedFormat('d M Y') : null;

        $rows = $rows->map(function ($r) use ($fromSchedule, $fromAsesi, $fmt) {
            $isMandiri = $r->batch_id === null;
            $jadwal    = $fromSchedule[$r->gkey] ?? [];
            $asesi     = $fromAsesi[$r->gkey] ?? [];

            if ($isMandiri) {
                // Asesi mandiri memang dari lembaga yang berbeda-beda
                $lembaga = count($asesi) > 1 ? 'Beragam (' . count($asesi) . ' lembaga)' : (array_key_first($asesi) ?? '-');
                $sumber  = count($asesi) ? 'asesi' : null;
                $lain    = 0;
            } elseif ($jadwal) {
                $lembaga = array_key_first($jadwal);
                $sumber  = 'jadwal';
                $lain    = count($jadwal) - 1;
            } elseif ($asesi) {
                $lembaga = array_key_first($asesi);
                $sumber  = 'asesi';
                $lain    = count($asesi) - 1;
            } else {
                $lembaga = '-';
                $sumber  = null;
                $lain    = 0;
            }

            $awal  = $fmt($r->tgl_awal);
            $akhir = $fmt($r->tgl_akhir);

            return [
                'key'          => $r->gkey,
                'batch_id'     => $r->batch_id,
                'label'        => $isMandiri ? 'Mandiri (tanpa batch)' : $r->batch_id,
                'is_mandiri'   => $isMandiri,
                'url'          => $isMandiri
                    ? ($r->tuk_id ? route('admin.asesi.mandiri-per-tuk', $r->tuk_id) : null)
                    : route('admin.asesi.batch.show', $r->batch_id),
                'lembaga'      => $lembaga,
                'lembaga_src'  => $sumber,
                'lembaga_lain' => $lain,
                // > 2 nama diringkas di halaman; Excel/PDF memakai *_full
                'tuk'          => $r->n_tuk > 2 ? "{$r->n_tuk} TUK" : ($r->tuk ?: 'Tanpa TUK'),
                'tuk_full'     => $r->tuk ?: 'Tanpa TUK',
                'skema'        => $r->n_skema > 2 ? "{$r->n_skema} skema" : ($r->skema ?: '-'),
                'skema_full'   => $r->skema ?: '-',
                'tanggal'      => $awal ? ($awal === $akhir ? $awal : "{$awal} – {$akhir}") : 'Belum dijadwalkan',
                'tgl_iso'      => $r->tgl_awal ? substr($r->tgl_awal, 0, 10) : '0000-00-00',
                'daftar'       => (int) $r->daftar,
                'kompeten'     => (int) $r->kompeten,
                'bk'           => (int) $r->bk,
                'belum'        => (int) $r->belum,
                'asesor'       => (int) $r->asesor,
            ];
        })
            // Default: urut TUK, lalu batch terbaru; baris Mandiri di akhir tiap TUK
            ->sortBy([
                ['tuk_full', 'asc'],
                ['is_mandiri', 'asc'],
                ['tgl_iso', 'desc'],
                ['label', 'asc'],
            ])
            ->values();

        $t = DB::query()->fromSub($base, 'r')->selectRaw($this->aggregateSelect())->first();

        return [
            'rows'     => $rows,
            'n_batch'  => $rows->where('is_mandiri', false)->count(),
            'total'    => [
                'daftar'   => (int) $t->daftar,
                'kompeten' => (int) $t->kompeten,
                'bk'       => (int) $t->bk,
                'belum'    => (int) $t->belum,
                'asesor'   => (int) $t->asesor,
            ],
        ];
    }

    /**
     * [gkey => [nama lembaga => jumlah asesi]] urut terbanyak, kosong diabaikan.
     */
    private function institutionCounts(\Illuminate\Database\Query\Builder $base, string $expr): array
    {
        $out = [];
        DB::query()->fromSub($base, 'r')
            ->selectRaw(self::GROUP_KEY . " AS gkey, {$expr} AS nama, COUNT(*) AS n")
            ->whereRaw("NULLIF({$expr}, '') IS NOT NULL")
            ->groupByRaw(self::GROUP_KEY . ", {$expr}")
            ->orderByRaw('gkey, n DESC, nama')
            ->get()
            ->each(function ($r) use (&$out) {
                $out[$r->gkey][$r->nama] = (int) $r->n;
            });

        return $out;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Export Excel
    // ─────────────────────────────────────────────────────────────────────

    private function exportBatchExcel(array $data, string $periodeText, string $filterText, string $fileSuffix)
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Per Batch');

        $headers = ['No', 'Batch', 'Asal Lembaga', 'TUK', 'Skema', 'Tanggal Asesmen', 'Daftar',
            'Kompeten', 'Belum Kompeten', 'Belum Ada Hasil', '% Kompeten', 'Jumlah Asesor Ditugaskan'];
        $lastCol = 'L';

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'REKAP ASESI PER BATCH');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Periode Asesmen: ' . $periodeText . ($filterText ? ' | ' . $filterText : ''));
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', 'LSP-KAP — Dicetak ' . now()->translatedFormat('d F Y')
            . ' — ' . $data['n_batch'] . ' batch');
        $sheet->getStyle('A2:A3')->applyFromArray([
            'font'      => ['size' => 10, 'color' => ['rgb' => '666666']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $headerRow = 5;
        $sheet->fromArray($headers, null, "A{$headerRow}");
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(30);

        $pct = fn(int $row) => "=IF(G{$row}>0,H{$row}/G{$row},0)";
        $row = $headerRow;
        foreach ($data['rows'] as $i => $r) {
            $row++;
            $lembaga = $r['lembaga'] . ($r['lembaga_src'] === 'asesi' && !$r['is_mandiri'] ? ' *' : '');
            $sheet->fromArray([
                $i + 1, $r['label'], $lembaga, $r['tuk_full'], $r['skema_full'], $r['tanggal'],
                $r['daftar'], $r['kompeten'], $r['bk'], $r['belum'], $pct($row), $r['asesor'],
            ], null, "A{$row}", true);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $i % 2 === 0 ? 'FFFFFF' : 'F9F9F9']],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            if ($r['is_mandiri']) {
                $sheet->getStyle("B{$row}")->getFont()->setItalic(true);
            }
        }

        $row++;
        $t = $data['total'];
        $sheet->fromArray(['', 'TOTAL', '', '', '', '', $t['daftar'], $t['kompeten'], $t['bk'], $t['belum'], $pct($row), $t['asesor']],
            null, "A{$row}", true);
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);

        $first = $headerRow + 1;
        $sheet->getStyle("A{$first}:A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G{$first}:L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("K{$first}:K{$row}")->getNumberFormat()->setFormatCode('0.0%');
        $sheet->getStyle("C{$first}:E{$row}")->getAlignment()->setWrapText(true);

        $sheet->setCellValue('A' . ($row + 2), '* Asal lembaga diambil dari isian asesi karena nama lembaga di jadwal belum diisi.');
        $sheet->setCellValue('A' . ($row + 3), 'Total Jumlah Asesor = jumlah asesor berbeda secara keseluruhan (bukan penjumlahan kolom).');
        $sheet->getStyle('A' . ($row + 2) . ':A' . ($row + 3))->getFont()->setItalic(true)->setSize(9);

        foreach (['A' => 6, 'B' => 30, 'C' => 38, 'D' => 34, 'E' => 34, 'F' => 24,
                  'G' => 10, 'H' => 11, 'I' => 11, 'J' => 12, 'K' => 11, 'L' => 13] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        $sheet->freezePane('C' . ($headerRow + 1));

        $filename = "Rekap_Per_Batch_{$fileSuffix}.xlsx";
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
