<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrAk01;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;


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
    // ADMIN — lihat PDF
    // ─────────────────────────────────────────────────────────────

    public function adminPdf(Request $request, FrAk01 $frak01)
    {
        $frak01->load(['asesmen.skema', 'asesmen.tuk', 'asesmen.schedule.asesor']);

        $pdf = Pdf::loadView('pdf.frak01', [
            'frak01'  => $frak01,
            'asesmen' => $frak01->asesmen,
        ])->setPaper('A4', 'portrait');

        $filename = 'FR-AK-01_' . str_replace(' ', '_', $frak01->asesmen->full_name) . '.pdf';

        return $request->boolean('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

}