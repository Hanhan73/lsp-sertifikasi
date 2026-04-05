<?php

namespace App\Http\Controllers\ManajerSertifikasi;

use App\Http\Controllers\Controller;
use App\Models\Portofolio;
use App\Models\Schedule;
use App\Models\SoalObservasi;
use App\Models\SoalTeori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $totalJadwal      = Schedule::approved()->count();
        $jadwalBelumTeori = Schedule::approved()->whereDoesntHave('distribusiSoalTeori')->count();
        $jadwalLengkap    = Schedule::approved()
            ->whereHas('distribusiSoalObservasi')
            ->whereHas('distribusiSoalTeori')
            ->count();
        $totalBankSoal = SoalTeori::count();
 
        $bankSoalPerSkema = SoalTeori::select('soal_teori.skema_id', DB::raw('count(*) as total'))
            ->join('skemas', 'skemas.id', '=', 'soal_teori.skema_id')
            ->addSelect('skemas.name as skema_name', 'skemas.code as skema_code')
            ->groupBy('soal_teori.skema_id', 'skemas.name', 'skemas.code')
            ->orderByDesc('total')
            ->get();
 
        $jadwalMendatang = Schedule::with(['skema', 'tuk', 'distribusiSoalTeori'])
            ->approved()
            ->upcoming()
            ->withCount('asesmens')
            ->take(6)
            ->get();
 
        return view('manajer-sertifikasi.dashboard', compact(
            'totalJadwal', 'jadwalBelumTeori', 'jadwalLengkap',
            'totalBankSoal', 'bankSoalPerSkema', 'jadwalMendatang',
        ));
    }

    public function distribusi(Request $request): \Illuminate\View\View
    {
        $jadwalBelumTeori = Schedule::approved()->whereDoesntHave('distribusiSoalTeori')->count();
        $jadwalLengkap    = Schedule::approved()
            ->whereHas('distribusiSoalObservasi')
            ->whereHas('distribusiSoalTeori')
            ->count();

        $query = Schedule::with([
            'skema', 'tuk',
            'distribusiSoalObservasi',
            'distribusiSoalTeori',
            'distribusiPortofolio',
        ])
        ->approved()
        ->withCount('asesmens');

        // Filter: pencarian skema / tuk
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('skema', fn($s) => $s->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('tuk', fn($t) => $t->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter: status jadwal (selesai / mendatang)
        $filterStatus = $request->input('status');
        if ($filterStatus === 'selesai') {
            $query->where('assessment_date', '<', now()->toDateString());
        } elseif ($filterStatus === 'mendatang') {
            $query->where('assessment_date', '>=', now()->toDateString());
        }

        // Sorting
        $sortBy  = $request->input('sort', 'date_desc');
        match ($sortBy) {
            'date_asc'   => $query->orderBy('assessment_date', 'asc'),
            'skema_asc'  => $query->join('skemas', 'skemas.id', '=', 'schedules.skema_id')->orderBy('skemas.name', 'asc'),
            'skema_desc' => $query->join('skemas', 'skemas.id', '=', 'schedules.skema_id')->orderBy('skemas.name', 'desc'),
            default      => $query->orderBy('assessment_date', 'desc'),
        };

        $schedules = $query->paginate(15)->withQueryString();

        return view('manajer-sertifikasi.distribusi', compact(
            'schedules',
            'jadwalBelumTeori',
            'jadwalLengkap',
            'filterStatus',
            'sortBy',
        ));
    }
}