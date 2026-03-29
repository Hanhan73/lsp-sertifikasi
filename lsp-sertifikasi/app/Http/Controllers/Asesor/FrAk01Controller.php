<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\FrAk01;
use App\Models\Asesmen;
use App\Models\Schedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * FrAk01Controller (Asesor)
 *
 * Flow baru:
 *  1. Asesi membuat & menandatangani FR.AK.01 → status: submitted
 *  2. Asesor mereview dan menandatangani → status: verified
 *
 * Asesor TIDAK lagi membuat/menginisiasi FR.AK.01.
 */
class FrAk01Controller extends Controller
{
    /**
     * Tampilkan FR.AK.01 untuk di-review dan ditandatangani asesor.
     * FR.AK.01 sudah dibuat dan ditandatangani oleh asesi sebelumnya.
     */
    public function show(Schedule $schedule, Asesmen $asesmen)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403, 'Anda tidak ditugaskan ke jadwal ini.');
        abort_if($asesmen->schedule_id !== $schedule->id, 403, 'Asesi ini tidak dalam jadwal Anda.');

        $asesmen->load(['user', 'skema', 'tuk', 'schedule.asesor']);

        $frak01 = $asesmen->frak01;

        // FR.AK.01 harus sudah ada (dibuat oleh asesi)
        if (!$frak01) {
            return view('asesor.frak01.waiting', compact('schedule', 'asesmen', 'asesor'));
        }

        return view('asesor.frak01.form', compact('schedule', 'asesmen', 'frak01', 'asesor'));
    }

    /**
     * Asesor menandatangani FR.AK.01 setelah asesi TTD lebih dulu.
     */
    public function signAsesor(Request $request, Schedule $schedule, Asesmen $asesmen)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);
        abort_if($asesmen->schedule_id !== $schedule->id, 403);

        $request->validate([
            'signature'   => 'required|string',
            'nama_asesor' => 'required|string|max:255',
        ]);

        $frak01 = $asesmen->frak01;

        if (!$frak01) {
            return response()->json([
                'success' => false,
                'message' => 'FR.AK.01 belum dibuat oleh asesi.',
            ], 404);
        }

        // Asesor hanya bisa TTD jika asesi sudah TTD (status: submitted)
        if ($frak01->status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => $frak01->status === 'draft'
                    ? 'Asesi belum menandatangani FR.AK.01.'
                    : 'FR.AK.01 sudah diverifikasi sebelumnya.',
            ], 400);
        }

        $sig = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature);

        $frak01->update([
            'status'              => 'verified',
            'ttd_asesor'          => $sig,
            'nama_ttd_asesor'     => $request->nama_asesor,
            'tanggal_ttd_asesor'  => now(),
            'verified_by'         => auth()->id(),
            'verified_at'         => now(),
        ]);

        Log::info('[FRAK01][asesor-sign] Asesor signed FR.AK.01', [
            'frak01_id'  => $frak01->id,
            'asesmen_id' => $asesmen->id,
            'asesor_id'  => $asesor->id,
        ]);

        return response()->json(['success' => true, 'message' => 'FR.AK.01 berhasil diverifikasi!']);
    }

    /**
     * Preview / download PDF FR.AK.01.
     */
    public function previewPdf(Schedule $schedule, Asesmen $asesmen)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);
        abort_if($asesmen->schedule_id !== $schedule->id, 403);

        $frak01 = $asesmen->frak01;
        abort_if(!$frak01, 404, 'FR.AK.01 belum ada.');

        $asesmen->load(['skema', 'tuk', 'schedule.asesor']);

        $pdf = Pdf::loadView('pdf.frak01', [
            'frak01'  => $frak01,
            'asesmen' => $asesmen,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('FR-AK-01_' . str_replace(' ', '_', $asesmen->full_name) . '.pdf');
    }
}