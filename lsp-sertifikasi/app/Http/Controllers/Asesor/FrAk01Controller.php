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
    // ASESOR — membuat / membuka form untuk asesi tertentu
    // ─────────────────────────────────────────────────────────────

    /**
     * Tampilkan form FR.AK.01 untuk pasangan schedule+asesmen.
     * Jika belum ada, buat baru dengan data pre-filled.
     */
    public function show(Schedule $schedule, Asesmen $asesmen)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);
        abort_if($asesmen->schedule_id !== $schedule->id, 403);

        $asesmen->load(['user', 'skema', 'tuk', 'schedule.asesor']);

        $frak01 = $asesmen->frak01 ?? $this->createDraft($asesmen);

        return view('asesor.frak01.form', compact('schedule', 'asesmen', 'frak01', 'asesor'));
    }

    public function saveBukti(Request $request, Schedule $schedule, Asesmen $asesmen): \Illuminate\Http\JsonResponse
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);
        abort_if($asesmen->schedule_id !== $schedule->id, 403);
 
        // Buat draft jika belum ada
        $frak01 = $asesmen->frak01 ?? $this->createDraft($asesmen);
 
        if (!in_array($frak01->status, ['draft'])) {
            return response()->json([
                'success' => false,
                'message' => 'FR.AK.01 sudah tidak dapat diubah (status: ' . $frak01->status . ').',
            ], 403);
        }
 
        $frak01->update([
            'bukti_verifikasi_portofolio'       => $request->boolean('bukti_verifikasi_portofolio'),
            'bukti_observasi_langsung'          => $request->boolean('bukti_observasi_langsung'),
            'bukti_pertanyaan_lisan'            => $request->boolean('bukti_pertanyaan_lisan'),
            'bukti_lainnya'                     => $request->boolean('bukti_lainnya'),
            'bukti_lainnya_keterangan'          => $request->input('bukti_lainnya_keterangan'),
            'bukti_hasil_review_produk'         => $request->boolean('bukti_hasil_review_produk'),
            'bukti_hasil_kegiatan_terstruktur'  => $request->boolean('bukti_hasil_kegiatan_terstruktur'),
            'bukti_pertanyaan_tertulis'         => $request->boolean('bukti_pertanyaan_tertulis'),
            'bukti_pertanyaan_wawancara'        => $request->boolean('bukti_pertanyaan_wawancara'),
        ]);
 
        Log::info('[FRAK01][save-bukti] Bukti saved', [
            'frak01_id'  => $frak01->id,
            'asesmen_id' => $asesmen->id,
            'asesor_id'  => $asesor->id,
        ]);
 
        return response()->json(['success' => true, 'message' => 'FR.AK.01 berhasil disimpan.']);
    }

    /**
     * Asesor menandatangani FR.AK.01 (setelah asesi TTD).
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

        if (!$frak01 || $frak01->status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'FR.AK.01 belum disubmit oleh asesi atau sudah diverifikasi.',
            ], 400);
        }

        $sig = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature);

        $frak01->update([
            'status'           => 'verified',
            'ttd_asesor'       => $sig,
            'nama_ttd_asesor'  => $request->nama_asesor,
            'tanggal_ttd_asesor' => now(),
            'verified_by'      => auth()->id(),
            'verified_at'      => now(),
        ]);

        Log::info('[FRAK01][asesor-sign] Asesor signed FR.AK.01', [
            'frak01_id'  => $frak01->id,
            'asesmen_id' => $asesmen->id,
            'asesor_id'  => $asesor->id,
        ]);

        return response()->json(['success' => true, 'message' => 'FR.AK.01 berhasil ditandatangani asesor!']);
    }

    /**
     * Preview / download PDF FR.AK.01 (asesor).
     */
    public function previewPdf(Schedule $schedule, Asesmen $asesmen)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);
        abort_if($asesmen->schedule_id !== $schedule->id, 403);

        $frak01 = $asesmen->frak01;
        abort_if(!$frak01, 404, 'FR.AK.01 belum dibuat');

        $asesmen->load(['skema', 'tuk', 'schedule.asesor']);

        $pdf = Pdf::loadView('pdf.frak01', [
            'frak01'  => $frak01,
            'asesmen' => $asesmen,
        ])->setPaper('A4', 'portrait');

        $filename = 'FR-AK-01_' . str_replace(' ', '_', $asesmen->full_name) . '.pdf';

        return $pdf->stream($filename);
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    private function createDraft(Asesmen $asesmen): FrAk01
    {
        $asesmen->loadMissing(['skema', 'tuk', 'schedule.asesor']);

        return FrAk01::create([
            'asesmen_id'   => $asesmen->id,
            'skema_judul'  => $asesmen->skema?->name,
            'skema_nomor'  => $asesmen->skema?->nomor_skema ?? $asesmen->skema?->code,
            'tuk_nama'     => $asesmen->tuk?->name,
            'waktu_asesmen' => $asesmen->schedule?->start_time
                ? $asesmen->schedule->start_time . ($asesmen->schedule->end_time ? ' – ' . $asesmen->schedule->end_time : '')
                : null,
            'hari_tanggal' => $asesmen->schedule?->assessment_date?->translatedFormat('l, d F Y'),
            'nama_asesor'  => $asesmen->schedule?->asesor?->nama,
            'nama_asesi'   => $asesmen->full_name,
            'status'       => 'draft',
        ]);
    }
}