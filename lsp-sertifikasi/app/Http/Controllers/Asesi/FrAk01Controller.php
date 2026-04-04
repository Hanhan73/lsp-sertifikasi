<?php

namespace App\Http\Controllers\Asesi;

use App\Http\Controllers\Controller;
use App\Models\FrAk01;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * FrAk01Controller (Asesi)
 *
 * Flow baru — asesi yang inisiasi & TTD pertama:
 *  1. Asesi buka halaman FR.AK.01 → auto-create draft jika belum ada
 *  2. Asesi TTD → status: submitted
 *  3. Asesor review & TTD → status: verified
 */
class FrAk01Controller extends Controller
{
    /**
     * Asesi melihat & mengisi FR.AK.01.
     * Draft dibuat otomatis dari data asesmen yang sudah ada.
     */
    public function showAsesi()
    {
        $asesmen = auth()->user()->asesmens()
            ->with(['skema', 'tuk', 'schedule.asesor', 'frak01'])
            ->latest()
            ->firstOrFail();

        // Auto-create draft jika belum ada
        $frak01 = $asesmen->frak01 ?? $this->createDraft($asesmen);

        return view('asesi.frak01.form', compact('asesmen', 'frak01'));
    }

    /**
     * Asesi menandatangani FR.AK.01 → status: submitted.
     */
    public function signAsesi(Request $request)
    {
        $request->validate([
            'signature'  => 'required|string',
            'nama_asesi' => 'required|string|max:255',
        ]);

        $asesmen = auth()->user()->asesmens()
            ->latest()
            ->firstOrFail();

        $frak01 = $asesmen->frak01;

        if (!$frak01) {
            return response()->json(['success' => false, 'message' => 'FR.AK.01 tidak ditemukan.'], 404);
        }

        if ($frak01->status !== 'draft' && $frak01->status !== 'returned') {
            return response()->json([
                'success' => false,
                'message' => 'FR.AK.01 sudah ditandatangani sebelumnya.',
            ], 400);
        }

        $sig = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature);

        $frak01->update([
            'status'            => 'submitted',
            'ttd_asesi'         => $sig,
            'nama_ttd_asesi'    => $request->nama_asesi,
            'tanggal_ttd_asesi' => now(),
            'submitted_at'      => now(),
        ]);

        Log::info('[FRAK01][asesi-sign] Asesi signed FR.AK.01', [
            'frak01_id'  => $frak01->id,
            'asesmen_id' => $asesmen->id,
            'user_id'    => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'FR.AK.01 berhasil ditandatangani! Menunggu verifikasi asesor.']);
    }

    /**
     * Preview / download PDF (hanya setelah verified oleh asesor).
     */
    public function asesiPdf(Request $request)
    {
        $asesmen = auth()->user()->asesmens()
            ->with(['skema', 'tuk', 'schedule.asesor', 'frak01'])
            ->whereNotNull('schedule_id')
            ->latest()
            ->firstOrFail();

        $frak01 = $asesmen->frak01;
        abort_if(!$frak01, 404, 'FR.AK.01 belum ada.');
        abort_if(!in_array($frak01->status, ['verified', 'approved']), 403, 'FR.AK.01 belum diverifikasi asesor.');

        $pdf = Pdf::loadView('pdf.frak01', [
            'frak01'  => $frak01,
            'asesmen' => $asesmen,
        ])->setPaper('A4', 'portrait');

        $filename = 'FR-AK-01_' . str_replace(' ', '_', $asesmen->full_name) . '.pdf';

        return $request->get('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

    /**
     * Auto-save checklist bukti dari asesi (AJAX).
     */
    public function saveBukti(Request $request)
    {
        $asesmen = auth()->user()->asesmens()
            ->latest()
            ->firstOrFail();

        $frak01 = $asesmen->frak01;

        if (!$frak01 || !in_array($frak01->status, ['draft', 'returned'])) {            
            return response()->json(['success' => false, 'message' => 'FR.AK.01 tidak dapat diubah.'], 403);
        }

        $frak01->update([
            'bukti_verifikasi_portofolio'     => $request->boolean('bukti_verifikasi_portofolio'),
            'bukti_hasil_review_produk'        => $request->boolean('bukti_hasil_review_produk'),
            'bukti_observasi_langsung'         => $request->boolean('bukti_observasi_langsung'),
            'bukti_hasil_kegiatan_terstruktur' => $request->boolean('bukti_hasil_kegiatan_terstruktur'),
            'bukti_pertanyaan_lisan'           => $request->boolean('bukti_pertanyaan_lisan'),
            'bukti_pertanyaan_tertulis'        => $request->boolean('bukti_pertanyaan_tertulis'),
            'bukti_pertanyaan_wawancara'       => $request->boolean('bukti_pertanyaan_wawancara'),
            'bukti_lainnya'                    => $request->boolean('bukti_lainnya'),
            'bukti_lainnya_keterangan'         => $request->input('bukti_lainnya_keterangan'),
        ]);

        return response()->json(['success' => true, 'message' => 'Checklist tersimpan.']);
    }
    // ─────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Auto-create draft FR.AK.01 dari data asesmen.
     * Tidak perlu asesor yang inisiasi lagi.
     */
    private function createDraft(\App\Models\Asesmen $asesmen): FrAk01
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