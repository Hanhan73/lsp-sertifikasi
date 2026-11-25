<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
 * Laporan Rekap Asesi — statistik jumlah daftar & hasil asesmen.
 *
 * Definisi:
 *  - Daftar          : semua asesmen (tanpa filter status), dikecualikan data dummy.
 *  - Hasil           : asesmens.result, fallback berita_acara_asesi.rekomendasi.
 *  - TUK             : COALESCE(assigned_tuk_id, tuk_id, schedules.tuk_id).
 *  - Bulan           : schedules.assessment_date (null → "Belum Dijadwalkan").
 *  - Jumlah Asesor   : COUNT(DISTINCT schedules.asesor_id).
 */
class RekapAsesiController extends Controller
{
    /** Penanda kelompok kosong (Tanpa Skema/TUK, Belum Dijadwalkan, Belum Ada Asesor) */
    protected const NULL_KEY = '_null';

    protected const TABS = [
        'skema'  => ['title' => 'Per Skema',  'label' => 'Skema'],
        'tuk'    => ['title' => 'Per TUK',    'label' => 'TUK'],
        'bulan'  => ['title' => 'Per Bulan',  'label' => 'Bulan-Tahun'],
        'asesor' => ['title' => 'Per Asesor', 'label' => 'Nama Asesor'],
    ];

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $rekap   = $this->buildRekap($filters);

        $skemas = Skema::orderBy('name')->get(['id', 'code', 'name']);
        $tuks   = Tuk::where(fn($q) => $q->whereNull('user_id')
                ->orWhereHas('user', fn($u) => $u->where('is_dummy', false)))
            ->orderBy('name')->get(['id', 'name']);

        $tabs        = self::TABS;
        $periodeText = $this->periodeText($filters);

