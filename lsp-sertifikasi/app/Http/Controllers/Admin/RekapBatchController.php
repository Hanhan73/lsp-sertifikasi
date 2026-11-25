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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Laporan Rekap Per Batch — tampilan sama dengan Rekap Asesi (4 tab, grafik, klik baris → detail),
 * tetapi yang dihitung adalah BATCH (collective_batch_id), bukan asesi.
 *
 * Definisi:
 *  - Batch            : satu collective_batch_id. Asesi mandiri (tanpa batch) tidak dihitung sebagai batch.
 *  - Batch Selesai    : semua asesi di batch sudah punya hasil K/BK (result → fallback rekomendasi BA).
 *  - Batch Masih Proses: masih ada asesi yang belum punya hasil.
 *  - Jumlah batch total = COUNT(DISTINCT) keseluruhan — satu batch dengan beberapa jadwal bisa masuk
 *    ke lebih dari satu kelompok (mis. dua bulan), jadi total bukan penjumlahan kolom.
 *  - Asal lembaga     : schedules.institution_name terbanyak → fallback isian asesi terbanyak.
 *
 * Memakai baseQuery() Rekap Asesi (exclude dummy, fallback hasil, penentuan TUK) agar konsisten.
 */
class RekapBatchController extends RekapAsesiController
{
    /** Kolom pengelompokan per tab (sama dengan Rekap Asesi) */
    private const GROUP_EXPR = [
        'skema'  => 'r.skema_id',
        'tuk'    => 'r.tuk_id',
        'bulan'  => "DATE_FORMAT(r.assessment_date, '%Y-%m')",
        'asesor' => 'r.asesor_id',
    ];

    /** Kolom teks daftar batch; kolom yang sama dengan tab disembunyikan */
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
        $rekap   = $this->buildBatchSummary($filters);

        $skemas = Skema::orderBy('name')->get(['id', 'code', 'name']);
        $tuks   = Tuk::where(fn($q) => $q->whereNull('user_id')
                ->orWhereHas('user', fn($u) => $u->where('is_dummy', false)))
            ->orderBy('name')->get(['id', 'name']);

        $tabs        = self::TABS;
        $periodeText = $this->periodeText($filters);

