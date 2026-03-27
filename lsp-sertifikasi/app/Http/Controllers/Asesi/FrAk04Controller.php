<?php

namespace App\Http\Controllers\Asesi;

use App\Http\Controllers\Controller;
use App\Models\FrAk04;
use App\Models\Asesmen;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FrAk04Controller extends Controller
{
    /**
     * Tampilkan form banding (dari tab di halaman jadwal).
     * Jika belum ada, buat draft baru secara otomatis.
     */
    public function showAsesi()
    {
        $asesmen = auth()->user()->asesmens()
            ->with(['skema', 'tuk', 'schedule.asesor', 'frak04'])
            ->whereNotNull('schedule_id')
            ->latest()
            ->firstOrFail();

        // Auto-create draft jika belum ada
        $frak04 = $asesmen->frak04 ?? FrAk04::create([
            'asesmen_id'          => $asesmen->id,
            'nama_asesi'          => $asesmen->full_name,
            'nama_asesor'         => $asesmen->schedule?->asesor?->nama,
            'tanggal_asesmen'     => $asesmen->schedule?->assessment_date?->translatedFormat('l, d F Y'),
            'skema_sertifikasi'   => $asesmen->skema?->name,
            'no_skema_sertifikasi' => $asesmen->skema?->nomor_skema,
            'status'              => 'draft',
        ]);

        return view('asesi.frak04.form', compact('asesmen', 'frak04'));
    }

    /**
     * Simpan jawaban + tanda tangan asesi (submit banding).
     */
    public function submitAsesi(Request $request)
    {
        $request->validate([
            'proses_banding_dijelaskan'   => 'required|in:1,0',
            'sudah_diskusi_dengan_asesor' => 'required|in:1,0',
            'melibatkan_orang_lain'       => 'required|in:1,0',
            'alasan_banding'              => 'required|string|min:10|max:2000',
            'signature'                   => 'required|string',
            'nama_asesi'                  => 'required|string|max:255',
        ]);

        $asesmen = auth()->user()->asesmens()
            ->whereNotNull('schedule_id')
            ->latest()
            ->firstOrFail();

        $frak04 = $asesmen->frak04;

        if (!$frak04) {
            return response()->json(['success' => false, 'message' => 'FR.AK.04 tidak ditemukan.'], 404);
        }

        if (!$frak04->is_editable) {
            return response()->json(['success' => false, 'message' => 'Banding sudah disubmit, tidak dapat diubah.'], 400);
        }

        $sig = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature);

        $frak04->update([
            'proses_banding_dijelaskan'   => (bool) $request->proses_banding_dijelaskan,
            'sudah_diskusi_dengan_asesor' => (bool) $request->sudah_diskusi_dengan_asesor,
            'melibatkan_orang_lain'       => (bool) $request->melibatkan_orang_lain,
            'alasan_banding'              => $request->alasan_banding,
            'ttd_asesi'                   => $sig,
            'nama_ttd_asesi'              => $request->nama_asesi,
            'tanggal_ttd_asesi'           => now(),
            'status'                      => 'submitted',
            'submitted_at'                => now(),
        ]);

        Log::info('[FRAK04][submit] Asesi submitted banding', [
            'frak04_id'  => $frak04->id,
            'asesmen_id' => $asesmen->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banding berhasil diajukan!',
        ]);
    }

    /**
     * Preview / download PDF (asesi — setelah submitted).
     */
    public function asesiPdf(Request $request)
    {
        $asesmen = auth()->user()->asesmens()
            ->with(['skema', 'tuk', 'schedule.asesor', 'frak04'])
            ->whereNotNull('schedule_id')
            ->latest()
            ->firstOrFail();

        $frak04 = $asesmen->frak04;

        abort_if(!$frak04, 404, 'FR.AK.04 belum ada');
        abort_if($frak04->status !== 'submitted', 403, 'Banding belum disubmit.');

        $pdf = Pdf::loadView('pdf.frak04', [
            'frak04'  => $frak04,
            'asesmen' => $asesmen,
        ])->setPaper('A4', 'portrait');

        $filename = 'FR-AK-04_' . str_replace(' ', '_', $asesmen->full_name) . '.pdf';

        return $request->get('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }
}