        return view('admin.rekap-asesi.index', compact(
            'filters', 'rekap', 'tabs', 'skemas', 'tuks', 'periodeText'
        ));
    }

    public function export(Request $request)
    {
        $filters     = $this->filters($request);
        $rekap       = $this->buildRekap($filters);
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
            return $this->exportExcel($tabs, $rekap, $periodeText, $filterText, $fileSuffix);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.rekap-asesi.pdf', compact(
            'rekap', 'tabs', 'periodeText', 'filterText'
        ))->setPaper('A4', 'landscape');

        return $pdf->download("Rekap_Asesi_{$fileSuffix}.pdf");
    }

    /**
     * Detail satu baris rekap: daftar batch (collective_batch_id) di kelompok tsb.
     * Asesi tanpa batch (mandiri) digabung jadi satu baris agar total = angka baris.
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

        $q = DB::query()->fromSub($this->baseQuery($filters), 'r');

        $column = [
            'skema'  => 'r.skema_id',
            'tuk'    => 'r.tuk_id',
            'asesor' => 'r.asesor_id',
            'bulan'  => "DATE_FORMAT(r.assessment_date, '%Y-%m')",
        ][$bagian];

        $key === self::NULL_KEY
            ? $q->whereRaw("{$column} IS NULL")
            : $q->whereRaw("{$column} = ?", [$key]);

        $batches = $q
            ->leftJoin('tuks as tk', 'tk.id', '=', 'r.tuk_id')
            ->leftJoin('skemas as sk', 'sk.id', '=', 'r.skema_id')
            ->selectRaw("
                r.collective_batch_id AS batch_id,
                GROUP_CONCAT(DISTINCT tk.name ORDER BY tk.name SEPARATOR ', ') AS tuk,
                GROUP_CONCAT(DISTINCT sk.name ORDER BY sk.name SEPARATOR ', ') AS skema,
                COUNT(DISTINCT r.tuk_id)   AS n_tuk,
                COUNT(DISTINCT r.skema_id) AS n_skema,
                MIN(r.assessment_date) AS tgl_awal,
                MAX(r.assessment_date) AS tgl_akhir,
                {$this->aggregateSelect()}
            ")
            ->groupBy('r.collective_batch_id')
            // Batch terbaru di atas; belum dijadwalkan & mandiri (tanpa batch) di bawah
            ->orderByRaw('r.collective_batch_id IS NULL, MIN(r.assessment_date) IS NULL, MIN(r.assessment_date) DESC, r.collective_batch_id')
            ->get()
            ->map(function ($b) {
                $fmt     = fn($d) => $d ? Carbon::parse($d)->translatedFormat('d M Y') : null;
                $awal    = $fmt($b->tgl_awal);
                $akhir   = $fmt($b->tgl_akhir);
                return [
                    'batch_id' => $b->batch_id,
                    'label'    => $b->batch_id ?? 'Mandiri (tanpa batch)',
                    'url'      => $b->batch_id ? route('admin.asesi.batch.show', $b->batch_id) : null,
                    // > 2 nama (biasanya baris Mandiri) diringkas; nama lengkap di tooltip
                    'tuk'        => $b->n_tuk > 2 ? "{$b->n_tuk} TUK" : ($b->tuk ?: '-'),
                    'tuk_full'   => $b->tuk ?: '-',
                    'skema'      => $b->n_skema > 2 ? "{$b->n_skema} skema" : ($b->skema ?: '-'),
                    'skema_full' => $b->skema ?: '-',
                    'tanggal'  => $awal ? ($awal === $akhir ? $awal : "{$awal} – {$akhir}") : 'Belum dijadwalkan',
                    'tgl_iso'  => $b->tgl_awal ? substr($b->tgl_awal, 0, 10) : '',
                    'daftar'   => (int) $b->daftar,
                    'kompeten' => (int) $b->kompeten,
                    'bk'       => (int) $b->bk,
                    'belum'    => (int) $b->belum,
                    'asesor'   => (int) $b->asesor,
                ];
            });

        return response()->json([
            'batches' => $batches,
            'total'   => [
                'daftar'   => $batches->sum('daftar'),
                'kompeten' => $batches->sum('kompeten'),
                'bk'       => $batches->sum('bk'),
                'belum'    => $batches->sum('belum'),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Data
    // ─────────────────────────────────────────────────────────────────────

    protected function filters(Request $request): array
    {
        $v = $request->validate([
            'dari'     => ['nullable', 'date'],
            'sampai'   => ['nullable', 'date', 'after_or_equal:dari'],
            'skema_id' => ['nullable', 'integer', 'exists:skemas,id'],
            'tuk_id'   => ['nullable', 'integer', 'exists:tuks,id'],
        ]);

        return [
            'dari'     => $v['dari'] ?? null,
            'sampai'   => $v['sampai'] ?? null,
            'skema_id' => isset($v['skema_id']) ? (int) $v['skema_id'] : null,
            'tuk_id'   => isset($v['tuk_id']) ? (int) $v['tuk_id'] : null,
        ];
    }

    /**
     * Satu baris per asesi (non-dummy) dengan kolom turunan:
     * skema_id, tuk_id, asesor_id, assessment_date, hasil ('K' | 'BK' | null).
     */
    public function baseQuery(array $filters): Builder
    {
        $tukExpr = 'COALESCE(a.assigned_tuk_id, a.tuk_id, s.tuk_id)';

        // 1 baris berita acara per asesmen (yang terbaru) agar tidak dobel hitung
        $latestBa = DB::table('berita_acara_asesi')
            ->selectRaw('asesmen_id, MAX(id) AS id')
            ->groupBy('asesmen_id');

        $q = DB::table('asesmens as a')
            ->leftJoin('schedules as s', 's.id', '=', 'a.schedule_id')
            ->leftJoinSub($latestBa, 'lba', 'lba.asesmen_id', '=', 'a.id')
            ->leftJoin('berita_acara_asesi as baa', 'baa.id', '=', 'lba.id')
            ->leftJoin('users as ua', 'ua.id', '=', 'a.user_id')
            ->leftJoin('tuks as t', 't.id', '=', DB::raw($tukExpr))
            ->leftJoin('users as ut', 'ut.id', '=', 't.user_id')
            ->leftJoin('asesors as x', 'x.id', '=', 's.asesor_id')
            ->leftJoin('users as ux', 'ux.id', '=', 'x.user_id')
            // ── Exclude data dummy (asesi / TUK / asesor dummy) ──
            ->whereRaw('COALESCE(ua.is_dummy, 0) = 0')
            ->whereRaw('COALESCE(ut.is_dummy, 0) = 0')
            ->whereRaw('COALESCE(ux.is_dummy, 0) = 0')
            ->selectRaw("
                a.id,
                a.collective_batch_id,
                a.institution,
                s.institution_name,
                a.skema_id,
                {$tukExpr} AS tuk_id,
                s.asesor_id,
                s.assessment_date,
                CASE
                    WHEN a.result = 'kompeten'       THEN 'K'
                    WHEN a.result = 'belum_kompeten' THEN 'BK'
                    WHEN UPPER(TRIM(baa.rekomendasi)) = 'K'  THEN 'K'
                    WHEN UPPER(TRIM(baa.rekomendasi)) = 'BK' THEN 'BK'
                    ELSE NULL
                END AS hasil
            ");

        if ($filters['dari']) {
            $q->whereDate('s.assessment_date', '>=', $filters['dari']);
        }
        if ($filters['sampai']) {
            $q->whereDate('s.assessment_date', '<=', $filters['sampai']);
        }
        if ($filters['skema_id']) {
            $q->where('a.skema_id', $filters['skema_id']);
        }
        if ($filters['tuk_id']) {
            $q->whereRaw("{$tukExpr} = ?", [$filters['tuk_id']]);
        }

        return $q;
    }

    protected function aggregateSelect(bool $withAsesor = true): string
    {
        return "
            COUNT(*)                                         AS daftar,
            SUM(CASE WHEN r.hasil = 'K'  THEN 1 ELSE 0 END)  AS kompeten,
            SUM(CASE WHEN r.hasil = 'BK' THEN 1 ELSE 0 END)  AS bk,
            SUM(CASE WHEN r.hasil IS NULL THEN 1 ELSE 0 END) AS belum
        " . ($withAsesor ? ', COUNT(DISTINCT r.asesor_id) AS asesor' : '');
    }

    /**
     * Semua agregasi dilakukan di SQL (5 query total, tanpa N+1).
     */
    public function buildRekap(array $filters): array
    {
        $base = $this->baseQuery($filters);
        $agg  = $this->aggregateSelect();

        $skema = DB::query()->fromSub($base, 'r')
            ->leftJoin('skemas as sk', 'sk.id', '=', 'r.skema_id')
            ->selectRaw("r.skema_id AS gid, sk.code, sk.name, {$agg}")
            ->groupBy('r.skema_id', 'sk.code', 'sk.name')
            ->orderBy('sk.name')
            ->get()
            ->map(fn($r) => $this->row(
                $r,
                $r->name ? trim(($r->code ? "{$r->code} — " : '') . $r->name) : 'Tanpa Skema',
                $r->name ?? 'zzz',
                $r->gid
            ));

        $tuk = DB::query()->fromSub($base, 'r')
            ->leftJoin('tuks as tk', 'tk.id', '=', 'r.tuk_id')
            ->selectRaw("r.tuk_id AS gid, tk.name, {$agg}")
            ->groupBy('r.tuk_id', 'tk.name')
            ->orderBy('tk.name')
            ->get()
            ->map(fn($r) => $this->row($r, $r->name ?? 'Tanpa TUK', $r->name ?? 'zzz', $r->gid));

        $bulan = DB::query()->fromSub($base, 'r')
            ->selectRaw("DATE_FORMAT(r.assessment_date, '%Y-%m') AS ym, {$agg}")
            ->groupBy('ym')
            ->orderByRaw('ym IS NULL, ym')
            ->get()
            ->map(fn($r) => $this->row(
                $r,
                $r->ym ? Carbon::createFromFormat('Y-m-d', $r->ym . '-01')->translatedFormat('F Y') : 'Belum Dijadwalkan',
                $r->ym ?? '9999-12', // ISO, "Belum Dijadwalkan" selalu terakhir
                $r->ym
            ));

        $asesor = DB::query()->fromSub($base, 'r')
            ->leftJoin('asesors as xs', 'xs.id', '=', 'r.asesor_id')
            ->selectRaw('r.asesor_id AS gid, xs.nama, ' . $this->aggregateSelect(false))
            ->groupBy('r.asesor_id', 'xs.nama')
            ->orderByRaw('r.asesor_id IS NULL, xs.nama')
            ->get()
            ->map(fn($r) => $this->row($r, $r->nama ?? 'Belum Ada Asesor', $r->nama ?? 'zzz', $r->gid));

        // Total keseluruhan — jumlah asesor = DISTINCT keseluruhan, bukan jumlah kolom
        $t = DB::query()->fromSub($base, 'r')->selectRaw($agg)->first();
        $total = [
            'daftar'   => (int) $t->daftar,
            'kompeten' => (int) $t->kompeten,
            'bk'       => (int) $t->bk,
            'belum'    => (int) $t->belum,
            'asesor'   => (int) $t->asesor,
        ];

        return [
            'rows'  => compact('skema', 'tuk', 'bulan', 'asesor'),
            'total' => $total,
        ];
    }

    /**
     * $key = id kelompok (skema/tuk/asesor id, atau 'YYYY-MM'); null = baris
     * "Tanpa Skema/TUK", "Belum Dijadwalkan", "Belum Ada Asesor" (dikirim sebagai '_null').
     */
    private function row(object $r, string $label, string $order, int|string|null $key): array
    {
        return [
            'label'    => $label,
            'order'    => $order,
            'key'      => $key === null ? self::NULL_KEY : (string) $key,
            'kosong'   => $key === null,
            'daftar'   => (int) $r->daftar,
            'kompeten' => (int) $r->kompeten,
            'bk'       => (int) $r->bk,
            'belum'    => (int) $r->belum,
            'asesor'   => isset($r->asesor) ? (int) $r->asesor : null,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Label helpers
    // ─────────────────────────────────────────────────────────────────────

    protected function periodeText(array $f): string
    {
        $fmt = fn($d) => Carbon::parse($d)->translatedFormat('d F Y');

        return match (true) {
            $f['dari'] && $f['sampai'] => $fmt($f['dari']) . ' s.d. ' . $fmt($f['sampai']),
            (bool) $f['dari']          => 'Sejak ' . $fmt($f['dari']),
            (bool) $f['sampai']        => 'Sampai ' . $fmt($f['sampai']),
            default                    => 'Semua Periode',
        };
    }

    protected function filterText(array $f): string
    {
        $parts = [];
        if ($f['skema_id']) {
            $parts[] = 'Skema: ' . (Skema::find($f['skema_id'])->name ?? '-');
        }
        if ($f['tuk_id']) {
            $parts[] = 'TUK: ' . (Tuk::find($f['tuk_id'])->name ?? '-');
        }
        return implode(' | ', $parts);
    }

    protected function fileSuffix(array $f): string
    {
        if (!$f['dari'] && !$f['sampai']) {
            return 'Semua_Periode';
        }
        return ($f['dari'] ? Carbon::parse($f['dari'])->format('Ymd') : 'awal')
            . '-' . ($f['sampai'] ? Carbon::parse($f['sampai'])->format('Ymd') : 'akhir');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Export Excel (satu sheet per tab)
    // ─────────────────────────────────────────────────────────────────────

    private function exportExcel(array $tabs, array $rekap, string $periodeText, string $filterText, string $fileSuffix)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach ($tabs as $key => $tab) {
            $isAsesor = $key === 'asesor';
            $sheet    = $spreadsheet->createSheet();
            $sheet->setTitle($tab['title']);

            $headers = $isAsesor
                ? ['No', $tab['label'], 'Jumlah Asesi Ditangani', 'Kompeten', 'Belum Kompeten', 'Belum Ada Hasil', '% Kompeten']
                : ['No', $tab['label'], 'Daftar', 'Kompeten', 'Belum Kompeten', 'Belum Ada Hasil', '% Kompeten', 'Jumlah Asesor Ditugaskan'];
            $lastCol = $isAsesor ? 'G' : 'H';
            $pctFormula = fn(int $row) => "=IF(C{$row}>0,D{$row}/C{$row},0)";

            // ── Judul ──
            $sheet->mergeCells("A1:{$lastCol}1");
            $sheet->setCellValue('A1', 'REKAP ASESI — ' . strtoupper($tab['title']));
            $sheet->getStyle('A1')->applyFromArray([
                'font'      => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(24);

            $sheet->mergeCells("A2:{$lastCol}2");
            $sheet->setCellValue('A2', 'Periode Asesmen: ' . $periodeText . ($filterText ? ' | ' . $filterText : ''));
            $sheet->mergeCells("A3:{$lastCol}3");
            $sheet->setCellValue('A3', 'LSP-KAP — Dicetak ' . now()->translatedFormat('d F Y'));
            $sheet->getStyle('A2:A3')->applyFromArray([
                'font'      => ['size' => 10, 'color' => ['rgb' => '666666']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // ── Header ──
            $headerRow = 5;
            $sheet->fromArray($headers, null, "A{$headerRow}");
            $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ]);

            // ── Data ──
            $row = $headerRow;
            foreach ($rekap['rows'][$key] as $i => $r) {
                $row++;
                $values = [$i + 1, $r['label'], $r['daftar'], $r['kompeten'], $r['bk'], $r['belum'], $pctFormula($row)];
                if (!$isAsesor) {
                    $values[] = $r['asesor'];
                }
                $sheet->fromArray($values, null, "A{$row}", true);
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $i % 2 === 0 ? 'FFFFFF' : 'F9F9F9']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
                ]);
            }

            // ── Total ──
            $row++;
            $t      = $rekap['total'];
            $values = ['', 'TOTAL', $t['daftar'], $t['kompeten'], $t['bk'], $t['belum'], $pctFormula($row)];
            if (!$isAsesor) {
                $values[] = $t['asesor'];
            }
            $sheet->fromArray($values, null, "A{$row}", true);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ]);

            $sheet->getStyle("A" . ($headerRow + 1) . ":A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C" . ($headerRow + 1) . ":{$lastCol}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G" . ($headerRow + 1) . ":G{$row}")->getNumberFormat()->setFormatCode('0.0%');

            if (!$isAsesor) {
                $sheet->setCellValue('A' . ($row + 2), '* Total Jumlah Asesor Ditugaskan = jumlah asesor berbeda secara keseluruhan (bukan penjumlahan kolom).');
                $sheet->getStyle('A' . ($row + 2))->getFont()->setItalic(true)->setSize(9);
            }

            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(48);
            foreach (['C', 'D', 'E', 'F', 'G', 'H'] as $col) {
                $sheet->getColumnDimension($col)->setWidth(16);
            }
            $sheet->getRowDimension($headerRow)->setRowHeight(30);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = "Rekap_Asesi_{$fileSuffix}.xlsx";
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
