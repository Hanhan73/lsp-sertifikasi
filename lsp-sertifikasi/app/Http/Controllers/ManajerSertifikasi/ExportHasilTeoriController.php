<?php

namespace App\Http\Controllers\ManajerSertifikasi;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\Schedule;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExportHasilTeoriController extends Controller
{
    // Style constants — dipakai semua export agar konsisten
    private const NAVY       = '1E3A5F';
    private const BLUE       = '2563EB';
    private const LIGHT_BLUE = 'DBEAFE';
    private const WHITE      = 'FFFFFF';
    private const GRAY       = 'F8FAFC';
    private const FONT       = 'Calibri';

    // =========================================================================
    // INDEX — tampilkan daftar batch + tab observasi & berita acara
    // =========================================================================

    public function index()
    {
        // Tab teori: batch kolektif
        $batches = Asesmen::select(
                'collective_batch_id',
                DB::raw('MIN(asesmens.id) as first_asesmen_id'),
                DB::raw('COUNT(DISTINCT asesmens.id) as total_asesi'),
                DB::raw('MAX(schedules.assessment_date) as tanggal_asesmen')
            )
            ->join('schedules', 'schedules.id', '=', 'asesmens.schedule_id')
            ->whereNotNull('collective_batch_id')
            ->whereNotNull('asesmens.schedule_id')
            ->whereHas('soalTeoriAsesi')
            ->groupBy('collective_batch_id')
            ->orderByDesc('tanggal_asesmen')
            ->get();

        $batches = $batches->map(function ($b) {
            $asesmens    = Asesmen::with(['soalTeoriAsesi', 'schedule', 'skema'])
                ->where('collective_batch_id', $b->collective_batch_id)->get();
            $sudahSubmit = 0; $belumSubmit = 0; $totalSoal = 0;
            foreach ($asesmens as $a) {
                $soal = $a->soalTeoriAsesi;
                if ($soal->isEmpty()) continue;
                $totalSoal++;
                $soal->whereNotNull('submitted_at')->count() > 0 ? $sudahSubmit++ : $belumSubmit++;
            }
            $first = $asesmens->first();
            return [
                'batch_id'      => $b->collective_batch_id,
                'total_asesi'   => $b->total_asesi,
                'skema_name'    => $first?->skema?->name ?? '-',
                'tanggal'       => $first?->schedule?->assessment_date,
                'sudah_submit'  => $sudahSubmit,
                'belum_submit'  => $belumSubmit,
                'total_soal'    => $totalSoal,
                'semua_selesai' => $belumSubmit === 0 && $totalSoal > 0,
            ];
        });

        // Tab teori: jadwal mandiri
        $jadwalMandiri = Schedule::with(['skema', 'asesmens.soalTeoriAsesi'])
            ->whereHas('asesmens.soalTeoriAsesi')
            ->whereHas('asesmens', fn($q) => $q->where('is_collective', false))
            ->orderByDesc('assessment_date')
            ->get()
            ->map(function ($s) {
                $asesmens    = $s->asesmens->where('is_collective', false);
                $sudahSubmit = 0; $belumSubmit = 0;
                foreach ($asesmens as $a) {
                    $soal = $a->soalTeoriAsesi;
                    if ($soal->isEmpty()) continue;
                    $soal->whereNotNull('submitted_at')->count() > 0 ? $sudahSubmit++ : $belumSubmit++;
                }
                return [
                    'schedule_id'   => $s->id,
                    'skema_name'    => $s->skema?->name ?? '-',
                    'tanggal'       => $s->assessment_date,
                    'total_asesi'   => $asesmens->count(),
                    'sudah_submit'  => $sudahSubmit,
                    'belum_submit'  => $belumSubmit,
                    'semua_selesai' => $belumSubmit === 0 && $sudahSubmit > 0,
                ];
            });

        // Tab observasi & berita acara: semua batch yang punya jadwal
        $batchData = Asesmen::with(['skema', 'tuk', 'schedule'])
            ->whereNotNull('collective_batch_id')
            ->whereNotNull('schedule_id')
            ->get()
            ->groupBy('collective_batch_id')
            ->map(function ($items) {
                $scheduleIds = $items->pluck('schedule_id')->unique()->filter();
                $schedules   = Schedule::with(['beritaAcara.asesis', 'hasilObservasi'])
                    ->whereIn('id', $scheduleIds)->get();

                $first           = $items->first();
                $totalJadwal     = $scheduleIds->count();

                // Observasi
                $jadwalAdaObs    = $schedules->filter(fn($s) => $s->hasilObservasi->isNotEmpty())->count();
                $pesertaObs      = $schedules->flatMap->hasilObservasi->count(); // file yg diupload
                // Hitung asesi dari jadwal yang sudah upload
                $asesiObs        = $schedules
                    ->filter(fn($s) => $s->hasilObservasi->isNotEmpty())
                    ->sum(fn($s) => $s->hasilObservasi->pluck('schedule_id')->unique()->count() > 0
                        ? Asesmen::where('schedule_id', $s->id)->count()
                        : 0
                    );

                // Berita Acara
                $jadwalAdaBA     = $schedules->filter(fn($s) => $s->beritaAcara !== null)->count();
                $pesertaBA       = $schedules
                    ->filter(fn($s) => $s->beritaAcara !== null)
                    ->sum(fn($s) => $s->beritaAcara->asesis->count());

                return [
                    'batch_id'        => $first->collective_batch_id,
                    'skema_name'      => $first->skema?->name ?? '-',
                    'tuk_name'        => $first->tuk?->name ?? '-',
                    'jumlah_peserta'  => $items->count(),
                    'total_jadwal'    => $totalJadwal,
                    // observasi
                    'ada_observasi'   => $jadwalAdaObs > 0,
                    'jadwal_obs'      => $jadwalAdaObs,      // berapa jadwal yg sudah upload
                    'peserta_obs'     => $items->whereIn(   // asesi dari jadwal yg sudah upload
                        'schedule_id',
                        $schedules->filter(fn($s) => $s->hasilObservasi->isNotEmpty())->pluck('id')
                    )->count(),
                    // berita acara
                    'ada_ba'          => $jadwalAdaBA > 0,
                    'jadwal_ba'       => $jadwalAdaBA,
                    'peserta_ba'      => $pesertaBA,
                    'tanggal'         => $items->pluck('schedule.assessment_date')->filter()->sort()->first(),
                ];
            })
            ->sortByDesc('tanggal')
            ->values();

        return view('manajer-sertifikasi.export-hasil-teori.index', compact(
            'batches', 'jadwalMandiri', 'batchData'
        ));
    }

