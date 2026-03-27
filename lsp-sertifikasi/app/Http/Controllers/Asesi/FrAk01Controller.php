<?php

namespace App\Http\Controllers\Asesi;

use App\Http\Controllers\Controller;
use App\Models\FrAk01;
use App\Models\Asesmen;
use App\Models\Schedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * FrAk01Controller
 *
 * Mengelola FR.AK.01 - Persetujuan Asesmen dan Kerahasiaan
 *
 * Flow:
 *  1. Asesor membuka form → pre-filled data skema/jadwal
 *  2. Asesi tanda tangan → status: submitted
 *  3. Asesor tanda tangan → status: verified
 *  4. Admin / semua pihak bisa lihat PDF
 */
class FrAk01Controller extends Controller
{
    
    // ─────────────────────────────────────────────────────────────
    // ASESI — tanda tangan
    // ─────────────────────────────────────────────────────────────

    /**
     * Asesi melihat form FR.AK.01 (read-only kecuali TTD).
     */
    public function showAsesi()
    {
        $asesmen = auth()->user()->asesmens()
            ->with(['skema', 'tuk', 'schedule.asesor', 'frak01'])
            ->whereNotNull('schedule_id')
            ->latest()
            ->firstOrFail();

        $frak01 = $asesmen->frak01;
        abort_if(!$frak01, 404, 'FR.AK.01 belum dibuat oleh asesor');

        return view('asesi.frak01.form', compact('asesmen', 'frak01'));
    }

    /**
     * Asesi menandatangani FR.AK.01.
     */
    public function signAsesi(Request $request)
    {
        $request->validate([
            'signature'  => 'required|string',
            'nama_asesi' => 'required|string|max:255',
        ]);

        $asesmen = auth()->user()->asesmens()
            ->whereNotNull('schedule_id')
            ->latest()
            ->firstOrFail();

        $frak01 = $asesmen->frak01;

        if (!$frak01) {
            return response()->json(['success' => false, 'message' => 'FR.AK.01 belum dibuat.'], 404);
        }

        if (!$frak01->is_editable) {
            return response()->json(['success' => false, 'message' => 'FR.AK.01 sudah ditandatangani.'], 400);
        }

        $sig = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature);

        $frak01->update([
            'status'           => 'submitted',
            'ttd_asesi'        => $sig,
            'nama_ttd_asesi'   => $request->nama_asesi,
            'tanggal_ttd_asesi' => now(),
            'submitted_at'     => now(),
        ]);

        Log::info('[FRAK01][asesi-sign] Asesi signed FR.AK.01', [
            'frak01_id'  => $frak01->id,
            'asesmen_id' => $asesmen->id,
        ]);

        return response()->json(['success' => true, 'message' => 'FR.AK.01 berhasil ditandatangani!']);
    }

    /**
     * Preview / download PDF (asesi — setelah verified).
     */
    public function asesiPdf(Request $request)
    {
        $asesmen = auth()->user()->asesmens()
            ->with(['skema', 'tuk', 'schedule.asesor', 'frak01'])
            ->whereNotNull('schedule_id')
            ->latest()
            ->firstOrFail();

        $frak01 = $asesmen->frak01;

        abort_if(!$frak01, 404, 'FR.AK.01 belum ada');
        abort_if(!in_array($frak01->status, ['verified', 'approved']), 403, 'FR.AK.01 belum diverifikasi.');

        $pdf = Pdf::loadView('pdf.frak01', [
            'frak01'  => $frak01,
            'asesmen' => $asesmen,
        ])->setPaper('A4', 'portrait');

        $filename = 'FR-AK-01_' . str_replace(' ', '_', $asesmen->full_name) . '.pdf';

        return $request->get('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

   
}