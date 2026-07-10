<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\AsesorDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AsesorDocumentController extends Controller
{
    private function rules(): array
    {
        return [
            'jenis_dokumen' => 'required|in:' . implode(',', array_keys(AsesorDocument::JENIS_LABELS)),
            'file'          => 'required|file|mimes:pdf|max:5120',
        ];
    }

    private function upload(Request $request, Asesor $asesor, ?int $uploadedBy): string
    {
        $request->validate($this->rules(), [
            'jenis_dokumen.required' => 'Jenis dokumen tidak valid.',
            'file.required'          => 'File wajib dipilih.',
            'file.mimes'             => 'File harus berformat PDF.',
            'file.max'               => 'Ukuran file maksimal 5 MB.',
        ]);

        $jenis = $request->jenis_dokumen;
        $label = AsesorDocument::JENIS_LABELS[$jenis];

        $existing = $asesor->documents()->where('jenis_dokumen', $jenis)->first();
        if ($existing) {
            Storage::disk('private')->delete($existing->file_path);
        }

        $file     = $request->file('file');
        $filename = $jenis . '_' . str_replace(' ', '_', $asesor->nama) . '_' . now()->format('YmdHis') . '.pdf';
        $path     = $file->storeAs("asesors/documents/{$asesor->id}", $filename, 'private');

        AsesorDocument::updateOrCreate(
            ['asesor_id' => $asesor->id, 'jenis_dokumen' => $jenis],
            [
                'file_path'   => $path,
                'file_name'   => $file->getClientOriginalName(),
                'file_size'   => $file->getSize(),
                'uploaded_by' => $uploadedBy,
            ]
        );

        Log::info('[ASESOR-DOKUMEN] Upload', [
            'asesor_id'   => $asesor->id,
            'jenis'       => $jenis,
            'uploaded_by' => $uploadedBy,
        ]);

        return $label;
    }

    private function download(AsesorDocument $document)
    {
        abort_unless(Storage::disk('private')->exists($document->file_path), 404, 'File tidak ditemukan.');

        return response()->streamDownload(function () use ($document) {
            echo Storage::disk('private')->get($document->file_path);
        }, $document->file_name, ['Content-Type' => 'application/pdf']);
    }

    private function delete(AsesorDocument $document): void
    {
        Storage::disk('private')->delete($document->file_path);
        $document->delete();
    }

    // ── ADMIN ──

    public function storeAdmin(Request $request, Asesor $asesor)
    {
        $label = $this->upload($request, $asesor, auth()->id());
        return back()->with('success', "{$label} berhasil diupload.");
    }

    public function downloadAdmin(Asesor $asesor, AsesorDocument $document)
    {
        abort_if($document->asesor_id !== $asesor->id, 403);
        return $this->download($document);
    }

    public function destroyAdmin(Asesor $asesor, AsesorDocument $document)
    {
        abort_if($document->asesor_id !== $asesor->id, 403);
        $this->delete($document);
        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    // ── ASESOR (self-service) ──

    public function storeSelf(Request $request)
    {
        $asesor = auth()->user()->asesor;
        abort_unless($asesor, 403);

        $label = $this->upload($request, $asesor, auth()->id());
        return back()->with('success', "{$label} berhasil diupload.");
    }

    public function downloadSelf(AsesorDocument $document)
    {
        $asesor = auth()->user()->asesor;
        abort_if(!$asesor || $document->asesor_id !== $asesor->id, 403);
        return $this->download($document);
    }

    public function destroySelf(AsesorDocument $document)
    {
        $asesor = auth()->user()->asesor;
        abort_if(!$asesor || $document->asesor_id !== $asesor->id, 403);
        $this->delete($document);
        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}