    // =========================================================================
    // EXPORT TEORI — existing methods (tidak diubah)
    // =========================================================================

    public function exportBatch(string $batchId)
    {
        $asesmens = Asesmen::with(['soalTeoriAsesi.soalTeori', 'schedule', 'skema'])
            ->where('collective_batch_id', $batchId)
            ->whereHas('soalTeoriAsesi')
            ->get();

        abort_if($asesmens->isEmpty(), 404, 'Batch tidak ditemukan atau tidak punya data soal teori.');

        return $this->streamXlsx(
            $asesmens,
            "Hasil Ujian Teori — Batch {$batchId}",
            "Hasil_Teori_Batch_{$batchId}"
        );
    }

    public function exportJadwal(Schedule $schedule)
    {
        $asesmens = Asesmen::with(['soalTeoriAsesi.soalTeori', 'schedule', 'skema'])
            ->where('schedule_id', $schedule->id)
            ->whereHas('soalTeoriAsesi')
            ->get();

        abort_if($asesmens->isEmpty(), 404, 'Tidak ada data soal teori untuk jadwal ini.');

        $slug = str_replace([' ', '/'], ['_', '-'], $schedule->skema?->name ?? 'Asesmen');
        return $this->streamXlsx(
            $asesmens,
            "Hasil Ujian Teori — {$schedule->skema?->name} {$schedule->assessment_date->translatedFormat('d M Y')}",
            "Hasil_Teori_{$slug}_{$schedule->assessment_date->format('d-m-Y')}"
        );
    }

    // =========================================================================
    // EXPORT OBSERVASI PER BATCH — merge semua file asesor jadi satu
    // =========================================================================
 
