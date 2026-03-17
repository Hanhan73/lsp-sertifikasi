<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\AplSatu;
use App\Models\AplSatuBukti;
use App\Models\AplDua;
use App\Models\Schedule;
use App\Models\Tuk;
use App\Services\Apl01Service;
use App\Services\GoogleDriveService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * AplController
 *
 * Mengelola semua hal terkait dokumen APL-01 dan APL-02 dari sisi admin:
 *  - Daftar per jadwal (index)
 *  - Detail & verifikasi APL-01
 *  - Update status bukti kelengkapan (AJAX)
 *  - Generate PDF APL-01 & APL-02
 */
class AplController extends Controller
{
    // =========================================================
    // INDEX — Jadwal & dokumen per asesi
    // =========================================================

    public function index()
    {
        // Ambil semua jadwal beserta asesi, skema, tuk, asesor, dan APL-01 mereka
        $schedules = Schedule::with([
            'tuk',
            'skema',
            'asesor',
            'asesmens.aplsatu',         // APL-01 per asesi
            'asesmens.apldua',
            'asesmens.user',
        ])
        ->orderBy('assessment_date', 'desc')
        ->get();

        // Stats untuk kartu di atas
        $pendingApl01Count  = AplSatu::where('status', 'submitted')->count();
        $verifiedApl01Count = AplSatu::whereIn('status', ['verified', 'approved'])->count();
        $totalAsesiScheduled = Asesmen::whereNotNull('schedule_id')->count();

        // Dropdown TUK untuk filter
        $tuks = Tuk::orderBy('name')->get();

        return view('admin.apl.index', compact(
            'schedules',
            'pendingApl01Count',
            'verifiedApl01Count',
            'totalAsesiScheduled',
            'tuks'
        ));
    }

    // =========================================================
    // APL-01 DETAIL & VERIFIKASI
    // =========================================================

    public function showApl01(AplSatu $aplsatu)
    {
        $aplsatu->load([
            'asesmen.user',
            'asesmen.skema.unitKompetensis',
            'buktiKelengkapan',
            'verifier',
        ]);

        return view('admin.apl.show', compact('aplsatu'));
    }

    /**
     * Update status satu item bukti kelengkapan (AJAX).
     */
    public function updateBuktiStatus(Request $request, AplSatuBukti $bukti)
    {
        $request->validate([
            'status'  => 'required|in:Ada Memenuhi Syarat,Ada Tidak Memenuhi Syarat,Tidak Ada',
            'catatan' => 'nullable|string|max:500',
        ]);

        $bukti->update([
            'status'      => $request->status,
            'catatan'     => $request->catatan,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Status bukti diupdate!']);
    }

    /**
     * Verify APL-01 dengan tanda tangan admin (AJAX).
     */
    public function verifyApl01(Request $request, AplSatu $aplsatu)
    {
        $request->validate([
            'signature'  => 'required|string',
            'nama_admin' => 'required|string|max:255',
        ]);

        // Strip base64 data-URI prefix jika ada
        $signature = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature);

        $service = new Apl01Service();

        if ($service->verifyApl01($aplsatu, $signature, $request->nama_admin)) {
            return response()->json([
                'success' => true,
                'message' => 'APL-01 berhasil diverifikasi!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal verifikasi APL-01.',
        ], 500);
    }

    /**
     * Kembalikan APL-01 ke asesi dengan catatan (AJAX).
     */
    public function returnApl01(Request $request, AplSatu $aplsatu)
    {
        $request->validate([
            'catatan' => 'required|string|max:1000',
        ]);

        if ($aplsatu->status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'APL-01 tidak dalam status submitted.',
            ], 400);
        }

        $aplsatu->update([
            'status'              => 'returned',
            'verified_by'         => auth()->id(),
            'verified_at'         => now(),
        ]);

        Log::info('[APL01][return] APL-01 #' . $aplsatu->id . ' dikembalikan oleh admin #' . auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'APL-01 dikembalikan ke asesi.',
        ]);
    }

    // =========================================================
    // PDF
    // =========================================================

    public function pdfApl01(Request $request, AplSatu $aplsatu)
    {
        $aplsatu->load([
            'buktiKelengkapan',
            'asesmen.skema.unitKompetensis',
            'asesmen.tuk',
        ]);

        abort_unless($aplsatu->asesmen, 404, 'Asesmen tidak ditemukan');

        $pdf = Pdf::loadView('pdf.aplsatu', [
            'aplsatu' => $aplsatu,
            'asesmen' => $aplsatu->asesmen,
        ])->setPaper('A4', 'portrait');

        $filename = 'APL-01_' . str_replace(' ', '_', $aplsatu->nama_lengkap) . '.pdf';

        return $request->boolean('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

    public function pdfApl02(Request $request, AplDua $apldua)
    {
        $apldua->load([
            'asesmen.skema.unitKompetensis.elemens.kuks',
            'asesmen.schedule.asesor',
            'jawabans',
        ]);

        abort_unless($apldua->asesmen, 404, 'Asesmen tidak ditemukan');

        $pdf = Pdf::loadView('pdf.apldua', [
            'apldua'  => $apldua,
            'asesmen' => $apldua->asesmen,
        ])->setPaper('A4', 'portrait');

        $filename = 'APL-02_' . str_replace(' ', '_', $apldua->asesmen->full_name) . '.pdf';

        return $request->boolean('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

    // =========================================================
    // UPLOAD BUKTI (opsional — jika admin perlu upload sendiri)
    // =========================================================

    public function uploadBukti(Request $request, AplSatuBukti $bukti)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $driveService = new GoogleDriveService();
        $result = $driveService->uploadFile(
            $request->file('file'),
            'APL-01-' . $bukti->aplsatu->asesmen_id
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload: ' . ($result['error'] ?? 'Unknown error'),
            ], 500);
        }

        $bukti->update([
            'gdrive_file_id'    => $result['gdrive_file_id'],
            'gdrive_file_url'   => $result['gdrive_file_url'],
            'original_filename' => $result['original_filename'],
            'uploaded_by'       => auth()->id(),
            'uploaded_at'       => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File berhasil diupload!',
            'url'     => $result['gdrive_file_url'],
        ]);
    }
}