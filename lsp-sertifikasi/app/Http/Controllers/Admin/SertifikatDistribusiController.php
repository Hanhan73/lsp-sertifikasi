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

        // ── Kolektif: kelompokkan per batch ──
        $batches = $asesmens->where('is_collective', true)
            ->groupBy('collective_batch_id')
            ->map(function ($members) {
                $first = $members->first();
                return (object) [
                    'batch_id'         => $first->collective_batch_id,
                    'tuk'              => $first->tuk,
                    'skema'            => $first->skema,
                    'total'            => $members->count(),
                    'ada_ba'           => $members->contains(fn ($m) => $m->schedule?->beritaAcara !== null),
                    'siap_distribusi'  => $members->every(fn ($m) => $m->status === 'certified'),
                    'sudah_distribusi' => $members->every(fn ($m) => $m->status === 'certificate_distributed'),
                    'sudah_upload'     => $members->every(fn ($m) => $m->hasUploadedPhysicalCertificate()),
                ];
            })
            ->values();

        // ── Mandiri: per-asesi ──
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