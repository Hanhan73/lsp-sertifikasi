<?php

namespace App\Http\Controllers\ManajerSertifikasi;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\Schedule;
use App\Models\SoalTeoriAsesi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportHasilTeoriController extends Controller
{
    /**
     * Halaman index export — list semua batch yang punya data soal teori.
     */
    public function index()
    {
        // Ambil semua batch kolektif yang punya asesi dengan soal teori
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

        // Enrich setiap batch dengan info tambahan
        $batches = $batches->map(function ($b) {
            $asesmens = Asesmen::with([
                'soalTeoriAsesi.soalTeori',
                'schedule',
                'skema',
            ])
            ->where('collective_batch_id', $b->collective_batch_id)
            ->get();

            $totalSoal     = 0;
            $sudahSubmit   = 0;
            $belumSubmit   = 0;

            foreach ($asesmens as $a) {
                $soal = $a->soalTeoriAsesi;
                if ($soal->isEmpty()) continue;
                $totalSoal++;
                if ($soal->whereNotNull('submitted_at')->count() > 0) {
                    $sudahSubmit++;
                } else {
                    $belumSubmit++;
                }
            }

            $first = $asesmens->first();
            return [
                'batch_id'       => $b->collective_batch_id,
                'total_asesi'    => $b->total_asesi,
                'skema_name'     => $first?->skema?->name ?? '-',
                'tanggal'        => $first?->schedule?->assessment_date,
                'sudah_submit'   => $sudahSubmit,
                'belum_submit'   => $belumSubmit,
                'total_soal'     => $totalSoal,
                'semua_selesai'  => $belumSubmit === 0 && $totalSoal > 0,
            ];
        });

        // Juga ambil jadwal non-kolektif yang punya soal teori (mandiri)
        $jadwalMandiri = Schedule::with(['skema', 'asesmens.soalTeoriAsesi'])
            ->whereHas('asesmens.soalTeoriAsesi')
            ->whereHas('asesmens', fn($q) => $q->where('is_collective', false))
            ->orderByDesc('assessment_date')
            ->get()
            ->map(function ($s) {
                $asesmens    = $s->asesmens->where('is_collective', false);
                $sudahSubmit = 0;
                $belumSubmit = 0;
                foreach ($asesmens as $a) {
                    $soal = $a->soalTeoriAsesi;
                    if ($soal->isEmpty()) continue;
                    if ($soal->whereNotNull('submitted_at')->count() > 0) $sudahSubmit++;
                    else $belumSubmit++;
                }
                return [
                    'schedule_id'   => $s->id,
                    'skema_name'    => $s->skema?->name ?? '-',
                    'tanggal'       => $s->assessment_date,
                    'total_asesi'   => $asesmens->count(),
                    'sudah_submit'  => $sudahSubmit,
                    'belum_submit'  => $belumSubmit,
                    'semua_selesai' => $belumSubmit === 0 && ($sudahSubmit > 0),
                ];
            });

        return view('manajer-sertifikasi.export-hasil-teori.index', compact('batches', 'jadwalMandiri'));
    }

    /**
     * Export Excel hasil teori per batch kolektif.
     * GET /manajer-sertifikasi/export-hasil-teori/batch/{batchId}
     */
    public function exportBatch(string $batchId)
    {
        $asesmens = Asesmen::with([
            'soalTeoriAsesi.soalTeori',
            'schedule',
            'skema',
        ])
        ->where('collective_batch_id', $batchId)
        ->whereHas('soalTeoriAsesi')
        ->get();

        abort_if($asesmens->isEmpty(), 404, 'Batch tidak ditemukan atau tidak punya data soal teori.');

        return $this->streamExcel($asesmens, "Hasil_Teori_Batch_{$batchId}");
    }

    /**
     * Export Excel hasil teori per jadwal (untuk mandiri).
     * GET /manajer-sertifikasi/export-hasil-teori/jadwal/{schedule}
     */
    public function exportJadwal(Schedule $schedule)
    {
        $asesmens = Asesmen::with([
            'soalTeoriAsesi.soalTeori',
            'schedule',
            'skema',
        ])
        ->where('schedule_id', $schedule->id)
        ->whereHas('soalTeoriAsesi')
        ->get();

        abort_if($asesmens->isEmpty(), 404, 'Tidak ada data soal teori untuk jadwal ini.');

        $slug = str_replace([' ', '/'], ['_', '-'], $schedule->skema?->name ?? 'Asesmen');
        return $this->streamExcel($asesmens, "Hasil_Teori_{$slug}_{$schedule->assessment_date->format('d-m-Y')}");
    }

    // =========================================================================
    // PRIVATE
    // =========================================================================

    private function streamExcel($asesmens, string $filename)
    {
        $rows = $asesmens->map(function ($a, $i) {
            $soalAsesi  = $a->soalTeoriAsesi;
            $total      = $soalAsesi->count();
            $submitted  = $soalAsesi->whereNotNull('submitted_at')->count() > 0;

            $benar = 0;
            if ($submitted) {
                foreach ($soalAsesi as $sa) {
                    if ($sa->jawaban !== null && $sa->soalTeori && $sa->jawaban === $sa->soalTeori->jawaban_benar) {
                        $benar++;
                    }
                }
            }

            $skor       = $total > 0 && $submitted ? round($benar / $total * 100, 2) : null;
            $submittedAt = $soalAsesi->whereNotNull('submitted_at')->max('submitted_at');

            return [
                $i + 1,
                $a->full_name,
                $a->institution ?? '-',
                $a->schedule?->assessment_date?->translatedFormat('d F Y') ?? '-',
                $submitted ? ($skor . ' / 100') : 'Belum Submit',
                $submitted ? "{$benar}/{$total} Benar" : '-',
                $submittedAt ? \Carbon\Carbon::parse($submittedAt)->translatedFormat('d M Y H:i') : '-',
                $a->skema?->name ?? '-',
            ];
        });

        $headers = ['No', 'Nama Peserta', 'Asal Lembaga', 'Tanggal Pelaksanaan', 'Skor Nilai', 'Jawaban Benar', 'Waktu Submit', 'Skema'];

        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
            fputcsv($handle, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }
}