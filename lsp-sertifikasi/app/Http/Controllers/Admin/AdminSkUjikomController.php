<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\BeritaAcaraAsesi;
use App\Models\Schedule;
use App\Models\SkHasilUjikom;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminSkUjikomController extends Controller
{
    /**
     * Daftar semua batch + status SK masing-masing (versi admin).
     */
    public function index()
    {
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

            $schedules = Schedule::with(['beritaAcara'])
                ->whereHas('asesmens', fn($q) => $q->where('collective_batch_id', $batchId))
                ->get();

            $totalJadwal = $schedules->count();
            $totalBA     = $schedules->filter(fn($s) => $s->beritaAcara !== null)->count();

            $sk = SkHasilUjikom::where('collective_batch_id', $batchId)->first();

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
                'total_jadwal' => $totalJadwal,
                'total_ba'     => $totalBA,
                'total_k'      => $totalK,
                'total_bk'     => $totalBK,
                'siap'         => $totalBA > 0,
                'sk'           => $sk,
            ];
        })->sortByDesc(fn($d) => $d['sk']?->created_at ?? '0');

        return view('admin.sk-ujikom.index', compact('data'));
    }

    /**
     * Form generate SK (admin — langsung approve).
     */
    public function create(string $batchId)
    {
        $existing = SkHasilUjikom::where('collective_batch_id', $batchId)->first();
        if ($existing) {
            return redirect()->route('admin.sk-ujikom.show', $existing)
                ->with('info', 'SK untuk batch ini sudah ada.');
        }

        $first = Asesmen::with(['tuk', 'skema'])
            ->where('collective_batch_id', $batchId)
            ->firstOrFail();

        $schedules = Schedule::with(['beritaAcara', 'asesor', 'tuk'])
            ->whereHas('asesmens', fn($q) => $q->where('collective_batch_id', $batchId))
            ->get();

        $scheduleIds     = $schedules->pluck('id');
        $peserta = $this->getPesertaSemua($scheduleIds);

        abort_if($peserta->isEmpty(), 422, 'Belum ada peserta yang dinilai (K/BK) di batch ini.');

        return view('admin.sk-ujikom.create', compact(
            'batchId',
            'first',
            'schedules',
            'peserta'
        ) + ['pesertaKompeten' => $peserta]);
    }
    /**
     * Generate SK langsung approved + PDF.
     */
