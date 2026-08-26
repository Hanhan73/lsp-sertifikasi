<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SertifikatDistribusiController extends Controller
{
    /**
     * Resolusi hasil K/BK per asesmen.
     * Prioritas: $asesmen->result (kalau resmi sudah diisi) → fallback ke rekomendasi Berita Acara.
     * Sumber kebenaran K/BK yang sebenarnya ada di BeritaAcaraAsesi->rekomendasi,
     * karena kolom `result` di tabel asesmens sering tidak terisi oleh alur sistem saat ini.
     */
    private function resolveResult(Asesmen $m): ?string
    {
        if ($m->result) {
            return $m->result; // 'kompeten' | 'belum_kompeten'
        }

        $rekom = $m->schedule?->beritaAcara?->asesis
            ->where('asesmen_id', $m->id)
            ->first()?->rekomendasi; // 'K' | 'BK'

        return match ($rekom) {
            'K'     => 'kompeten',
            'BK'    => 'belum_kompeten',
            default => null,
        };
    }

    public function index()
    {
        $asesmens = Asesmen::with([
                'schedule.beritaAcara.asesis',
                'tuk', 'skema',
            ])
            ->whereIn('status', ['assessed', 'certified', 'certificate_distributed'])
            ->orderByDesc('assessed_at')
            ->get();

        $batches = $asesmens->where('is_collective', true)
            ->groupBy('collective_batch_id')
            ->map(function ($members) {
                $first = $members->first();

                // Resolusi hasil tiap member sekali aja, simpan biar nggak recompute
                $resolved = $members->map(fn ($m) => [
                    'asesmen' => $m,
                    'result'  => $this->resolveResult($m),
                ]);

                $eligible  = $resolved->filter(fn ($r) => $r['result'] === 'kompeten');
                $bkCount   = $resolved->filter(fn ($r) => $r['result'] === 'belum_kompeten')->count();
                $belumAda  = $resolved->filter(fn ($r) => is_null($r['result']))->count();

                $certifiedCount = $eligible->filter(
                    fn ($r) => in_array($r['asesmen']->status, ['certified', 'certificate_distributed'])
                )->count();

                return (object) [
                    'batch_id'         => $first->collective_batch_id,
                    'tuk'              => $first->tuk,
                    'skema'            => $first->skema,
                    'total'            => $members->count(),
                    'total_kompeten'   => $eligible->count(),
                    'bk_count'         => $bkCount,
                    'belum_ada_hasil'  => $belumAda,
                    'certified_count'  => $certifiedCount,
                    'ada_ba'           => $members->contains(fn ($m) => $m->schedule?->beritaAcara !== null),
                    'siap_distribusi'  => $eligible->isNotEmpty()
                        && $eligible->every(fn ($r) => $r['asesmen']->status === 'certified'),
                    'sudah_distribusi' => $eligible->isNotEmpty()
                        && $eligible->every(fn ($r) => $r['asesmen']->status === 'certificate_distributed'),
                    'sudah_upload'     => $eligible->isNotEmpty()
                        && $eligible->every(fn ($r) => $r['asesmen']->hasUploadedPhysicalCertificate()),
                ];
            })
            ->values();

        // ── Mandiri: sama, pakai resolveResult juga biar konsisten ──
        $mandiri = $asesmens->where('is_collective', false)->values();

        return view('admin.sertifikat-distribusi.index', compact('batches', 'mandiri'));
    }

    public function distributeBatch(Request $request, string $batchId)
    {
        $members = Asesmen::with('schedule.beritaAcara.asesis')
            ->where('collective_batch_id', $batchId)
            ->where('status', 'certified')
            ->get();

        if ($members->isEmpty()) {
            return back()->with('error', 'Tidak ada asesi berstatus "Tersertifikasi" pada batch ini (mungkin belum di-SK atau sudah didistribusikan).');
        }

        DB::transaction(function () use ($members) {
            foreach ($members as $m) {
                $m->update([
                    'status'         => 'certificate_distributed',
                    'distributed_at' => now(),
                    'distributed_by' => auth()->id(),
                ]);
            }
        });

        return back()->with('success', "Status {$members->count()} asesi pada batch {$batchId} diubah menjadi 'Sertifikat Didistribusikan'.");
    }

    public function distributeIndividual(Request $request, Asesmen $asesmen)
    {
        if ($asesmen->status !== 'certified') {
            return back()->with('error', 'Asesi ini belum berstatus "Tersertifikasi".');
        }

        $asesmen->update([
            'status'         => 'certificate_distributed',
            'distributed_at' => now(),
            'distributed_by' => auth()->id(),
        ]);

        return back()->with('success', "Status {$asesmen->full_name} diubah menjadi 'Sertifikat Didistribusikan'.");
    }
}