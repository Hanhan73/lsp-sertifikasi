<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ArsipSertifikatController extends Controller
{
    public function index()
    {
        $asesmens = Asesmen::with(['tuk', 'skema'])
            ->where('status', 'certificate_distributed')
            ->orderByDesc('distributed_at')
            ->get();

        $batches = $asesmens->where('is_collective', true)
            ->groupBy('collective_batch_id')
            ->map(function ($members) {
                $first = $members->first();

                return (object) [
                    'batch_id'       => $first->collective_batch_id,
                    'tuk'            => $first->tuk,
                    'skema'          => $first->skema,
                    'total'          => $members->count(),
                    'uploaded_count' => $members->filter(fn ($m) => $m->hasUploadedPhysicalCertificate())->count(),
                    'distributed_at' => $members->max('distributed_at'),
                ];
            })
            ->values();

        $mandiri = $asesmens->where('is_collective', false)->values();

        return view('admin.arsip-sertifikat.index', compact('batches', 'mandiri'));
    }

    /**
     * Detail lengkap satu batch — dipanggil via AJAX untuk mengisi modal.
     */
    public function batchDetail(string $batchId)
    {
        $members = Asesmen::with(['tuk', 'skema'])
            ->where('collective_batch_id', $batchId)
            ->where('status', 'certificate_distributed')
            ->orderBy('full_name')
            ->get();

        if ($members->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Batch tidak ditemukan.'], 404);
        }

        $first = $members->first();

        $peserta = $members->map(fn ($m) => [
            'id'                => $m->id,
            'full_name'         => $m->full_name,
            'nik'               => $m->nik,
            'institution'       => $m->institution,
            'uploaded'          => $m->hasUploadedPhysicalCertificate(),
            'cert_number'       => $m->physical_certificate_number,
            'adm_number'        => $m->physical_certificate_adm_number,
            'uploaded_at'       => $m->physical_certificate_uploaded_at?->translatedFormat('d M Y H:i'),
            'download_url'      => $m->hasUploadedPhysicalCertificate()
                ? route('admin.arsip-sertifikat.download', $m->id)
                : null,
        ]);

        return response()->json([
            'success' => true,
            'batch'   => [
                'batch_id'       => $batchId,
                'tuk'            => $first->tuk?->name,
                'skema'          => $first->skema?->name,
                'total'          => $members->count(),
                'uploaded_count' => $peserta->where('uploaded', true)->count(),
            ],
            'peserta' => $peserta->values(),
        ]);
    }

    /**
     * Download 1 file sertifikat fisik milik satu asesi.
     */
    public function download(Asesmen $asesmen)
    {
        abort_unless($asesmen->hasUploadedPhysicalCertificate(), 404, 'Sertifikat belum diupload.');
        abort_unless(Storage::disk('public_html')->exists($asesmen->physical_certificate_path), 404, 'File tidak ditemukan.');

        $ext = pathinfo($asesmen->physical_certificate_path, PATHINFO_EXTENSION);
        $filename = 'Sertifikat_' . str_replace(' ', '_', $asesmen->full_name) . '.' . $ext;

        return response()->streamDownload(function () use ($asesmen) {
            echo Storage::disk('public_html')->get($asesmen->physical_certificate_path);
        }, $filename);
    }

    /**
     * Download semua file sertifikat fisik dalam 1 batch sebagai ZIP.
     */
    public function downloadBatchZip(string $batchId)
    {
        $members = Asesmen::where('collective_batch_id', $batchId)
            ->where('status', 'certificate_distributed')
            ->whereNotNull('physical_certificate_path')
            ->get();

        abort_if($members->isEmpty(), 404, 'Tidak ada sertifikat yang bisa diunduh untuk batch ini.');

        $zipFileName = 'Sertifikat_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $batchId) . '.zip';
        $tmpPath = storage_path('app/tmp/' . uniqid('sertifikat_', true) . '.zip');

        if (!is_dir(dirname($tmpPath))) {
            mkdir(dirname($tmpPath), 0755, true);
        }

        $zip = new ZipArchive();
        $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $added = 0;
        foreach ($members as $m) {
            if (!Storage::disk('public_html')->exists($m->physical_certificate_path)) {
                continue;
            }

            $ext = pathinfo($m->physical_certificate_path, PATHINFO_EXTENSION);
            $safeName = preg_replace('/[^A-Za-z0-9\-]/', '_', $m->full_name);
            $entryName = $safeName . '_' . $m->id . '.' . $ext;

            $zip->addFromString($entryName, Storage::disk('public_html')->get($m->physical_certificate_path));
            $added++;
        }

        $zip->close();

        abort_if($added === 0, 404, 'Tidak ada file valid yang ditemukan untuk batch ini.');

        return response()->download($tmpPath, $zipFileName)->deleteFileAfterSend(true);
    }
}