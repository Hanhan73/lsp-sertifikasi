<?php

namespace App\Http\Controllers\ManajerSertifikasi;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExportHasilTeoriController extends Controller
{
    public function index()
    {
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

        return view('manajer-sertifikasi.export-hasil-teori.index', compact('batches', 'jadwalMandiri'));
    }

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
    // PRIVATE
    // =========================================================================

    private function streamXlsx($asesmens, string $judul, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $ws          = $spreadsheet->getActiveSheet();
        $ws->setTitle('Hasil Ujian Teori');

        $NAVY       = '1E3A5F';
        $BLUE       = '2563EB';
        $LIGHT_BLUE = 'DBEAFE';
        $WHITE      = 'FFFFFF';
        $GRAY       = 'F8FAFC';
        $FONT       = 'Calibri';

        // ── ROW 1: JUDUL
        $ws->mergeCells('A1:F1');
        $ws->setCellValue('A1', $judul);
        $ws->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $NAVY], 'name' => $FONT],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws->getRowDimension(1)->setRowHeight(34);

        // ── ROW 2: SUBTITLE
        $ws->mergeCells('A2:F2');
        $ws->setCellValue('A2', 'Dicetak: ' . now()->translatedFormat('d F Y, H:i'));
        $ws->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '64748B'], 'name' => $FONT],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $ws->getRowDimension(2)->setRowHeight(16);

        // ── ROW 3: spacer
        $ws->getRowDimension(3)->setRowHeight(6);

        // ── ROW 1 & 2 merge fix — sesuaikan ke F
        // ── ROW 4: HEADER (6 kolom: No | Nama | Lembaga | Tanggal | Jawaban | Skor)
        $ws->setCellValue('A4', 'No');
        $ws->setCellValue('B4', 'Nama Peserta');
        $ws->setCellValue('C4', 'Asal Lembaga / Institusi');
        $ws->setCellValue('D4', 'Tanggal Pelaksanaan');
        $ws->setCellValue('E4', 'Jawaban Benar / Soal');
        $ws->setCellValue('F4', 'Skor');

        $ws->getStyle('A4:F4')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => $WHITE], 'size' => 10, 'name' => $FONT],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $BLUE]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $LIGHT_BLUE]]],
        ]);
        $ws->getRowDimension(4)->setRowHeight(26);

        // ── DATA ROWS
        $r = 5;
        foreach ($asesmens as $i => $a) {
            $soalAsesi = $a->soalTeoriAsesi;
            $total     = $soalAsesi->count();
            $submitted = $soalAsesi->whereNotNull('submitted_at')->count() > 0;

            $benar = 0;
            if ($submitted) {
                foreach ($soalAsesi as $sa) {
                    if ($sa->jawaban !== null && $sa->soalTeori && $sa->jawaban === $sa->soalTeori->jawaban_benar) {
                        $benar++;
                    }
                }
            }

            // Skor: benar/total (bukan persen), misal "24 / 30"
            $skor = $submitted && $total > 0 ? round($benar / $total * 100) : '-';

            $ws->setCellValue("A{$r}", $i + 1);
            $ws->setCellValue("B{$r}", $a->full_name);
            $ws->setCellValue("C{$r}", $a->institution ?? '-');
            $ws->setCellValue("D{$r}", $a->schedule?->assessment_date?->translatedFormat('d F Y') ?? '-');
            $ws->setCellValue("E{$r}", $submitted ? "{$benar} / {$total}" : '-');
            $ws->setCellValue("F{$r}", $skor);

            $bg = $i % 2 === 0 ? $WHITE : $GRAY;
            $ws->getStyle("A{$r}:F{$r}")->applyFromArray([
                'font'      => ['name' => $FONT, 'size' => 10],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            $ws->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws->getStyle("D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $ws->getRowDimension($r)->setRowHeight(20);
            $r++;
        }

        // ── SUMMARY ROW
        $totalAsesi  = $asesmens->count();
        $sudahSubmit = $asesmens->filter(
            fn($a) => $a->soalTeoriAsesi->whereNotNull('submitted_at')->count() > 0
        )->count();

        $ws->mergeCells("A{$r}:F{$r}");
        $ws->setCellValue("A{$r}", "Total: {$totalAsesi} peserta  |  Sudah Submit: {$sudahSubmit}  |  Belum Submit: " . ($totalAsesi - $sudahSubmit));
        $ws->getStyle("A{$r}:F{$r}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => $NAVY], 'name' => $FONT],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $LIGHT_BLUE]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws->getRowDimension($r)->setRowHeight(20);

        // ── COLUMN WIDTHS
        $ws->getColumnDimension('A')->setWidth(5);
        $ws->getColumnDimension('B')->setWidth(32);
        $ws->getColumnDimension('C')->setWidth(30);
        $ws->getColumnDimension('D')->setWidth(24);
        $ws->getColumnDimension('E')->setWidth(22);
        $ws->getColumnDimension('F')->setWidth(14);

        // ── FREEZE + AUTOFILTER
        $ws->freezePane('A5');
        $ws->setAutoFilter('A4:F4');

        // ── STREAM
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}.xlsx\"");
        header('Cache-Control: max-age=0');

        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
}