public function store(Request $request)
{
    $request->validate([
        'collective_batch_id' => 'required|string',
        'nomor_sk'            => 'required|string|max:100',
        'tanggal_pleno'       => 'required|date',
        'tempat_dikeluarkan'  => 'required|string|max:100',
    ]);

    $batchId = $request->collective_batch_id;

    abort_if(
        SkHasilUjikom::where('collective_batch_id', $batchId)->exists(),
        422,
        'SK untuk batch ini sudah ada.'
    );

    DB::beginTransaction();
    try {
        $sk = SkHasilUjikom::create([
            'collective_batch_id' => $batchId,
            'nomor_sk'            => $request->nomor_sk,
            'tanggal_pleno'       => $request->tanggal_pleno,
            'tempat_dikeluarkan'  => $request->tempat_dikeluarkan,
            'status'              => 'approved',
            'submitted_at'        => now(),
            'approved_at'         => now(),
            'approved_by'         => Auth::id(),
            'created_by'          => Auth::id(),
        ]);

        // Generate PDF langsung
        $skPath = $this->generatePdf($sk);
        $sk->update(['sk_path' => $skPath]);

        // ── FIX: sinkronkan status & result Asesmen berdasarkan rekomendasi BA ──
        $this->syncAsesmenStatusFromBa($batchId);

        DB::commit();

        Log::info("Admin #" . Auth::id() . " generate SK Ujikom batch {$batchId}. Nomor: {$request->nomor_sk}");

        return redirect()->route('admin.sk-ujikom.show', $sk)
            ->with('success', 'SK berhasil digenerate dan disetujui.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('[AdminSkUjikom][store] ' . $e->getMessage());
        return back()->with('error', 'Gagal generate SK: ' . $e->getMessage())->withInput();
    }
}

    /**
     * Detail SK.
     */
    public function show(SkHasilUjikom $skUjikom)
    {
        $schedules = Schedule::with(['beritaAcara', 'asesor', 'tuk', 'skema'])
            ->whereHas('asesmens', fn($q) => $q->where('collective_batch_id', $skUjikom->collective_batch_id))
            ->get();

        $scheduleIds     = $schedules->pluck('id');
        $peserta = $this->getPesertaSemua($scheduleIds);

        $first = Asesmen::with(['tuk', 'skema'])
            ->where('collective_batch_id', $skUjikom->collective_batch_id)
            ->first();

        return view('admin.sk-ujikom.show', compact(
            'skUjikom',
            'schedules',
            'peserta',
            'first'
        ));
    }

    /**
     * Download PDF SK.
     */
    public function download(SkHasilUjikom $skUjikom)
    {
        abort_unless($skUjikom->isApproved() && $skUjikom->hasSk(), 403, 'SK belum tersedia.');
        abort_unless(Storage::disk('private')->exists($skUjikom->sk_path), 404, 'File tidak ditemukan.');

        $filename = 'SK_Hasil_Ujikom_' . str_replace(['/', ' '], ['-', '_'], $skUjikom->nomor_sk) . '.pdf';

        return response()->streamDownload(function () use ($skUjikom) {
            echo Storage::disk('private')->get($skUjikom->sk_path);
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    /**
     * Regenerate PDF SK (jika ada update data).
     */
    public function regenerate(SkHasilUjikom $skUjikom)
    {
        try {
            $skPath = $this->generatePdf($skUjikom->fresh());
            $skUjikom->update(['sk_path' => $skPath]);

            return back()->with('success', 'SK berhasil di-generate ulang.');
        } catch (\Exception $e) {
            Log::error('[AdminSkUjikom][regenerate] ' . $e->getMessage());
            return back()->with('error', 'Gagal regenerate: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Ambil SEMUA peserta (K & BK) dari schedule IDs, dikelompokkan per jadwal (untuk asesor rowspan).
     * Return: collection of Asesmen, dengan tambahan _asesor & _rekomendasi.
     */
    private function getPesertaSemua($scheduleIds)
    {
        return BeritaAcaraAsesi::with(['asesmen', 'beritaAcara.schedule.asesor'])
            ->whereHas('beritaAcara', fn($q) => $q->whereIn('schedule_id', $scheduleIds))
            ->whereIn('rekomendasi', ['K', 'BK'])
            ->get()
            ->map(function ($baa) {
                $asesi = $baa->asesmen;
                if ($asesi) {
                    $asesi->_asesor      = $baa->beritaAcara?->schedule?->asesor;
                    $asesi->_rekomendasi = $baa->rekomendasi;
                }
                return $asesi;
            })
            ->filter()
            ->values();
    }


    /**
 * Sinkronkan status & result Asesmen berdasarkan rekomendasi K/BK di Berita Acara,
 * dipanggil setelah SK Hasil Ujikom di-generate/approve.
 *
 * - Peserta rekomendasi K → status: 'certified', result: 'kompeten'
 * - Peserta rekomendasi BK → status: 'assessed' (final, tanpa sertifikat), result: 'belum_kompeten'
 */
private function syncAsesmenStatusFromBa(string $batchId): void
{
    $schedules = Schedule::whereHas('asesmens', fn($q) => $q->where('collective_batch_id', $batchId))
        ->pluck('id');

    $rekomendasiMap = BeritaAcaraAsesi::whereHas('beritaAcara', fn($q) => $q->whereIn('schedule_id', $schedules))
        ->whereIn('rekomendasi', ['K', 'BK'])
        ->get()
        ->pluck('rekomendasi', 'asesmen_id'); // [asesmen_id => 'K'|'BK']

    $kIds  = $rekomendasiMap->filter(fn($r) => $r === 'K')->keys();
    $bkIds = $rekomendasiMap->filter(fn($r) => $r === 'BK')->keys();

    if ($kIds->isNotEmpty()) {
        Asesmen::whereIn('id', $kIds)
            ->where('collective_batch_id', $batchId)
            ->update([
                'status' => 'certified',
                'result' => 'kompeten',
            ]);
    }

    if ($bkIds->isNotEmpty()) {
        Asesmen::whereIn('id', $bkIds)
            ->where('collective_batch_id', $batchId)
            ->update([
                'status' => 'assessed', // tetap di 'assessed', tidak pernah certified
                'result' => 'belum_kompeten',
            ]);
    }

    Log::info("[SyncAsesmenStatus] Batch {$batchId}: {$kIds->count()} → certified, {$bkIds->count()} → assessed (BK)");
}

    /**
     * Generate PDF SK dan simpan ke storage private.
     * Return path file.
     */
    private function generatePdf(SkHasilUjikom $sk): string
    {
        $schedules = Schedule::with(['tuk', 'skema', 'asesor', 'beritaAcara'])
            ->whereHas('asesmens', fn($q) => $q->where('collective_batch_id', $sk->collective_batch_id))
            ->get();

        $scheduleIds     = $schedules->pluck('id');
        $peserta = $this->getPesertaSemua($scheduleIds);

        $first = Asesmen::with(['tuk', 'skema'])
            ->where('collective_batch_id', $sk->collective_batch_id)
            ->first();

        // Kelompokkan peserta per asesor untuk rowspan di PDF
        $pesertaPerAsesor = $this->groupByAsesor($peserta, $schedules);

        $pdf = Pdf::loadView('pdf.sk-hasil-ujikom', [
            'skUjikom'         => $sk,
            'pesertaKompeten'  => $peserta, // nama var dipertahankan di PDF template
            'pesertaPerAsesor' => $pesertaPerAsesor,
            'schedules'        => $schedules,
            'first'            => $first,
            'preview'          => false,
        ])->setPaper('A4', 'portrait');

        $dir  = 'sk-ujikom';
        $path = $dir . '/SK_' . str_replace(['/', ' '], ['_', '_'], $sk->nomor_sk) . '_' . $sk->id . '.pdf';

        Storage::disk('private')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Kelompokkan peserta berdasarkan asesor jadwalnya.
     * Return: array of ['asesor' => Asesor|null, 'asesis' => Collection, 'count' => int]
     */
    private function groupByAsesor($pesertaKompeten, $schedules): array
    {
        // Map schedule_id → asesor
        $asesorBySchedule = $schedules->keyBy('id')->map(fn($s) => $s->asesor);

        $groups = [];
        foreach ($pesertaKompeten as $asesi) {
            $asesor    = $asesi->_asesor;
            $asesorKey = $asesor?->id ?? 'tanpa_asesor';

            if (!isset($groups[$asesorKey])) {
                $groups[$asesorKey] = [
                    'asesor' => $asesor,
                    'asesis' => collect(),
                ];
            }
            $groups[$asesorKey]['asesis']->push($asesi);
        }

        // Tambahkan count
        foreach ($groups as &$g) {
            $g['count'] = $g['asesis']->count();
        }

        return array_values($groups);
    }
}
