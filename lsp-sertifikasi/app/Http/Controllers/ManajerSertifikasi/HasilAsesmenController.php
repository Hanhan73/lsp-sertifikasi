<?php

namespace App\Http\Controllers\ManajerSertifikasi;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\BeritaAcaraAsesi;
use App\Models\HasilObservasi;
use App\Models\HasilPortofolio;
use App\Models\JawabanObservasiAsesi;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HasilAsesmenController extends Controller
{
    public function index(Request $request)
    {
        $jadwalQuery = Schedule::with(['skema', 'tuk', 'beritaAcara.asesis', 'asesmens'])
            ->approved()
            ->withCount('asesmens');

        if ($search = $request->input('search')) {
            $jadwalQuery->where(function ($q) use ($search) {
                $q->whereHas('skema', fn($s) => $s->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('tuk', fn($t) => $t->where('name', 'like', "%{$search}%"));
            });
        }

        $filterStatus = $request->input('status');
        if ($filterStatus === 'selesai') {
            $jadwalQuery->where('assessment_date', '<', now()->toDateString());
        } elseif ($filterStatus === 'mendatang') {
            $jadwalQuery->where('assessment_date', '>=', now()->toDateString());
        }

        $jadwalList = $jadwalQuery->orderByDesc('assessment_date')
            ->paginate(15, ['*'], 'jadwal_page')
            ->withQueryString();

        $batches = Asesmen::select('collective_batch_id')
            ->whereNotNull('collective_batch_id')
            ->where('is_collective', true)
            ->distinct()
            ->pluck('collective_batch_id');

        $batchData = $batches->map(function ($batchId) {
            $first = Asesmen::with(['tuk', 'skema'])
                ->where('collective_batch_id', $batchId)->first();

            $schedules   = Schedule::with(['beritaAcara'])
                ->whereHas('asesmens', fn($q) => $q->where('collective_batch_id', $batchId))
                ->get();
            $scheduleIds = $schedules->pluck('id');

            $totalK  = BeritaAcaraAsesi::whereHas('beritaAcara', fn($q) => $q->whereIn('schedule_id', $scheduleIds))
                ->where('rekomendasi', 'K')->count();
            $totalBK = BeritaAcaraAsesi::whereHas('beritaAcara', fn($q) => $q->whereIn('schedule_id', $scheduleIds))
                ->where('rekomendasi', 'BK')->count();

            return [
                'batch_id'     => $batchId,
                'tuk'          => $first?->tuk,
                'skema'        => $first?->skema,
                'total_asesi'  => Asesmen::where('collective_batch_id', $batchId)->count(),
                'total_jadwal' => $schedules->count(),
                'total_ba'     => $schedules->filter(fn($s) => $s->beritaAcara !== null)->count(),
                'total_k'      => $totalK,
                'total_bk'     => $totalBK,
                'tanggal'      => $schedules->min('assessment_date'),
            ];
        })->sortByDesc('tanggal')->values();

        $activeTab = $request->input('tab', 'jadwal');

        return view('manajer-sertifikasi.hasil-asesmen.index', compact(
            'jadwalList', 'batchData', 'filterStatus', 'activeTab'
        ));
    }

    public function showJadwal(Schedule $schedule)
    {
        $schedule->load([
            'skema', 'tuk', 'asesor.user', 'asesmens',
            'distribusiSoalObservasi.soalObservasi',
            'distribusiSoalObservasi.paketSoalObservasi',
            'distribusiPortofolio.portofolio',
            'beritaAcara.asesis',
        ]);

        $distribusiTeori = $schedule->distribusiSoalTeori()->with('soalAsesi.soalTeori')->first();
        $hasilObservasi  = HasilObservasi::where('schedule_id', $schedule->id)->get();
        $hasilPortofolio = HasilPortofolio::where('schedule_id', $schedule->id)->get();
        $beritaAcara     = $schedule->beritaAcara;

        $rekomendasiMap = [];
        if ($beritaAcara) {
            foreach ($beritaAcara->asesis as $ba) {
                $rekomendasiMap[$ba->asesmen_id] = $ba->rekomendasi;
            }
        }

        $jawabanObservasi = JawabanObservasiAsesi::whereIn(
            'distribusi_soal_observasi_id',
            $schedule->distribusiSoalObservasi->pluck('id')
        )->get();

        return view('manajer-sertifikasi.hasil-asesmen.show-jadwal', [
            'schedule'         => $schedule,
            'distribusiTeori'  => $distribusiTeori,
            'hasilObservasi'   => $hasilObservasi,
            'hasilPortofolio'  => $hasilPortofolio,
            'beritaAcara'      => $beritaAcara,
            'rekomendasiMap'   => $rekomendasiMap,
            'jawabanObservasi' => $jawabanObservasi,
            'totalObservasi'   => $schedule->distribusiSoalObservasi->count(),
            'totalPortofolio'  => $schedule->distribusiPortofolio->count(),
        ]);
    }

    public function showBatch(string $batchId)
    {
        $first = Asesmen::with(['tuk', 'skema'])
            ->where('collective_batch_id', $batchId)
            ->firstOrFail();

        $schedules = Schedule::with([
            'tuk', 'skema', 'asesor.user', 'asesmens',
            'distribusiSoalObservasi.soalObservasi',
            'distribusiSoalObservasi.paketSoalObservasi',
            'distribusiPortofolio.portofolio',
            'beritaAcara.asesis', 'hasilObservasi', 'hasilPortofolio', 
        ])
        ->whereHas('asesmens', fn($q) => $q->where('collective_batch_id', $batchId))
        ->orderBy('assessment_date')
        ->get();

        $scheduleIds              = $schedules->pluck('id');
        $hasilObservasiAll        = HasilObservasi::whereIn('schedule_id', $scheduleIds)->get();
        $hasilPortofolioAll       = HasilPortofolio::whereIn('schedule_id', $scheduleIds)->get();
        $distribusiTeoriMap       = [];
        $allDistribusiObservasiIds = collect();

        foreach ($schedules as $s) {
            $distribusiTeoriMap[$s->id] = $s->distribusiSoalTeori()->with('soalAsesi.soalTeori')->first();
            $allDistribusiObservasiIds  = $allDistribusiObservasiIds->merge($s->distribusiSoalObservasi->pluck('id'));
        }

        $jawabanObservasiAll = JawabanObservasiAsesi::whereIn('distribusi_soal_observasi_id', $allDistribusiObservasiIds)->get();

        $rekomendasiMap = [];
        $totalK = 0; $totalBK = 0;
        foreach ($schedules as $s) {
            if ($s->beritaAcara) {
                foreach ($s->beritaAcara->asesis as $ba) {
                    $rekomendasiMap[$ba->asesmen_id] = $ba->rekomendasi;
                    if ($ba->rekomendasi === 'K')  $totalK++;
                    if ($ba->rekomendasi === 'BK') $totalBK++;
                }
            }
        }
        $adaObservasi = $schedules->flatMap->hasilObservasi->isNotEmpty();
        $adaBA        = $schedules->some(fn($s) => $s->beritaAcara !== null);

        return view('manajer-sertifikasi.hasil-asesmen.show-batch', [
            'batchId'             => $batchId,
            'first'               => $first,
            'schedules'           => $schedules,
            'distribusiTeoriMap'  => $distribusiTeoriMap,
            'hasilObservasiAll'   => $hasilObservasiAll,
            'hasilPortofolioAll'  => $hasilPortofolioAll,
            'jawabanObservasiAll' => $jawabanObservasiAll,
            'rekomendasiMap'      => $rekomendasiMap,
            'totalK'              => $totalK,
            'totalBK'             => $totalBK,
            'adaObservasi'        => $adaObservasi,
            'adaBA'               => $adaBA,
        ]);
    }

    /**
     * Serve foto dokumentasi dari private storage untuk manajer.
     * GET /manajer-sertifikasi/hasil-asesmen/jadwal/{schedule}/foto/{slot}
     */
    public function previewFoto(Schedule $schedule, int $slot)
    {
        abort_unless(in_array($slot, [1, 2]), 404);

        $col  = "foto_dokumentasi_{$slot}";
        $path = $schedule->$col;

        abort_unless($path && Storage::disk('private')->exists($path), 404, 'Foto tidak ditemukan.');

        $mime = Storage::disk('private')->mimeType($path) ?: 'image/jpeg';
        $ext  = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';

        $headers = [
            'Content-Type'  => $mime,
            'Cache-Control' => 'private, max-age=300',
        ];

        if (request()->boolean('download')) {
            $skema   = preg_replace('/[\/\\\s]+/', '_', $schedule->skema->name ?? 'foto');
            $tanggal = $schedule->assessment_date->format('Ymd');
            $headers['Content-Disposition'] = "attachment; filename=\"Foto_{$slot}_{$skema}_{$tanggal}.{$ext}\"";
        }

        return response(Storage::disk('private')->get($path), 200, $headers);
    }
}