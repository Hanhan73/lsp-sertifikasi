<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\Schedule;
use App\Models\Tuk;
use App\Models\Skema;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_asesi'          => Asesmen::count(),
            'total_tuk'            => Tuk::count(),
            'total_skema'          => Skema::count(),

            // Menunggu admin mulai asesmen
            'pending_mulai'        => Asesmen::where('status', 'data_completed')->count(),

            // Sudah dimulai, sedang isi dokumen
            'sedang_asesmen'       => Asesmen::where('status', 'pra_pra_asesmen_started')->count(),

            // Sudah dijadwalkan
            'scheduled'            => Asesmen::where('status', 'scheduled')->count(),

            // Belum ada asesor
            'pending_asesor'       => Schedule::whereNull('asesor_id')->count(),

            // Tersertifikasi
            'certified'            => Asesmen::where('status', 'certified')->count(),
        ];

        // 10 asesi terbaru
        $asesmens = Asesmen::with(['user', 'tuk', 'skema'])
            ->latest()
            ->take(10)
            ->get();

        // Batch kolektif terbaru
        $latestBatch = $this->getLatestBatchInfo();

        // Aktivitas yang perlu perhatian admin
        $needsAttention = [
            'mulai_asesmen' => Asesmen::where('status', 'data_completed')
                ->with(['tuk', 'skema'])
                ->latest()
                ->take(5)
                ->get(),

            'belum_asesor'  => Schedule::whereNull('asesor_id')
                ->with(['tuk', 'skema', 'asesmens'])
                ->latest()
                ->take(5)
                ->get(),
        ];

        return view('admin.dashboard', compact('stats', 'asesmens', 'latestBatch', 'needsAttention'));
    }

    private function getLatestBatchInfo(): ?array
    {
        $batchId = Asesmen::where('is_collective', true)
            ->whereNotNull('collective_batch_id')
            ->latest()
            ->value('collective_batch_id');

        if (!$batchId) return null;

        $batch = Asesmen::with(['tuk', 'registrar'])
            ->where('collective_batch_id', $batchId)
            ->get();

        if ($batch->isEmpty()) return null;

        $first = $batch->first();

        return [
            'batch_id'      => $batchId,
            'total_members' => $batch->count(),
            'tuk'           => $first->tuk,
            'registered_by' => $first->registrar,
            'status_counts' => [
                'registered'      => $batch->where('status', 'registered')->count(),
                'data_completed'  => $batch->where('status', 'data_completed')->count(),
                'pra_asesmen_started' => $batch->where('status', 'pra_asesmen_started')->count(),
                'scheduled'       => $batch->where('status', 'scheduled')->count(),
                'certified'       => $batch->where('status', 'certified')->count(),
            ],
        ];
    }
}