        return view('admin.rekap-batch.index', compact('filters', 'rekap', 'tabs', 'skemas', 'tuks', 'periodeText'));
    }

    /**
     * Modal detail: daftar batch dalam satu kelompok.
     * GET ?bagian=skema|tuk|bulan|asesor&key=<id|YYYY-MM|_null> + filter halaman.
     */
    public function detail(Request $request)
    {
        $filters = $this->filters($request);
        $v = $request->validate([
            'bagian' => ['required', 'in:' . implode(',', array_keys(self::TABS))],
            'key'    => ['required', 'string', 'max:20'],
        ]);
        $bagian = $v['bagian'];
        $key    = $v['key'];

        if ($key !== self::NULL_KEY) {
            $valid = $bagian === 'bulan' ? preg_match('/^\d{4}-\d{2}$/', $key) : ctype_digit($key);
            abort_unless($valid, 422, 'Key tidak valid.');
        }

        $batches = $this->batchRows($this->baseQuery($filters), $bagian, $key);

        return response()->json([
            'columns' => self::columnsFor($bagian),
            'batches' => $batches,
            'total'   => [
                'n_batch'  => $batches->count(),
                'selesai'  => $batches->where('selesai', true)->count(),
                'daftar'   => $batches->sum('daftar'),
                'kompeten' => $batches->sum('kompeten'),
                'bk'       => $batches->sum('bk'),
                'belum'    => $batches->sum('belum'),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters     = $this->filters($request);
        $rekap       = $this->buildBatchSummary($filters);
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

        // Lampiran: daftar batch per kelompok (1 query per tab)
        $base     = $this->baseQuery($filters);
        $lampiran = [];
        foreach (array_keys($tabs) as $tab) {
            $lampiran[$tab] = $this->batchRows($base, $tab)->groupBy('gkey');
        }

        if ($request->get('format') === 'excel') {
            return $this->exportBatchExcel($tabs, $rekap, $lampiran, $periodeText, $filterText, $fileSuffix);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.rekap-batch.pdf', compact(
            'rekap', 'tabs', 'lampiran', 'periodeText', 'filterText'
        ))->setPaper('A4', 'landscape');

        return $pdf->download("Rekap_Per_Batch_{$fileSuffix}.pdf");
    }

    // ─────────────────────────────────────────────────────────────────────
    // Data
    // ─────────────────────────────────────────────────────────────────────

    /** Status per batch: selesai = semua asesi (dalam filter) sudah punya hasil */
    private function batchStatus(Builder $base): Builder
    {
        return DB::query()->fromSub($base, 'b')
            ->selectRaw('b.collective_batch_id AS bid,
                CASE WHEN SUM(CASE WHEN b.hasil IS NULL THEN 1 ELSE 0 END) = 0 THEN 1 ELSE 0 END AS selesai')
            ->whereNotNull('b.collective_batch_id')
            ->groupBy('b.collective_batch_id');
    }

    /**
     * Ringkasan per tab: jumlah batch, selesai, masih proses, asesi, asesor.
     * Agregasi di SQL: 4 query (1 per tab) + 1 query total, tanpa N+1.
     */
    public function buildBatchSummary(array $filters): array
    {
        $base   = $this->baseQuery($filters);
        $status = $this->batchStatus($base);
        $agg    = '
            COUNT(DISTINCT r.collective_batch_id) AS n_batch,
            COUNT(DISTINCT CASE WHEN st.selesai = 1 THEN r.collective_batch_id END) AS selesai,
            COUNT(DISTINCT CASE WHEN st.selesai = 0 THEN r.collective_batch_id END) AS proses,
            COUNT(*) AS asesi,
            COUNT(DISTINCT r.asesor_id) AS asesor
        ';
        $from = fn() => DB::query()->fromSub($base, 'r')
            ->joinSub($status, 'st', 'st.bid', '=', 'r.collective_batch_id'); // hanya asesi kolektif

        $rows = [];

        $rows['skema'] = $from()
            ->leftJoin('skemas as sk', 'sk.id', '=', 'r.skema_id')
            ->selectRaw("r.skema_id AS gid, sk.code, sk.name, {$agg}")
            ->groupBy('r.skema_id', 'sk.code', 'sk.name')
            ->orderByRaw('sk.name IS NULL, sk.name')
            ->get()
            ->map(fn($r) => $this->summaryRow($r, $r->gid,
                $r->name ? trim(($r->code ? "{$r->code} — " : '') . $r->name) : 'Tanpa Skema', $r->name ?? 'zzz'));

        $rows['tuk'] = $from()
            ->leftJoin('tuks as tk', 'tk.id', '=', 'r.tuk_id')
            ->selectRaw("r.tuk_id AS gid, tk.name, {$agg}")
            ->groupBy('r.tuk_id', 'tk.name')
            ->orderByRaw('tk.name IS NULL, tk.name')
            ->get()
            ->map(fn($r) => $this->summaryRow($r, $r->gid, $r->name ?? 'Tanpa TUK', $r->name ?? 'zzz'));

        $rows['bulan'] = $from()
            ->selectRaw("DATE_FORMAT(r.assessment_date, '%Y-%m') AS ym, {$agg}")
            ->groupBy('ym')
            ->orderByRaw('ym IS NULL, ym')
            ->get()
            ->map(fn($r) => $this->summaryRow($r, $r->ym,
                $r->ym ? Carbon::createFromFormat('Y-m-d', $r->ym . '-01')->translatedFormat('F Y') : 'Belum Dijadwalkan',
                $r->ym ?? '9999-12'));

        $rows['asesor'] = $from()
            ->leftJoin('asesors as xs', 'xs.id', '=', 'r.asesor_id')
            ->selectRaw("r.asesor_id AS gid, xs.nama, {$agg}")
            ->groupBy('r.asesor_id', 'xs.nama')
            ->orderByRaw('r.asesor_id IS NULL, xs.nama')
            ->get()
            ->map(fn($r) => $this->summaryRow($r, $r->gid, $r->nama ?? 'Belum Ada Asesor', $r->nama ?? 'zzz'));

        // Total: jumlah batch & asesor = DISTINCT keseluruhan (bukan penjumlahan kolom)
        $t = DB::query()->fromSub($base, 'r')
            ->leftJoinSub($status, 'st', 'st.bid', '=', 'r.collective_batch_id')
            ->selectRaw("
                COUNT(DISTINCT r.collective_batch_id) AS n_batch,
                COUNT(DISTINCT CASE WHEN st.selesai = 1 THEN r.collective_batch_id END) AS selesai,
                COUNT(DISTINCT CASE WHEN st.selesai = 0 THEN r.collective_batch_id END) AS proses,
                SUM(CASE WHEN r.collective_batch_id IS NOT NULL THEN 1 ELSE 0 END) AS asesi,
                SUM(CASE WHEN r.collective_batch_id IS NULL THEN 1 ELSE 0 END) AS mandiri,
                COUNT(DISTINCT CASE WHEN r.collective_batch_id IS NOT NULL THEN r.asesor_id END) AS asesor
            ")->first();

        return [
            'rows'  => $rows,
            'total' => [
                'n_batch' => (int) $t->n_batch,
                'selesai' => (int) $t->selesai,
                'proses'  => (int) $t->proses,
                'asesi'   => (int) $t->asesi,
                'mandiri' => (int) $t->mandiri,
                'asesor'  => (int) $t->asesor,
            ],
        ];
    }

    private function summaryRow(object $r, int|string|null $key, string $label, string $order): array
    {
        return [
            'key'     => $key === null ? self::NULL_KEY : (string) $key,
            'kosong'  => $key === null,
            'label'   => $label,
            'order'   => $order,
            'n_batch' => (int) $r->n_batch,
            'selesai' => (int) $r->selesai,
            'proses'  => (int) $r->proses,
            'asesi'   => (int) $r->asesi,
            'asesor'  => (int) $r->asesor,
        ];
    }

    /**
     * Daftar batch per (kelompok, batch) untuk satu tab; opsional hanya satu kelompok ($key).
     * Asal lembaga dihitung per batch (2 query agregat) agar sama di semua tab.
     */
    public function batchRows(Builder $base, string $tab, ?string $key = null)
    {
        $groupExpr = self::GROUP_EXPR[$tab];
        $fmt       = fn($d) => $d ? Carbon::parse($d)->translatedFormat('d M Y') : null;
        $compact   = fn(int $n, ?string $names, string $unit, string $empty) =>
            $n > 2 ? "{$n} {$unit}" : ($names ?: $empty);

        $fromSchedule = $this->institutionCounts($base, 'TRIM(r.institution_name)');
        $fromAsesi    = $this->institutionCounts($base, 'UPPER(TRIM(r.institution))');

        $q = DB::query()->fromSub($base, 'r')
            ->joinSub($this->batchStatus($base), 'st', 'st.bid', '=', 'r.collective_batch_id')
            ->leftJoin('tuks as tk', 'tk.id', '=', 'r.tuk_id')
            ->leftJoin('skemas as sk', 'sk.id', '=', 'r.skema_id')
            ->leftJoin('asesors as xs', 'xs.id', '=', 'r.asesor_id')
            ->selectRaw("
                {$groupExpr} AS gkey,
                r.collective_batch_id AS batch_id,
                MAX(st.selesai) AS selesai,
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
            ->groupByRaw("{$groupExpr}, r.collective_batch_id")
            // batch terbaru di atas; belum dijadwalkan di bawah
            ->orderByRaw('MIN(r.assessment_date) IS NULL, MIN(r.assessment_date) DESC, r.collective_batch_id');

        if ($key !== null) {
            $key === self::NULL_KEY
                ? $q->whereRaw("{$groupExpr} IS NULL")
                : $q->whereRaw("{$groupExpr} = ?", [$key]);
        }

        return $q->get()->map(function ($r) use ($fromSchedule, $fromAsesi, $fmt, $compact) {
            $jadwal = $fromSchedule[$r->batch_id] ?? [];
            $asesi  = $fromAsesi[$r->batch_id] ?? [];
            [$lembaga, $sumber, $lain] = $jadwal
                ? [array_key_first($jadwal), 'jadwal', count($jadwal) - 1]
                : ($asesi ? [array_key_first($asesi), 'asesi', count($asesi) - 1] : ['-', null, 0]);
            $awal  = $fmt($r->tgl_awal);
            $akhir = $fmt($r->tgl_akhir);

            return [
                'gkey'         => $r->gkey === null ? self::NULL_KEY : (string) $r->gkey,
                'batch_id'     => $r->batch_id,
                'url'          => route('admin.asesi.batch.show', $r->batch_id),
                'selesai'      => (bool) $r->selesai,
                'lembaga'      => $lembaga,
                'lembaga_src'  => $sumber,
                'lembaga_lain' => $lain,
                // > 2 nama diringkas di halaman; export memakai *_full
                'tuk'          => $compact($r->n_tuk, $r->tuk, 'TUK', 'Tanpa TUK'),
                'tuk_full'     => $r->tuk ?: 'Tanpa TUK',
                'skema'        => $compact($r->n_skema, $r->skema, 'skema', '-'),
                'skema_full'   => $r->skema ?: '-',
                'asesor'       => $compact($r->n_asesor, $r->asesor_nama, 'asesor', 'Belum ada asesor'),
                'asesor_full'  => $r->asesor_nama ?: 'Belum ada asesor',
                'tanggal'      => $awal ? ($awal === $akhir ? $awal : "{$awal} – {$akhir}") : 'Belum dijadwalkan',
                'daftar'       => (int) $r->daftar,
                'kompeten'     => (int) $r->kompeten,
                'bk'           => (int) $r->bk,
                'belum'        => (int) $r->belum,
            ];
        });
    }

    /** [batch id => [nama lembaga => jumlah asesi]] urut terbanyak, kosong diabaikan. */
    private function institutionCounts(Builder $base, string $expr): array
    {
        $out = [];
        DB::query()->fromSub($base, 'r')
            ->selectRaw("r.collective_batch_id AS bid, {$expr} AS nama, COUNT(*) AS n")
            ->whereNotNull('r.collective_batch_id')
            ->whereRaw("NULLIF({$expr}, '') IS NOT NULL")
            ->groupByRaw("r.collective_batch_id, {$expr}")
            ->orderByRaw('bid, n DESC, nama')
            ->get()
            ->each(function ($r) use (&$out) {
                $out[$r->bid][$r->nama] = (int) $r->n;
            });

        return $out;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Export Excel: sheet ringkasan per tab + sheet lampiran daftar batch
    // ─────────────────────────────────────────────────────────────────────

    private function exportBatchExcel(array $tabs, array $rekap, array $lampiran, string $periodeText, string $filterText, string $fileSuffix)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $t = $rekap['total'];
        $sub = 'Periode Asesmen: ' . $periodeText . ($filterText ? ' | ' . $filterText : '');

        $headStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];
        $totalStyle = [
            'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];
        $title = function (Worksheet $sheet, string $text, string $lastCol) use ($sub, $t) {
            $sheet->mergeCells("A1:{$lastCol}1");
            $sheet->setCellValue('A1', $text);
            $sheet->getStyle('A1')->applyFromArray([
                'font'      => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(24);
            $sheet->mergeCells("A2:{$lastCol}2");
            $sheet->setCellValue('A2', $sub);
            $sheet->mergeCells("A3:{$lastCol}3");
            $sheet->setCellValue('A3', 'LSP-KAP — Dicetak ' . now()->translatedFormat('d F Y')
                . " — {$t['n_batch']} batch ({$t['selesai']} selesai, {$t['proses']} masih proses), {$t['mandiri']} asesi mandiri tidak termasuk");
            $sheet->getStyle('A2:A3')->applyFromArray([
                'font'      => ['size' => 10, 'color' => ['rgb' => '666666']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        };

        // ── Sheet ringkasan per tab ──
        foreach ($tabs as $key => $tab) {
            $isAsesor = $key === 'asesor';
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($tab['title']);
            $headers = ['No', $tab['label'], 'Jumlah Batch', 'Batch Selesai', 'Batch Masih Proses', '% Selesai', 'Jumlah Asesi'];
            if (!$isAsesor) $headers[] = 'Jumlah Asesor';
            $lastCol = $isAsesor ? 'G' : 'H';
            $pct = fn(int $row) => "=IF(C{$row}>0,D{$row}/C{$row},0)";

            $title($sheet, 'REKAP PER BATCH — ' . strtoupper($tab['title']), $lastCol);
            $headerRow = 5;
            $sheet->fromArray($headers, null, "A{$headerRow}");
            $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray($headStyle);
            $sheet->getRowDimension($headerRow)->setRowHeight(30);

            $row = $headerRow;
            foreach ($rekap['rows'][$key] as $i => $r) {
                $row++;
                $vals = [$i + 1, $r['label'], $r['n_batch'], $r['selesai'], $r['proses'], $pct($row), $r['asesi']];
                if (!$isAsesor) $vals[] = $r['asesor'];
                $sheet->fromArray($vals, null, "A{$row}", true);
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $i % 2 === 0 ? 'FFFFFF' : 'F9F9F9']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
                ]);
            }
            $row++;
            $vals = ['', 'TOTAL', $t['n_batch'], $t['selesai'], $t['proses'], $pct($row), $t['asesi']];
            if (!$isAsesor) $vals[] = $t['asesor'];
            $sheet->fromArray($vals, null, "A{$row}", true);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($totalStyle);

            $first = $headerRow + 1;
            $sheet->getStyle("A{$first}:A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$first}:{$lastCol}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$first}:F{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->setCellValue('A' . ($row + 2), '* Total jumlah batch & asesor = jumlah berbeda secara keseluruhan (bukan penjumlahan kolom); '
                . 'satu batch dengan beberapa jadwal dapat masuk ke lebih dari satu kelompok.');
            $sheet->setCellValue('A' . ($row + 3), 'Batch Selesai = semua asesi di batch sudah punya hasil K/BK. Detail batch ada di sheet "Detail ' . $tab['title'] . '".');
            $sheet->getStyle('A' . ($row + 2) . ':A' . ($row + 3))->getFont()->setItalic(true)->setSize(9);
            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(48);
            foreach (['C', 'D', 'E', 'F', 'G', 'H'] as $c) $sheet->getColumnDimension($c)->setWidth(15);
        }

        // ── Sheet lampiran daftar batch per tab ──
        foreach ($tabs as $key => $tab) {
            $sheet    = $spreadsheet->createSheet();
            $sheet->setTitle('Detail ' . $tab['title']);
            $textCols = self::columnsFor($key);
            $col      = fn(int $i) => \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $nText    = count($textCols) + 1;
            $cDaftar  = $col($nText + 1);
            $cK       = $col($nText + 2);
            $cPct     = $col($nText + 5);
            $lastCol  = $col($nText + 6); // kolom Status
            $pct      = fn(int $row) => "=IF({$cDaftar}{$row}>0,{$cK}{$row}/{$cDaftar}{$row},0)";

            $title($sheet, 'DAFTAR BATCH — ' . strtoupper($tab['title']), $lastCol);
            $headerRow = 5;
            $sheet->fromArray(array_merge(['No'], array_values($textCols),
                ['Asesi', 'Kompeten', 'Belum Kompeten', 'Belum Ada Hasil', '% Kompeten', 'Status']), null, "A{$headerRow}");
            $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray($headStyle);
            $sheet->getRowDimension($headerRow)->setRowHeight(30);

            $row = $headerRow;
            foreach ($rekap['rows'][$key] as $g) {
                $row++;
                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->setCellValue("A{$row}", "{$g['label']} — {$g['n_batch']} batch ({$g['selesai']} selesai, {$g['proses']} masih proses)");
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCE6F1']],
                ]);
                foreach (($lampiran[$key][$g['key']] ?? collect())->values() as $i => $b) {
                    $row++;
                    $text = [];
                    foreach (array_keys($textCols) as $c) {
                        $text[] = match ($c) {
                            'batch'   => $b['batch_id'],
                            'lembaga' => $b['lembaga'] . ($b['lembaga_src'] === 'asesi' ? ' *' : ''),
                            'tuk'     => $b['tuk_full'],
                            'skema'   => $b['skema_full'],
                            'asesor'  => $b['asesor_full'],
                            'tanggal' => $b['tanggal'],
                        };
                    }
                    $sheet->fromArray(array_merge([$i + 1], $text, [
                        $b['daftar'], $b['kompeten'], $b['bk'], $b['belum'], $pct($row),
                        $b['selesai'] ? 'Selesai' : 'Masih proses',
                    ]), null, "A{$row}", true);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                    ]);
                }
            }

            $first = $headerRow + 1;
            $sheet->getStyle("{$cDaftar}{$first}:{$lastCol}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("{$cPct}{$first}:{$cPct}{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->setCellValue('A' . ($row + 2), '* Asal lembaga diambil dari isian asesi karena nama lembaga di jadwal belum diisi. '
                . 'Batch yang punya beberapa jadwal dapat muncul di lebih dari satu kelompok dengan jumlah bagiannya saja.');
            $sheet->getStyle('A' . ($row + 2))->getFont()->setItalic(true)->setSize(9);

            $widths = ['batch' => 28, 'lembaga' => 34, 'tuk' => 32, 'skema' => 32, 'asesor' => 28, 'tanggal' => 24];
            $sheet->getColumnDimension('A')->setWidth(6);
            $i = 2;
            foreach (array_keys($textCols) as $c) {
                $sheet->getColumnDimension($col($i++))->setWidth($widths[$c]);
            }
            for ($j = $nText + 1; $j <= $nText + 6; $j++) {
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
