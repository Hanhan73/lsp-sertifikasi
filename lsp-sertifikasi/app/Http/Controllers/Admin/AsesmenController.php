<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * AsesmenController
 *
 * Mengelola:
 *  - Daftar semua asesi
 *  - Detail satu asesi (halaman + AJAX)
 *  - Input hasil asesmen
 *  - Generate sertifikat
 */
class AsesmenController extends Controller
{
    // =========================================================
    // DAFTAR SEMUA ASESI
    // =========================================================

    public function index()
    {
        $asesmens = Asesmen::with(['user', 'tuk', 'skema', 'payment', 'schedule'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.asesmens.index', compact('asesmens'));
    }

    // =========================================================
    // DETAIL ASESI
    // =========================================================

    /**
     * Halaman penuh detail satu asesi (termasuk APL-01 & APL-02).
     */
    public function show(Asesmen $asesmen)
    {
        $asesmen->load([
            'user',
            'tuk',
            'skema',
            'schedule.asesor',
            'schedule.tuk',
            'aplsatu.buktiKelengkapan',
            'apldua.jawabans',
        ]);

        $aplsatu = $asesmen->aplsatu;
        $apldua  = $asesmen->apldua;

        return view('admin.asesmens.show', compact('asesmen', 'aplsatu', 'apldua'));
    }

    /**
     * JSON + rendered HTML untuk modal di halaman index (AJAX).
     */
    public function detail(Asesmen $asesmen)
    {
        try {
            $asesmen->load([
                'user',
                'tuk',
                'assignedTuk',
                'assigner',
                'skema',
                'payment',
                'payments',
                'schedule',
                'certificate',
                'registrar',
                'assessorRegistrar',
                'tukVerifier',
                'assessor',
            ]);

            $html = view('admin.asesmens.partials.detail-modal', compact('asesmen'))->render();

            return response()->json(['success' => true, 'html' => $html]);

        } catch (\Exception $e) {
            Log::error('[ASESMEN][detail] ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // INPUT HASIL ASESMEN
    // =========================================================

    public function assessments()
    {
        $asesmens = Asesmen::with(['user', 'tuk', 'skema', 'schedule'])
            ->whereIn('status', ['pre_assessment_completed', 'scheduled'])
            ->get();

        return view('admin.asesmens.assessments', compact('asesmens'));
    }

    public function inputHasil(Request $request, Asesmen $asesmen)
    {
        $request->validate([
            'result'       => 'required|in:kompeten,belum_kompeten',
            'result_notes' => 'nullable|string',
        ]);

        $asesmen->update([
            'result'       => $request->result,
            'result_notes' => $request->result_notes,
            'assessed_by'  => auth()->id(),
            'assessed_at'  => now(),
            'status'       => 'assessed',
        ]);

        if ($request->result === 'kompeten') {
            $this->generateCertificate($asesmen);
        }

        return redirect()->route('admin.assessments')
            ->with('success', 'Hasil asesmen berhasil disimpan!');
    }

    // =========================================================
    // PRIVATE — SERTIFIKAT
    // =========================================================

    private function generateCertificate(Asesmen $asesmen): void
    {
        $certNumber = Certificate::generateCertificateNumber();

        $pdf = Pdf::loadView('certificates.template', [
            'asesmen'            => $asesmen,
            'certificate_number' => $certNumber,
            'issued_date'        => now(),
        ]);

        $path = 'certificates/certificate_' . $asesmen->id . '_' . time() . '.pdf';
        Storage::put('public/' . $path, $pdf->output());

        Certificate::create([
            'asesmen_id'         => $asesmen->id,
            'certificate_number' => $certNumber,
            'issued_date'        => now(),
            'valid_until'        => now()->addYears(3),
            'pdf_path'           => $path,
            'generated_by'       => auth()->id(),
        ]);

        $asesmen->update(['status' => 'certified']);
    }
}