<?php

namespace App\Http\Controllers\ManajerSertifikasi;

use App\Http\Controllers\Controller;
use App\Models\Portofolio;
use App\Models\Schedule;
use App\Models\SoalObservasi;
use App\Models\SoalTeori;
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

    public function distribusi(): \Illuminate\View\View
    {
        $jadwalBelumTeori = Schedule::approved()->whereDoesntHave('distribusiSoalTeori')->count();
        $jadwalLengkap    = Schedule::approved()
            ->whereHas('distribusiSoalObservasi')
            ->whereHas('distribusiSoalTeori')
            ->count();
 
        $schedules = Schedule::with([
            'skema', 'tuk',
            'distribusiSoalObservasi',
            'distribusiSoalTeori',
            'distribusiPortofolio',
        ])
        ->approved()
        ->withCount('asesmens')
        ->latest('assessment_date')
        ->paginate(15);
 
        return view('manajer-sertifikasi.distribusi', compact(
            'schedules',
            'jadwalBelumTeori',
            'jadwalLengkap',
        ));
    }
}