    public function exportObservasi(string $batchId)
    {
        $asesmens = Asesmen::with(['skema', 'tuk', 'schedule'])
            ->where('collective_batch_id', $batchId)
            ->whereNotNull('schedule_id')
            ->get();
 
        abort_if($asesmens->isEmpty(), 404, 'Batch tidak ditemukan.');
 
        $scheduleIds = $asesmens->pluck('schedule_id')->unique()->filter();
        $schedules   = Schedule::with(['hasilObservasi'])
            ->whereIn('id', $scheduleIds)
            ->orderBy('assessment_date')
            ->get();
 
        $first     = $asesmens->first();
        $skemaName = $first->skema?->name ?? 'Asesmen';
 
        $files = [];
        foreach ($schedules as $schedule) {
            foreach ($schedule->hasilObservasi as $hasil) {
                if (!$hasil->file_path) continue;
                $path = Storage::disk('private')->path($hasil->file_path);
                if (file_exists($path)) {
                    $files[] = $path;
                }
            }
        }
 
        abort_if(empty($files), 404, 'Belum ada file observasi yang diupload untuk batch ini.');
 
        $targetSheets = ['Pencapaian', 'Hasil Asesmen'];
 
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
 
        foreach ($targetSheets as $targetSheetName) {
            $outputWs   = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $targetSheetName);
            $spreadsheet->addSheet($outputWs);
 
            $currentRow   = 1;
            $headersDone  = 0; // jumlah baris header yang sudah dicopy (Pencapaian punya 2 baris header)
            $globalNo     = 1;
 
            // Pencapaian  : No=col1, filter valid=col2 (Nama), sub-header UK di col3
            // Hasil Asesmen: No=col3, filter valid=col5 (UK 01)
            $isPencapaian  = mb_strtolower(trim($targetSheetName)) === 'pencapaian';
            $colNo         = $isPencapaian ? 1 : 3;
            $colFilter     = $isPencapaian ? 2 : 5;
            $totalHeaders  = $isPencapaian ? 2 : 2; // kedua sheet punya 2 baris header
            // Pencapaian: col 1-14 (No,Nama,UK01-UK11,Pencapaian). Col 15+ (skor,Rubrik) dibuang.
            // Hasil Asesmen: semua kolom.
            $colLimit      = $isPencapaian ? 14 : null;
 
            foreach ($files as $filePath) {
                try {
                    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
                    $reader->setReadDataOnly(true);
                    $sourceBook = $reader->load($filePath);
                } catch (\Throwable $e) {
                    \Log::warning("[exportObservasi] Gagal buka file: {$filePath} — " . $e->getMessage());
                    continue;
                }
 
                $sourceWs = null;
                foreach ($sourceBook->getSheetNames() as $sName) {
                    if (mb_strtolower(trim($sName)) === mb_strtolower(trim($targetSheetName))) {
                        $sourceWs = $sourceBook->getSheetByName($sName);
                        break;
                    }
                }
 
                if (!$sourceWs) {
                    \Log::info("[exportObservasi] Sheet '{$targetSheetName}' tidak ditemukan di: {$filePath}");
                    continue;
                }
 
                $highestRow    = $sourceWs->getHighestRow();
                $highestCol    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sourceWs->getHighestColumn());
                $copyUpToCol   = $colLimit !== null ? min($colLimit, $highestCol) : $highestCol;
                $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($copyUpToCol);
 
                for ($r = 1; $r <= $highestRow; $r++) {
                    $noVal     = $sourceWs->getCellByColumnAndRow($colNo, $r)->getValue();
                    $filterVal = $sourceWs->getCellByColumnAndRow($colFilter, $r)->getValue();
 
                    // --- Klasifikasi baris ---
 
                    // DUMMY: No numeric tapi kolom validasi null → skip
                    if (is_numeric($noVal) && $filterVal === null) {
                        continue;
                    }
 
                    // DATA VALID: No numeric dan kolom validasi tidak null
                    $isDataRow = is_numeric($noVal) && $filterVal !== null;
 
                    // HEADER: baris yang punya konten tapi bukan data numerik
                    // Pencapaian row1: col1='No', col2='Nama'
                    // Pencapaian row2: col1=null, col2=null, col3='UK 01' ← ini yang sebelumnya terlewat
                    // Hasil Asesmen row1: col3='No', col4='Nama'
                    // Hasil Asesmen row2: col3=null, col4=null, col5='UK 01'
                    $isHeaderRow = !$isDataRow && !($noVal === null && $filterVal === null && $sourceWs->getCellByColumnAndRow(3, $r)->getValue() === null);
 
                    if ($isHeaderRow) {
                        // Hanya copy header dari file pertama (sampai $totalHeaders baris)
                        if ($headersDone >= $totalHeaders) continue;
 
                        for ($c = 1; $c <= $copyUpToCol; $c++) {
                            $outputWs->setCellValueByColumnAndRow(
                                $c, $currentRow,
                                $sourceWs->getCellByColumnAndRow($c, $r)->getValue()
                            );
                        }
                        $outputWs->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->applyFromArray([
                            'font'      => ['bold' => true, 'color' => ['rgb' => self::WHITE], 'name' => self::FONT, 'size' => 10],
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::BLUE]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::LIGHT_BLUE]]],
                        ]);
                        $outputWs->getRowDimension($currentRow)->setRowHeight(24);
                        $currentRow++;
                        $headersDone++;
                        continue;
                    }
 
                    if (!$isDataRow) continue;
 
                    // DATA: copy sampai kolom yang diizinkan
                    for ($c = 1; $c <= $copyUpToCol; $c++) {
                        $outputWs->setCellValueByColumnAndRow(
                            $c, $currentRow,
                            $sourceWs->getCellByColumnAndRow($c, $r)->getValue()
                        );
                    }
 
                    // Override No dengan nomor urut global lintas file
                    $outputWs->setCellValueByColumnAndRow($colNo, $currentRow, $globalNo);
 
                    $bg = ($globalNo % 2 === 0) ? self::GRAY : self::WHITE;
                    $outputWs->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")->applyFromArray([
                        'font'      => ['name' => self::FONT, 'size' => 10],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $outputWs->getRowDimension($currentRow)->setRowHeight(18);
 
                    $currentRow++;
                    $globalNo++;
                }
            }
 
            // Auto-width
            if ($currentRow > 1) {
                $highestColOut = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
                    $outputWs->getHighestColumn()
                );
                for ($c = 1; $c <= $highestColOut; $c++) {
                    $outputWs->getColumnDimensionByColumn($c)->setAutoSize(true);
                }
                $outputWs->freezePane('A3'); // freeze setelah 2 baris header
            }
        }
 
        $safe    = str_replace(['/', '\\', ' '], '-', $batchId);
        $tmpPath = sys_get_temp_dir() . "/Rekap_Observasi_{$safe}_" . time() . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);
 
        return response()->download($tmpPath, "Rekap_Observasi_{$safe}_" . date('Ymd') . '.xlsx')
            ->deleteFileAfterSend();
    }
 

    // =========================================================================
    // EXPORT BERITA ACARA PER BATCH
    // =========================================================================

    public function exportBeritaAcara(string $batchId)
    {
        $asesmens = Asesmen::with(['skema', 'tuk', 'schedule'])
            ->where('collective_batch_id', $batchId)
            ->whereNotNull('schedule_id')
            ->get();

        abort_if($asesmens->isEmpty(), 404, 'Batch tidak ditemukan.');

        $scheduleIds = $asesmens->pluck('schedule_id')->unique()->filter();
        $schedules   = Schedule::with(['asesor', 'asesmens', 'beritaAcara.asesis.asesmen'])
            ->whereIn('id', $scheduleIds)->get();

        $first     = $asesmens->first();
        $skemaName = $first->skema?->name ?? 'Asesmen';
        $tukName   = $first->tuk?->name ?? '-';

        $spreadsheet = new Spreadsheet();
        $ws          = $spreadsheet->getActiveSheet();
        $ws->setTitle('Berita Acara');

        // Judul
        $ws->mergeCells('A1:G1');
        $ws->setCellValue('A1', 'REKAP BERITA ACARA — ' . strtoupper($skemaName));
        $ws->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => self::NAVY], 'name' => self::FONT],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws->getRowDimension(1)->setRowHeight(34);

        // Subtitle
        $ws->mergeCells('A2:G2');
        $ws->setCellValue('A2', "TUK: {$tukName}  |  Batch: {$batchId}  |  Dicetak: " . now()->translatedFormat('d F Y, H:i'));
        $ws->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '64748B'], 'name' => self::FONT],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $ws->getRowDimension(2)->setRowHeight(16);
        $ws->getRowDimension(3)->setRowHeight(6);

        // Header
        $headers = ['No', 'Nama Asesi', 'Asal Lembaga', 'Tanggal Pelaksanaan', 'Asesor', 'Rekomendasi', 'Catatan'];
        foreach ($headers as $ci => $h) {
            $ws->setCellValueByColumnAndRow($ci + 1, 4, $h);
        }
        $ws->getStyle('A4:G4')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => self::WHITE], 'size' => 10, 'name' => self::FONT],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::BLUE]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::LIGHT_BLUE]]],
        ]);
        $ws->getRowDimension(4)->setRowHeight(26);

        // Data
        $row    = 5;
        $no     = 1;
        $totalK = 0; $totalBK = 0;

        foreach ($schedules->sortBy('assessment_date') as $schedule) {
            $ba = $schedule->beritaAcara;
            if (!$ba) continue;

            $rekMap  = $ba->asesis->pluck('rekomendasi', 'asesmen_id');
            $catatan = $ba->catatan ?? '';

            foreach ($schedule->asesmens as $asesmen) {
                $rek = $rekMap[$asesmen->id] ?? '-';
                if ($rek === 'K')  $totalK++;
                if ($rek === 'BK') $totalBK++;

                $ws->setCellValueByColumnAndRow(1, $row, $no++);
                $ws->setCellValueByColumnAndRow(2, $row, $asesmen->full_name ?? '-');
                $ws->setCellValueByColumnAndRow(3, $row, $asesmen->institution ?? '-');
                $ws->setCellValueByColumnAndRow(4, $row, $schedule->assessment_date->format('d/m/Y'));
                $ws->setCellValueByColumnAndRow(5, $row, $schedule->asesor?->nama ?? '-');
                $ws->setCellValueByColumnAndRow(6, $row, $rek);
                $ws->setCellValueByColumnAndRow(7, $row, $catatan);

                $bg = $no % 2 === 0 ? self::GRAY : self::WHITE;
                $ws->getStyle("A{$row}:G{$row}")->applyFromArray([
                    'font'    => ['name' => self::FONT, 'size' => 10],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Warna kolom rekomendasi
                if ($rek === 'K') {
                    $ws->getStyle("F{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '065F46']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                } elseif ($rek === 'BK') {
                    $ws->getStyle("F{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '991B1B']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }

                $ws->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $ws->getRowDimension($row)->setRowHeight(20);
                $row++;
            }
        }

        // Summary row
        $ws->mergeCells("A{$row}:E{$row}");
        $ws->setCellValue("A{$row}", "Total: " . ($no - 1) . " peserta");
        $ws->setCellValue("F{$row}", "K: {$totalK}  |  BK: {$totalBK}");
        $ws->mergeCells("F{$row}:G{$row}");
        $ws->getStyle("A{$row}:G{$row}")->applyFromArray([
            'font'    => ['bold' => true, 'size' => 9, 'color' => ['rgb' => self::NAVY], 'name' => self::FONT],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::LIGHT_BLUE]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws->getRowDimension($row)->setRowHeight(20);

        // Column widths
        $ws->getColumnDimension('A')->setWidth(5);
        $ws->getColumnDimension('B')->setWidth(32);
        $ws->getColumnDimension('C')->setWidth(28);
        $ws->getColumnDimension('D')->setWidth(20);
        $ws->getColumnDimension('E')->setWidth(28);
        $ws->getColumnDimension('F')->setWidth(16);
        $ws->getColumnDimension('G')->setWidth(32);

        $ws->freezePane('A5');
        $ws->setAutoFilter('A4:G4');

        $safe    = str_replace(['/', '\\', ' '], '-', $batchId);
        $tmpPath = sys_get_temp_dir() . "/Berita_Acara_{$safe}_" . time() . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        return response()->download($tmpPath, "Berita_Acara_{$safe}_" . date('Ymd') . '.xlsx')
            ->deleteFileAfterSend();
    }

    // =========================================================================
    // PRIVATE
    // =========================================================================

    private function streamXlsx($asesmens, string $judul, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $ws          = $spreadsheet->getActiveSheet();
        $ws->setTitle('Hasil Ujian Teori');

        $ws->mergeCells('A1:F1');
        $ws->setCellValue('A1', $judul);
        $ws->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => self::NAVY], 'name' => self::FONT],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws->getRowDimension(1)->setRowHeight(34);

        $ws->mergeCells('A2:F2');
        $ws->setCellValue('A2', 'Dicetak: ' . now()->translatedFormat('d F Y, H:i'));
        $ws->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '64748B'], 'name' => self::FONT],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $ws->getRowDimension(2)->setRowHeight(16);
        $ws->getRowDimension(3)->setRowHeight(6);

        foreach (['A4' => 'No', 'B4' => 'Nama Peserta', 'C4' => 'Asal Lembaga / Institusi',
                  'D4' => 'Tanggal Pelaksanaan', 'E4' => 'Jawaban Benar / Soal', 'F4' => 'Skor'] as $cell => $label) {
            $ws->setCellValue($cell, $label);
        }
        $ws->getStyle('A4:F4')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => self::WHITE], 'size' => 10, 'name' => self::FONT],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::BLUE]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::LIGHT_BLUE]]],
        ]);
        $ws->getRowDimension(4)->setRowHeight(26);

        $r = 5;
        foreach ($asesmens as $i => $a) {
            $soalAsesi = $a->soalTeoriAsesi;
            $total     = $soalAsesi->count();
            $submitted = $soalAsesi->whereNotNull('submitted_at')->count() > 0;
            $benar     = 0;
            if ($submitted) {
                foreach ($soalAsesi as $sa) {
                    if ($sa->jawaban !== null && $sa->soalTeori && $sa->jawaban === $sa->soalTeori->jawaban_benar) {
                        $benar++;
                    }
                }
            }
            $skor = $submitted && $total > 0 ? round($benar / $total * 100) : '-';

            $ws->setCellValue("A{$r}", $i + 1);
            $ws->setCellValue("B{$r}", $a->full_name);
            $ws->setCellValue("C{$r}", $a->institution ?? '-');
            $ws->setCellValue("D{$r}", $a->schedule?->assessment_date?->translatedFormat('d F Y') ?? '-');
            $ws->setCellValue("E{$r}", $submitted ? "{$benar} / {$total}" : '-');
            $ws->setCellValue("F{$r}", $skor);

            $bg = $i % 2 === 0 ? self::WHITE : self::GRAY;
            $ws->getStyle("A{$r}:F{$r}")->applyFromArray([
                'font'      => ['name' => self::FONT, 'size' => 10],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            foreach (['A', 'D', 'E', 'F'] as $col) {
                $ws->getStyle("{$col}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            $ws->getRowDimension($r)->setRowHeight(20);
            $r++;
        }

        $totalAsesi  = $asesmens->count();
        $sudahSubmit = $asesmens->filter(
            fn($a) => $a->soalTeoriAsesi->whereNotNull('submitted_at')->count() > 0
        )->count();

        $ws->mergeCells("A{$r}:F{$r}");
        $ws->setCellValue("A{$r}", "Total: {$totalAsesi} peserta  |  Sudah Submit: {$sudahSubmit}  |  Belum Submit: " . ($totalAsesi - $sudahSubmit));
        $ws->getStyle("A{$r}:F{$r}")->applyFromArray([
            'font'    => ['bold' => true, 'size' => 9, 'color' => ['rgb' => self::NAVY], 'name' => self::FONT],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::LIGHT_BLUE]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws->getRowDimension($r)->setRowHeight(20);

        $ws->getColumnDimension('A')->setWidth(5);
        $ws->getColumnDimension('B')->setWidth(32);
        $ws->getColumnDimension('C')->setWidth(30);
        $ws->getColumnDimension('D')->setWidth(24);
        $ws->getColumnDimension('E')->setWidth(22);
        $ws->getColumnDimension('F')->setWidth(14);

        $ws->freezePane('A5');
        $ws->setAutoFilter('A4:F4');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}.xlsx\"");
        header('Cache-Control: max-age=0');

        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    private function safeSheetName(string $name): string
    {
        $name = str_replace(['/', '\\', '?', '*', '[', ']', ':'], ' ', $name);
        return mb_substr(trim($name), 0, 31);
    }
}