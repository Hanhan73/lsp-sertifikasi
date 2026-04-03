<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\Asesor;
use App\Models\Schedule;
use App\Models\Skema;
use App\Models\Tuk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DirekturDashboardController extends Controller
{
    public function index()
    {
        // ── Statistik Umum ───────────────────────────────────────────────────
        $stats = [
            'total_asesi'      => Asesmen::count(),
            'total_tuk'        => Tuk::count(),
            'total_asesor'     => Asesor::count(),
            'total_skema'      => Skema::where('is_active', true)->count(),
            'total_jadwal'     => Schedule::count(),
            'pending_approval' => Schedule::pendingApproval()->count(),
            'certified'        => Asesmen::where('status', 'certified')->count(),
        ];

        // ── Progres Asesi (per status) ───────────────────────────────────────
        $progressStatus = [
            'registered'            => ['label' => 'Baru Daftar',    'icon' => 'bi-person-plus',    'color' => '#94a3b8'],
            'data_completed'        => ['label' => 'Biodata Lengkap', 'icon' => 'bi-clipboard-check','color' => '#60a5fa'],
            'pra_asesmen_started'   => ['label' => 'Pra-Asesmen',    'icon' => 'bi-pencil-square',  'color' => '#818cf8'],
            'scheduled'             => ['label' => 'Terjadwal',      'icon' => 'bi-calendar-event', 'color' => '#f59e0b'],
            'pra_asesmen_completed' => ['label' => 'Siap Diasesmen', 'icon' => 'bi-hourglass-split','color' => '#fb923c'],
            'assessed'              => ['label' => 'Sudah Diasesmen','icon' => 'bi-person-check',   'color' => '#34d399'],
            'certified'             => ['label' => 'Tersertifikasi', 'icon' => 'bi-award',          'color' => '#10b981'],
        ];

        $statusCounts = Asesmen::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        foreach ($progressStatus as $key => &$val) {
            $val['count'] = $statusCounts[$key] ?? 0;
        }
        unset($val);

        // ── TUK — jumlah asesi per TUK ───────────────────────────────────────
        $tukBatchStats = Tuk::withCount([
            'asesmens',
            'asesmens as collective_count' => fn($q) => $q->where('is_collective', true),
            'schedules',
        ])->orderByDesc('asesmens_count')->get();

        // Batch kolektif terbaru (5 batch terakhir)
        $latestBatches = Asesmen::select(
                'collective_batch_id',
                DB::raw('count(*) as total'),
                DB::raw('min(registration_date) as created_date'),
                'tuk_id',
                'skema_id'
            )
            ->where('is_collective', true)
            ->whereNotNull('collective_batch_id')
            ->groupBy('collective_batch_id', 'tuk_id', 'skema_id')
            ->with(['tuk', 'skema'])
            ->orderByDesc('created_date')
            ->limit(5)
            ->get();

        // ── Asesor — data jadwal ─────────────────────────────────────────────
        // Cek dulu apakah tabel asesor_skema sudah ada sebelum eager-load relasi skemas
        $asesorSkemaExists = Schema::hasTable('asesor_skema');

        $asesorQuery = Asesor::withCount([
            'schedules',
            'schedules as upcoming_schedules_count' => fn($q) =>
                $q->where('assessment_date', '>=', now()),
        ])->where('is_active', true)
          ->orderByDesc('upcoming_schedules_count');

        if ($asesorSkemaExists) {
            $asesorQuery->with('skemas');
        }

        $asesorStats = $asesorQuery->get();

        // ── Skema — jumlah pendaftar ─────────────────────────────────────────
        // Hitung asesor per skema lewat schedules (bukan pivot) jika asesor_skema belum ada
        if ($asesorSkemaExists) {
            $skemaStats = Skema::withCount([
                'asesmens',
                'asesmens as certified_count' => fn($q) => $q->where('status', 'certified'),
                'asesors',
            ])
            ->where('is_active', true)
            ->orderByDesc('asesmens_count')
            ->get();
        } else {
            // Fallback: hitung asesor unik lewat schedules
            $skemaStats = Skema::withCount([
                'asesmens',
                'asesmens as certified_count' => fn($q) => $q->where('status', 'certified'),
            ])
            ->where('is_active', true)
            ->orderByDesc('asesmens_count')
            ->get()
            ->each(function ($skema) {
                $skema->asesors_count = Schedule::where('skema_id', $skema->id)
                    ->whereNotNull('asesor_id')
                    ->distinct('asesor_id')
                    ->count('asesor_id');
            });
        }

        // ── Jadwal menunggu approval ─────────────────────────────────────────
        $pendingSchedules = Schedule::with(['tuk', 'skema', 'asesor', 'asesmens'])
            ->pendingApproval()
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();

        return view('direktur.dashboard', compact(
            'stats',
            'progressStatus',
            'tukBatchStats',
            'latestBatches',
            'asesorStats',
            'skemaStats',
            'pendingSchedules',
            'asesorSkemaExists'
        ));
    }
}