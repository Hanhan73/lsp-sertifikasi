<?php

namespace App\Http\Controllers\Admin;

use App\Models\Skema;
use App\Models\Tuk;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Laporan Rekap Per Batch — tab sama dengan Rekap Asesi (Per Skema / TUK / Bulan / Asesor),
 * tiap kelompok berisi daftar batch-nya.
 *
 *  - Subtotal kelompok  : diambil dari RekapAsesiController::buildRekap() → identik dgn Rekap Asesi.
 *  - Baris batch        : satu per (kelompok, collective_batch_id). Batch yang tersebar di beberapa
 *                         kelompok (mis. 2 jadwal beda bulan) muncul di tiap kelompok dengan
 *                         jumlah bagiannya saja → total tidak dobel.
 *  - Baris Mandiri      : asesi tanpa batch, satu baris per TUK di dalam kelompok.
 *  - Asal lembaga       : schedules.institution_name terbanyak → fallback isian asesi terbanyak.
 */
class RekapBatchController extends RekapAsesiController
{
    /** Kunci batch: id batch, atau "#M<tuk_id>" untuk asesi mandiri per TUK */
    private const BATCH_KEY = "COALESCE(r.collective_batch_id, CONCAT('#M', COALESCE(r.tuk_id, 0)))";

    /** Kolom pengelompokan per tab (sama dengan Rekap Asesi) */
    private const GROUP_EXPR = [
        'skema'  => 'r.skema_id',
        'tuk'    => 'r.tuk_id',
        'bulan'  => "DATE_FORMAT(r.assessment_date, '%Y-%m')",
        'asesor' => 'r.asesor_id',
    ];

    /** Kolom teks tabel batch; kolom yang sama dengan pengelompokan tab disembunyikan */
    private const TEXT_COLUMNS = [
        'batch'   => 'Batch',
        'lembaga' => 'Asal Lembaga',
        'tuk'     => 'TUK',
        'skema'   => 'Skema',
        'asesor'  => 'Asesor',
        'tanggal' => 'Tanggal Asesmen',
    ];

    public static function columnsFor(string $tab): array
    {
        return array_diff_key(self::TEXT_COLUMNS, [$tab => true]);
    }

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $data    = $this->buildBatchRekap($filters);

        $skemas = Skema::orderBy('name')->get(['id', 'code', 'name']);
        $tuks   = Tuk::where(fn($q) => $q->whereNull('user_id')
                ->orWhereHas('user', fn($u) => $u->where('is_dummy', false)))
            ->orderBy('name')->get(['id', 'name']);

        $tabs        = self::TABS;
        $periodeText = $this->periodeText($filters);

