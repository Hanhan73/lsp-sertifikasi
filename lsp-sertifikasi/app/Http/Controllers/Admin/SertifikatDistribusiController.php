<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SertifikatDistribusiController extends Controller
{
public function index()
{
    $asesmens = Asesmen::with(['schedule.beritaAcara', 'tuk', 'skema'])
        ->whereIn('status', ['assessed', 'certified', 'certificate_distributed'])
        ->orderByDesc('assessed_at')
        ->get();

    $batches = $asesmens->where('is_collective', true)
        ->groupBy('collective_batch_id')
        ->map(function ($members) {
            $first = $members->first();

            // ── Hanya peserta KOMPETEN yang relevan buat status SK/distribusi ──
            // Peserta BK tidak pernah di-SK-kan / didistribusikan, jadi tidak dihitung
            // dalam pengecekan "semua sudah di-SK".
            $eligible   = $members->filter(fn ($m) => $m->result === 'kompeten');
            $bkCount    = $members->filter(fn ($m) => $m->result === 'belum_kompeten')->count();
            $belumAda   = $members->filter(fn ($m) => is_null($m->result))->count();

            $certifiedCount = $eligible->filter(fn ($m) => in_array($m->status, ['certified', 'certificate_distributed']))->count();

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
                    && $eligible->every(fn ($m) => $m->status === 'certified'),
                'sudah_distribusi' => $eligible->isNotEmpty()
                    && $eligible->every(fn ($m) => $m->status === 'certificate_distributed'),
                'sudah_upload'     => $eligible->isNotEmpty()
                    && $eligible->every(fn ($m) => $m->hasUploadedPhysicalCertificate()),
            ];
        })
        ->values();

    $mandiri = $asesmens->where('is_collective', false)->values();

    return view('admin.sertifikat-distribusi.index', compact('batches', 'mandiri'));
}

    public function distributeBatch(Request $request, string $batchId)
    {
        $members = Asesmen::where('collective_batch_id', $batchId)
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