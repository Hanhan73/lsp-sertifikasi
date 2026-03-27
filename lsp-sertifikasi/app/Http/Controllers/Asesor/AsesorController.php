<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Asesmen;
use App\Models\AplDua;
use App\Models\AplSatu;
use Illuminate\Support\Facades\Log;

class AsesorController extends Controller
{
    /**
     * Dashboard asesor
     */
    public function dashboard()
    {
        $asesor = auth()->user()->asesor;

        $schedules = Schedule::with(['tuk', 'skema', 'asesmens'])
            ->where('asesor_id', $asesor->id)
            ->orderBy('assessment_date', 'asc')
            ->get();

        $stats = [
            'upcoming'   => $schedules->filter(fn($s) => $s->assessment_date->isFuture())->count(),
            'today'      => $schedules->filter(fn($s) => $s->assessment_date->isToday())->count(),
            'past'       => $schedules->filter(fn($s) => $s->assessment_date->isPast())->count(),
            'total_asesi' => $schedules->sum(fn($s) => $s->asesmens->count()),
        ];

        $todaySchedules = $schedules->filter(fn($s) => $s->assessment_date->isToday());
        $upcomingSchedules = $schedules->filter(fn($s) => $s->assessment_date->isFuture())->take(5);

        return view('asesor.dashboard', compact('stats', 'todaySchedules', 'upcomingSchedules', 'asesor'));
    }

    /**
     * Daftar semua jadwal asesmen asesor ini
     */
    public function schedule(Request $request)
    {
        $asesor = auth()->user()->asesor;

        $query = Schedule::with(['tuk', 'skema', 'asesmens.aplsatu', 'asesmens.apldua'])
            ->where('asesor_id', $asesor->id);

        // Filter
        $filter = $request->input('filter', 'upcoming');
        if ($filter === 'upcoming') {
            $query->where('assessment_date', '>=', now()->toDateString());
        } elseif ($filter === 'past') {
            $query->where('assessment_date', '<', now()->toDateString());
        } elseif ($filter === 'today') {
            $query->whereDate('assessment_date', now()->toDateString());
        }

        $schedules = $query->orderBy('assessment_date', $filter === 'past' ? 'desc' : 'asc')->get();

        return view('asesor.schedule.index', compact('schedules', 'filter'));
    }

    /**
     * Detail jadwal — daftar asesi di jadwal ini
     */
    public function scheduleDetail(Schedule $schedule)
    {
        // Pastikan schedule ini milik asesor yang login
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);

        $schedule->load([
            'tuk',
            'skema',
            'asesmens.user',
            'asesmens.aplsatu',
            'asesmens.apldua.jawabans',
        ]);

        return view('asesor.schedule.detail', compact('schedule', 'asesor'));
    }

    /**
     * Detail asesi — semua dokumen APL-01, APL-02, dll
     */
    public function asesiDetail(Schedule $schedule, Asesmen $asesmen)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);
        abort_if($asesmen->schedule_id !== $schedule->id, 403);

        $asesmen->load([
            'user',
            'tuk',
            'skema.unitKompetensis.elemens.kuks',
            'aplsatu.buktiKelengkapan',
            'apldua.jawabans.elemen',
            'frak01',
            'certificate',
        ]);

        return view('asesor.asesi.detail', compact('schedule', 'asesmen', 'asesor'));
    }

    /**
     * Verifikasi APL-02 — asesor tanda tangan dan beri rekomendasi
     */
    public function verifyApl02(Request $request, Schedule $schedule, Asesmen $asesmen)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);
        abort_if($asesmen->schedule_id !== $schedule->id, 403);

        $request->validate([
            'rekomendasi'  => 'required|in:lanjut,tidak_lanjut',
            'catatan'      => 'nullable|string|max:1000',
            'signature'    => 'required|string',
            'nama_asesor'  => 'required|string|max:255',
        ]);

        $apldua = $asesmen->apldua;

        if (!$apldua || $apldua->status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'APL-02 belum disubmit oleh asesi atau sudah diverifikasi.',
            ], 400);
        }

        $signature = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature);

        $apldua->update([
            'status'             => 'verified',
            'rekomendasi_asesor' => $request->rekomendasi,
            'catatan_asesor'     => $request->catatan,
            'ttd_asesor'         => $signature,
            'nama_ttd_asesor'    => $request->nama_asesor,
            'tanggal_ttd_asesor' => now(),
            'verified_by'        => auth()->id(),
            'verified_at'        => now(),
        ]);

        Log::info('[APL02-VERIFY] Asesor verified APL-02', [
            'apldua_id'    => $apldua->id,
            'asesmen_id'   => $asesmen->id,
            'asesor_id'    => $asesor->id,
            'rekomendasi'  => $request->rekomendasi,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'APL-02 berhasil diverifikasi!',
        ]);
    }

    /**
     * Preview PDF APL-01 (read-only untuk asesor)
     */
    public function previewApl01(Schedule $schedule, Asesmen $asesmen)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);
        abort_if($asesmen->schedule_id !== $schedule->id, 403);

        $aplsatu = $asesmen->aplsatu()->with('buktiKelengkapan')->first();
        abort_if(!$aplsatu, 404, 'APL-01 belum ada');

        $asesmen->load(['skema.unitKompetensis', 'tuk']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.aplsatu', [
            'aplsatu' => $aplsatu,
            'asesmen' => $asesmen,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('APL-01_' . str_replace(' ', '_', $asesmen->full_name) . '.pdf');
    }

    /**
     * Halaman SK Asesor
     */
    public function documentSk()
    {
        $asesor = auth()->user()->asesor;
        return view('asesor.document.sk', compact('asesor'));
    }

    /**
     * Preview PDF APL-02 (asesor — setelah verified)
     */
    public function previewApl02(Schedule $schedule, Asesmen $asesmen)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);
        abort_if($asesmen->schedule_id !== $schedule->id, 403);

        $apldua = $asesmen->apldua()->with('jawabans')->first();
        abort_if(!$apldua, 404, 'APL-02 belum ada');
        abort_if(!in_array($apldua->status, ['verified', 'approved']), 403, 'APL-02 belum diverifikasi');

        $asesmen->load(['skema.unitKompetensis.elemens.kuks', 'schedule.asesor']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.apldua', [
            'apldua'      => $apldua,
            'asesmen'     => $asesmen,
            'asesor_no_reg' => $asesor->no_reg_met,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('APL-02_' . str_replace(' ', '_', $asesmen->full_name) . '.pdf');
    }
}