        return view('admin.rekap-batch.index', compact('filters', 'data', 'tabs', 'skemas', 'tuks', 'periodeText'));
    }

    public function export(Request $request)
    {
        $filters     = $this->filters($request);
        $data        = $this->buildBatchRekap($filters);
        $periodeText = $this->periodeText($filters);
        $filterText  = $this->filterText($filters);
        $fileSuffix  = $this->fileSuffix($filters);

        // Download per bagian (?bagian=skema|tuk|bulan|asesor) atau semua bagian
        $bagian = $request->validate([
            'bagian' => ['nullable', 'in:' . implode(',', array_keys(self::TABS))],
        ])['bagian'] ?? null;
        $tabs = $bagian ? [$bagian => self::TABS[$bagian]] : self::TABS;
        if ($bagian) {
            $fileSuffix = str_replace(' ', '_', self::TABS[$bagian]['title']) . "_{$fileSuffix}";
        }

        if ($request->get('format') === 'excel') {
            return $this->exportBatchExcel($tabs, $data, $periodeText, $filterText, $fileSuffix);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.rekap-batch.pdf', compact(
            'data', 'tabs', 'periodeText', 'filterText'
        ))->setPaper('A4', 'landscape');

        return $pdf->download("Rekap_Per_Batch_{$fileSuffix}.pdf");
    }

    // ─────────────────────────────────────────────────────────────────────
    // Data
    // ─────────────────────────────────────────────────────────────────────

    /**
     * ['tabs' => [tab => [kelompok + 'batches' => [...]]], 'total' => [...]]
     * Query: 5 (buildRekap) + 4 (batch per tab) + 2 (asal lembaga) + 1 (ringkasan) — tanpa N+1.
     */
    public function buildBatchRekap(array $filters): array
    {
        $rekap = $this->buildRekap($filters);   // subtotal kelompok, identik dgn Rekap Asesi
        $base  = $this->baseQuery($filters);

        $fromSchedule = $this->institutionCounts($base, 'TRIM(r.institution_name)');
        $fromAsesi    = $this->institutionCounts($base, 'UPPER(TRIM(r.institution))');

        $tabs = [];
        foreach (self::GROUP_EXPR as $tab => $expr) {
            $batches = $this->batchRows($base, $expr, $fromSchedule, $fromAsesi)->groupBy('gkey');

            $tabs[$tab] = collect($rekap['rows'][$tab])->map(function ($g) use ($batches) {
                $list = ($batches[$g['key']] ?? collect())
                    // batch terbaru di atas; belum dijadwalkan lalu mandiri di bawah
                    ->sortBy([['is_mandiri', 'asc'], ['tgl_iso', 'desc'], ['label', 'asc']])
                    ->values();

                return $g + [
                    'n_batch' => $list->where('is_mandiri', false)->count(),
                    'batches' => $list,
                ];
            })->values();
        }

        $extra = DB::query()->fromSub($base, 'r')->selectRaw('
            COUNT(DISTINCT r.collective_batch_id) AS n_batch,
            SUM(CASE WHEN r.collective_batch_id IS NULL THEN 1 ELSE 0 END) AS mandiri
        ')->first();

        return [
            'tabs'  => $tabs,
            'total' => $rekap['total'] + [
                'n_batch' => (int) $extra->n_batch,
                'mandiri' => (int) $extra->mandiri,
            ],
        ];
    }

    private function batchRows(Builder $base, string $groupExpr, array $fromSchedule, array $fromAsesi)
    {
        $bkey = self::BATCH_KEY;
        $fmt  = fn($d) => $d ? Carbon::parse($d)->translatedFormat('d M Y') : null;
        $compact = fn(int $n, ?string $names, string $unit, string $empty) =>
            $n > 2 ? "{$n} {$unit}" : ($names ?: $empty);

        return DB::query()->fromSub($base, 'r')
            ->leftJoin('tuks as tk', 'tk.id', '=', 'r.tuk_id')
            ->leftJoin('skemas as sk', 'sk.id', '=', 'r.skema_id')
            ->leftJoin('asesors as xs', 'xs.id', '=', 'r.asesor_id')
            ->selectRaw("
                {$groupExpr} AS gkey,
                {$bkey} AS bkey,
                MAX(r.collective_batch_id) AS batch_id,
                MAX(r.tuk_id) AS tuk_id,
                GROUP_CONCAT(DISTINCT tk.name ORDER BY tk.name SEPARATOR ', ') AS tuk,
                GROUP_CONCAT(DISTINCT sk.name ORDER BY sk.name SEPARATOR ', ') AS skema,
                GROUP_CONCAT(DISTINCT xs.nama ORDER BY xs.nama SEPARATOR ', ') AS asesor_nama,
                COUNT(DISTINCT r.tuk_id)    AS n_tuk,
                COUNT(DISTINCT r.skema_id)  AS n_skema,
                COUNT(DISTINCT r.asesor_id) AS n_asesor,
                MIN(r.assessment_date) AS tgl_awal,
                MAX(r.assessment_date) AS tgl_akhir,
                {$this->aggregateSelect(false)}
            ")
            ->groupByRaw("{$groupExpr}, {$bkey}")
            ->get()
            ->map(function ($r) use ($fromSchedule, $fromAsesi, $fmt, $compact) {
                $isMandiri = $r->batch_id === null;
                [$lembaga, $sumber, $lain] = $this->lembagaFor(
                    $isMandiri, $fromSchedule[$r->bkey] ?? [], $fromAsesi[$r->bkey] ?? []
                );
                $awal  = $fmt($r->tgl_awal);
                $akhir = $fmt($r->tgl_akhir);

                return [
                    'gkey'         => $r->gkey === null ? self::NULL_KEY : (string) $r->gkey,
                    'bkey'         => $r->bkey,
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
                    'tuk'          => $compact($r->n_tuk, $r->tuk, 'TUK', 'Tanpa TUK'),
                    'tuk_full'     => $r->tuk ?: 'Tanpa TUK',
                    'skema'        => $compact($r->n_skema, $r->skema, 'skema', '-'),
                    'skema_full'   => $r->skema ?: '-',
                    'asesor'       => $compact($r->n_asesor, $r->asesor_nama, 'asesor', 'Belum ada asesor'),
                    'asesor_full'  => $r->asesor_nama ?: 'Belum ada asesor',
                    'tanggal'      => $awal ? ($awal === $akhir ? $awal : "{$awal} – {$akhir}") : 'Belum dijadwalkan',
                    'tgl_iso'      => $r->tgl_awal ? substr($r->tgl_awal, 0, 10) : '0000-00-00',
                    'daftar'       => (int) $r->daftar,
                    'kompeten'     => (int) $r->kompeten,
                    'bk'           => (int) $r->bk,
                    'belum'        => (int) $r->belum,
                ];
            });
    }

    /** [nama, sumber 'jadwal'|'asesi'|null, jumlah variasi lain] */
    private function lembagaFor(bool $isMandiri, array $jadwal, array $asesi): array
    {
        if ($isMandiri) {
            // Asesi mandiri memang dari lembaga yang berbeda-beda
            $nama = count($asesi) > 1 ? 'Beragam (' . count($asesi) . ' lembaga)' : (array_key_first($asesi) ?? '-');
            return [$nama, $asesi ? 'asesi' : null, 0];
        }
        if ($jadwal) {
            return [array_key_first($jadwal), 'jadwal', count($jadwal) - 1];
        }
        if ($asesi) {
            return [array_key_first($asesi), 'asesi', count($asesi) - 1];
        }
        return ['-', null, 0];
    }

    /**
     * [batch key => [nama lembaga => jumlah asesi]] urut terbanyak, kosong diabaikan.
     * Dihitung per batch (bukan per kelompok) agar asal lembaga sama di semua tab.
     */
    private function institutionCounts(Builder $base, string $expr): array
    {
        $out = [];
        DB::query()->fromSub($base, 'r')
            ->selectRaw(self::BATCH_KEY . " AS bkey, {$expr} AS nama, COUNT(*) AS n")
            ->whereRaw("NULLIF({$expr}, '') IS NOT NULL")
            ->groupByRaw(self::BATCH_KEY . ", {$expr}")
            ->orderByRaw('bkey, n DESC, nama')
            ->get()
            ->each(function ($r) use (&$out) {
                $out[$r->bkey][$r->nama] = (int) $r->n;
            });

        return $out;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Export Excel (satu sheet per tab)
    // ─────────────────────────────────────────────────────────────────────

    private function exportBatchExcel(array $tabs, array $data, string $periodeText, string $filterText, string $fileSuffix)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $t = $data['total'];

        foreach ($tabs as $key => $tab) {
            $sheet   = $spreadsheet->createSheet();
            $sheet->setTitle($tab['title']);
            $textCols = self::columnsFor($key);
            $nText    = count($textCols) + 1; // + No
            $col      = fn(int $i) => \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $cDaftar  = $col($nText + 1);
            $cK       = $col($nText + 2);
            $cPct     = $col($nText + 5);
            $lastCol  = $cPct;
            $lastText = $col($nText);
            $pct      = fn(int $row) => "=IF({$cDaftar}{$row}>0,{$cK}{$row}/{$cDaftar}{$row},0)";

            // ── Judul ──
            $sheet->mergeCells("A1:{$lastCol}1");
            $sheet->setCellValue('A1', 'REKAP PER BATCH — ' . strtoupper($tab['title']));
            $sheet->getStyle('A1')->applyFromArray([
                'font'      => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(24);
            $sheet->mergeCells("A2:{$lastCol}2");
            $sheet->setCellValue('A2', 'Periode Asesmen: ' . $periodeText . ($filterText ? ' | ' . $filterText : ''));
            $sheet->mergeCells("A3:{$lastCol}3");
            $sheet->setCellValue('A3', "LSP-KAP — Dicetak " . now()->translatedFormat('d F Y')
                . " — {$t['n_batch']} batch, {$t['daftar']} asesi");
            $sheet->getStyle('A2:A3')->applyFromArray([
                'font'      => ['size' => 10, 'color' => ['rgb' => '666666']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // ── Header ──
            $headerRow = 5;
            $sheet->fromArray(array_merge(['No'], array_values($textCols),
                ['Daftar', 'Kompeten', 'Belum Kompeten', 'Belum Ada Hasil', '% Kompeten']), null, "A{$headerRow}");
            $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ]);
            $sheet->getRowDimension($headerRow)->setRowHeight(30);

            $row = $headerRow;
            foreach ($data['tabs'][$key] as $g) {
                // ── Baris kelompok (subtotal) ──
                $row++;
                $info = "{$g['label']} — " . ($g['n_batch'] ? "{$g['n_batch']} batch" : 'hanya asesi mandiri')
                    . ($g['asesor'] !== null ? ", {$g['asesor']} asesor" : '');
                $sheet->mergeCells("A{$row}:{$lastText}{$row}");
                $sheet->setCellValue("A{$row}", $info);
                $sheet->fromArray([$g['daftar'], $g['kompeten'], $g['bk'], $g['belum'], $pct($row)], null, "{$cDaftar}{$row}", true);
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'font'    => ['bold' => true],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCE6F1']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BBBBBB']]],
                ]);

                // ── Baris batch ──
                foreach ($g['batches'] as $i => $b) {
                    $row++;
                    $text = [];
                    foreach (array_keys($textCols) as $c) {
                        $text[] = match ($c) {
                            'batch'   => $b['label'],
                            'lembaga' => $b['lembaga'] . ($b['lembaga_src'] === 'asesi' && !$b['is_mandiri'] ? ' *' : ''),
                            'tuk'     => $b['tuk_full'],
                            'skema'   => $b['skema_full'],
                            'asesor'  => $b['asesor_full'],
                            'tanggal' => $b['tanggal'],
                        };
                    }
                    $sheet->fromArray(array_merge([$i + 1], $text,
                        [$b['daftar'], $b['kompeten'], $b['bk'], $b['belum'], $pct($row)]), null, "A{$row}", true);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                    ]);
                    if ($b['is_mandiri']) {
                        $sheet->getStyle("B{$row}")->getFont()->setItalic(true);
                    }
                }
            }

            // ── Total ──
            $row++;
            $sheet->mergeCells("A{$row}:{$lastText}{$row}");
            $sheet->setCellValue("A{$row}", "TOTAL — {$t['n_batch']} batch");
            $sheet->fromArray([$t['daftar'], $t['kompeten'], $t['bk'], $t['belum'], $pct($row)], null, "{$cDaftar}{$row}", true);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ]);

            $first = $headerRow + 1;
            // Kolom A rata kiri: berisi No sekaligus judul kelompok (sel gabungan)
            $sheet->getStyle("A{$first}:A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("{$cDaftar}{$first}:{$lastCol}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("{$cPct}{$first}:{$cPct}{$row}")->getNumberFormat()->setFormatCode('0.0%');

            $sheet->setCellValue('A' . ($row + 2), '* Asal lembaga diambil dari isian asesi karena nama lembaga di jadwal belum diisi.');
            $sheet->getStyle('A' . ($row + 2))->getFont()->setItalic(true)->setSize(9);

            $widths = ['batch' => 28, 'lembaga' => 34, 'tuk' => 32, 'skema' => 32, 'asesor' => 28, 'tanggal' => 24];
            $sheet->getColumnDimension('A')->setWidth(6);
            $i = 2;
            foreach (array_keys($textCols) as $c) {
                $sheet->getColumnDimension($col($i++))->setWidth($widths[$c]);
            }
            for ($j = $nText + 1; $j <= $nText + 5; $j++) {
                $sheet->getColumnDimension($col($j))->setWidth(12);
            }
            $sheet->freezePane('A' . ($headerRow + 1));
        }

        $spreadsheet->setActiveSheetIndex(0);

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
