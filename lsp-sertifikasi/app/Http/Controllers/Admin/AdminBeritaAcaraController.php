<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\BeritaAcara;
use App\Models\BeritaAcaraAsesi;
use App\Models\Schedule;
use App\Models\Tuk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * AdminBeritaAcaraController
 *
 * Generate Berita Acara PDF dari sisi admin:
 *  - Per batch kolektif → 1 PDF gabungan semua jadwal dalam batch
 *  - Per asesi mandiri  → 1 PDF untuk 1 asesi mandiri
 *
 * Tanggal surat = tanggal pelaksanaan terakhir + 7 hari.
 */
class AdminBeritaAcaraController extends Controller
{
    // =========================================================================
    // INDEX — Daftar batch siap generate BA
    // =========================================================================

    public function index()
    {
        // Ambil semua batch yang punya minimal 1 BA
        $batches = Asesmen::select('collective_batch_id')
            ->whereNotNull('collective_batch_id')
            ->where('is_collective', true)
            ->whereHas('schedule.beritaAcara')
            ->distinct()
            ->pluck('collective_batch_id');

        $data = $batches->map(function ($batchId) {
            $first = Asesmen::with(['tuk', 'skema'])
                ->where('collective_batch_id', $batchId)
                ->first();

            $schedules = Schedule::with(['beritaAcara', 'asesor'])
                ->whereHas('asesmens', fn($q) => $q->where('collective_batch_id', $batchId))
                ->get();

            $scheduleIds = $schedules->pluck('id');
            $totalK  = BeritaAcaraAsesi::whereHas('beritaAcara', fn($q) => $q->whereIn('schedule_id', $scheduleIds))
                ->where('rekomendasi', 'K')->count();
            $totalBK = BeritaAcaraAsesi::whereHas('beritaAcara', fn($q) => $q->whereIn('schedule_id', $scheduleIds))
                ->where('rekomendasi', 'BK')->count();

            // Tanggal terakhir pelaksanaan dalam batch
            $tanggalTerakhir = $schedules->map(fn($s) => $s->assessment_date)->max();

            return [
                'batch_id'        => $batchId,
                'tuk'             => $first?->tuk,
                'skema'           => $first?->skema,
                'total_asesi'     => Asesmen::where('collective_batch_id', $batchId)->count(),
                'total_ba'        => $schedules->filter(fn($s) => $s->beritaAcara !== null)->count(),
                'total_jadwal'    => $schedules->count(),
                'total_k'         => $totalK,
                'total_bk'        => $totalBK,
                'tanggal_terakhir'=> $tanggalTerakhir,
                'tanggal_surat'   => \Carbon\Carbon::parse($tanggalTerakhir)->addDays(7)->translatedFormat('d F Y'),
            ];
        })->sortByDesc('tanggal_terakhir')->values();

        return view('admin.berita-acara.index', compact('data'));
    }

    // =========================================================================
    // DOWNLOAD — PDF BA per batch (gabungan semua jadwal)
    // =========================================================================

    public function downloadBatch(string $batchId)
    {
        $schedules = Schedule::with([
            'tuk', 'skema', 'asesor.user',
            'asesmens',
            'beritaAcara.asesis.asesmen',
        ])
            ->whereHas('asesmens', fn($q) => $q->where('collective_batch_id', $batchId))
            ->whereHas('beritaAcara')
            ->orderBy('assessment_date')
            ->get();

        abort_if($schedules->isEmpty(), 404, 'Tidak ada Berita Acara untuk batch ini.');

        $first = Asesmen::with(['tuk', 'skema'])
            ->where('collective_batch_id', $batchId)
            ->first();

        // Tanggal surat = tanggal pelaksanaan terakhir + 7 hari
        $tanggalTerakhir = $schedules->map(fn($s) => $s->assessment_date)->max();
        $tanggalSurat    = \Carbon\Carbon::parse($tanggalTerakhir)->addDays(7);

        // Kumpulkan semua peserta + rekomendasi per jadwal
        $jadwalData = $schedules->map(function ($schedule) {
            $ba     = $schedule->beritaAcara;
            $rekMap = $ba ? $ba->asesis->pluck('rekomendasi', 'asesmen_id') : collect();

            return [
                'schedule' => $schedule,
                'ba'       => $ba,
                'rekMap'   => $rekMap,
                'asesmens' => $schedule->asesmens,
            ];
        });

        // Hitung total K/BK gabungan
        $totalK  = $jadwalData->sum(fn($d) => $d['rekMap']->filter(fn($r) => $r === 'K')->count());
        $totalBK = $jadwalData->sum(fn($d) => $d['rekMap']->filter(fn($r) => $r === 'BK')->count());

        $pdf = Pdf::loadView('pdf.berita-acara-batch', [
            'batchId'         => $batchId,
            'first'           => $first,
            'jadwalData'      => $jadwalData,
            'tanggalSurat'    => $tanggalSurat,
            'tanggalTerakhir' => \Carbon\Carbon::parse($tanggalTerakhir),
            'totalK'          => $totalK,
            'totalBK'         => $totalBK,
        ])->setPaper('A4', 'portrait');

        $filename = 'Berita_Acara_Batch_' . $batchId . '.pdf';

        Log::info("[AdminBA] Download BA batch {$batchId} oleh admin #" . auth()->id());

        return request()->boolean('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

    // =========================================================================
    // INDEX MANDIRI — Daftar asesi mandiri yang punya BA
    // =========================================================================

    public function indexMandiri()
    {
        // Asesi mandiri yang sudah assessed (berarti sudah ada BA dari asesor)
        $asesmens = Asesmen::with(['tuk', 'skema', 'schedule.beritaAcara', 'schedule.asesor'])
            ->where('is_collective', false)
            ->whereHas('schedule.beritaAcara')
            ->orderByDesc('updated_at')
            ->get();

        return view('admin.berita-acara.mandiri', compact('asesmens'));
    }

    // =========================================================================
    // DOWNLOAD — PDF BA mandiri
    // =========================================================================

    public function downloadMandiri(Asesmen $asesmen)
    {
        abort_if($asesmen->is_collective, 422, 'Gunakan endpoint batch untuk asesi kolektif.');

        $schedule = $asesmen->schedule;
        abort_unless($schedule, 404, 'Asesi belum dijadwalkan.');

        $ba = $schedule->beritaAcara;
        abort_unless($ba, 404, 'Berita Acara belum ada untuk asesi ini.');

        $schedule->load(['tuk', 'skema', 'asesor.user', 'asesmens', 'beritaAcara.asesis.asesmen']);

        $rekMap = $ba->asesis->pluck('rekomendasi', 'asesmen_id');

        // Tanggal surat = tanggal pelaksanaan + 7 hari
        $tanggalSurat = \Carbon\Carbon::parse($schedule->assessment_date)->addDays(7);

        $pdf = Pdf::loadView('pdf.berita-acara-mandiri', [
            'schedule'     => $schedule,
            'beritaAcara'  => $ba,
            'rekMap'       => $rekMap,
            'asesor'       => $schedule->asesor,
            'asesmen'      => $asesmen,
            'tanggalSurat' => $tanggalSurat,
        ])->setPaper('A4', 'portrait');

        $filename = 'Berita_Acara_Mandiri_' . str_replace(' ', '_', $asesmen->full_name) . '_' . $schedule->assessment_date->format('d-m-Y') . '.pdf';

        Log::info("[AdminBA] Download BA mandiri asesmen #{$asesmen->id} oleh admin #" . auth()->id());

        return request()->boolean('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }
}