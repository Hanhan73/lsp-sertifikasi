<?php

namespace App\Http\Controllers\ManajerSertifikasi;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\FrAk03UmpanBalik;
use App\Models\Schedule;
use Illuminate\Http\Request;

class FrAk03ManajerController extends Controller
{
    // =========================================================================
    // MANAJER — List semua jadwal + status FR.AK.03
    // =========================================================================

    public function index(Request $request)
    {
        $query = Schedule::with([
            'skema',
            'tuk',
            'asesor',
            'asesmens.frAk03',
        ])->orderBy('assessment_date', 'desc');

        if ($request->filled('skema_id')) {
            $query->where('skema_id', $request->skema_id);
        }

        $schedules = $query->paginate(15)->withQueryString();

        // Hitung statistik per schedule
        $schedules->getCollection()->transform(function ($schedule) {
            $total     = $schedule->asesmens->count();
            $submitted = $schedule->asesmens->filter(fn($a) => $a->frAk03 && $a->frAk03->isSubmitted())->count();

            $schedule->frak03_total     = $total;
            $schedule->frak03_submitted = $submitted;
            $schedule->frak03_pct       = $total > 0 ? round($submitted / $total * 100) : 0;

            return $schedule;
        });

        return view('manajer-sertifikasi.frak03.index', compact('schedules'));
    }

    // =========================================================================
    // MANAJER — Detail per jadwal: list asesi + status FR.AK.03
    // =========================================================================

    public function detail(Schedule $schedule)
    {
        $schedule->load([
            'skema',
            'tuk',
            'asesor',
            'asesmens.frAk03',
            'asesmens.user',
        ]);

        return view('manajer-sertifikasi.frak03.detail', compact('schedule'));
    }

    // =========================================================================
    // MANAJER — Export PDF FR.AK.03 per asesi
    // =========================================================================

    public function exportPdf(Schedule $schedule, Asesmen $asesmen)
    {
        abort_if($asesmen->schedule_id !== $schedule->id, 403);

        $frAk03 = $asesmen->frAk03;
        abort_unless($frAk03 && $frAk03->isSubmitted(), 404, 'FR.AK.03 belum disubmit oleh asesi.');

        $asesmen->load(['skema', 'schedule.asesor']);

        $pertanyaan = self::pertanyaanList();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.frak03', [
            'asesmen'    => $asesmen,
            'schedule'   => $schedule,
            'frAk03'     => $frAk03,
            'pertanyaan' => $pertanyaan,
        ])->setPaper('A4', 'portrait');

        $nama     = str_replace(' ', '_', $asesmen->full_name);
        $filename = "FR.AK.03_{$nama}.pdf";

        return request()->boolean('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

    // =========================================================================
    // Static — daftar 10 pertanyaan FR.AK.03
    // =========================================================================

    public static function pertanyaanList(): array
    {
        return [
            'Saya mendapatkan penjelasan yang cukup memadai mengenai proses asesmen/uji kompetensi',
            'Saya diberikan kesempatan untuk mempelajari standar kompetensi yang akan diujikan dan menilai diri sendiri terhadap pencapaiannya',
            'Asesor memberikan kesempatan untuk mendiskusikan/ menegosiasikan metoda, instrumen dan sumber asesmen serta jadwal asesmen',
            'Asesor berusaha menggali seluruh bukti pendukung yang sesuai dengan latar belakang pelatihan dan pengalaman yang saya miliki',
            'Saya sepenuhnya diberikan kesempatan untuk mendemonstrasikan kompetensi yang saya miliki selama asesmen',
            'Saya mendapatkan penjelasan yang memadai mengenai keputusan asesmen',
            'Asesor memberikan umpan balik yang mendukung setelah asesmen serta tindak lanjutnya',
            'Asesor bersama saya mempelajari semua dokumen asesmen serta menandatanganinya',
            'Saya mendapatkan jaminan kerahasiaan hasil asesmen serta penjelasan penanganan dokumen asesmen',
            'Asesor menggunakan keterampilan komunikasi yang efektif selama asesmen',
        ];
    }
}