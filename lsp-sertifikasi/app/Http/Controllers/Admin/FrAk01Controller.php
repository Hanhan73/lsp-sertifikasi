<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrAk01;
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

        /**
     * Admin mengembalikan FR.AK.01 ke asesi untuk diperbaiki.
     * Dipanggil via AJAX dari halaman detail asesi admin.
     */
    public function returnFrak01(Request $request, FrAk01 $frak01)
    {
        $request->validate([
            'rejection_notes' => 'required|string|min:5|max:1000',
        ], [
            'rejection_notes.required' => 'Catatan pengembalian wajib diisi.',
            'rejection_notes.min'      => 'Catatan minimal 5 karakter.',
        ]);
 
        if ($frak01->status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'FR.AK.01 tidak dalam status submitted.',
            ], 400);
        }
 
        // Reset TTD asesor jika sudah ada (karena dokumen perlu diisi ulang)
        $frak01->update([
            'status'          => 'returned',
            'rejection_notes' => $request->rejection_notes,
            'returned_at'     => now(),
            'returned_by'     => auth()->id(),
            // Reset TTD asesi agar bisa TTD ulang setelah revisi
            'ttd_asesi'         => null,
            'nama_ttd_asesi'    => null,
            'tanggal_ttd_asesi' => null,
            'submitted_at'      => null,
        ]);
 
        Log::info('[FRAK01][return] Admin returned FR.AK.01 #' . $frak01->id, [
            'by'    => auth()->id(),
            'notes' => $request->rejection_notes,
        ]);
 
        return response()->json([
            'success' => true,
            'message' => 'FR.AK.01 dikembalikan ke asesi untuk diperbaiki.',
        ]);
    }

}