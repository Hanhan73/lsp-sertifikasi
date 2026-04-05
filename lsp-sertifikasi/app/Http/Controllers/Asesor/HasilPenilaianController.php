<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\BeritaAcara;
use App\Models\BeritaAcaraAsesi;
use App\Models\HasilObservasi;
use App\Models\HasilPortofolio;
use App\Models\Schedule;
use App\Models\SoalObservasi;
use App\Models\Portofolio;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HasilPenilaianController extends Controller
{
    private function authorizeSchedule(Schedule $schedule): void
    {
        $asesor = Auth::user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403, 'Anda tidak ditugaskan ke jadwal ini.');
    }

    // =========================================================================
    // UPLOAD HASIL OBSERVASI — hanya .xlsx
    // =========================================================================

    public function uploadObservasi(Request $request, Schedule $schedule, SoalObservasi $soalObservasi)
    {
        $this->authorizeSchedule($schedule);

        $request->validate([
            'file'    => 'required|file|mimes:xlsx|max:20480',
            'catatan' => 'nullable|string|max:500',
        ], [
            'file.mimes' => 'Format file harus .xlsx.',
        ]);

        $existing = HasilObservasi::where([
            'schedule_id'       => $schedule->id,
            'soal_observasi_id' => $soalObservasi->id,
        ])->first();

        if ($existing) {
            Storage::disk('private')->delete($existing->file_path);
            $existing->delete();
        }

        $file = $request->file('file');
        $path = $file->store("hasil/observasi/{$schedule->id}", 'private');

        HasilObservasi::create([
            'schedule_id'       => $schedule->id,
            'soal_observasi_id' => $soalObservasi->id,
            'file_path'         => $path,
            'file_name'         => $file->getClientOriginalName(),
            'uploaded_by'       => Auth::id(),
            'uploaded_at'       => now(),
            'catatan'           => $request->catatan,
        ]);

        $baMessage = $this->parseAndSaveBeritaAcara($schedule, Storage::disk('private')->path($path));

        $msg = "Hasil observasi '{$soalObservasi->judul}' berhasil diupload.";
        if ($baMessage) $msg .= ' ' . $baMessage;

        return back()->with('success', $msg);
    }

    // =========================================================================
    // UPLOAD HASIL PORTOFOLIO — hanya .xlsx
    // =========================================================================

    public function uploadPortofolio(Request $request, Schedule $schedule, Portofolio $portofolio)
    {
        $this->authorizeSchedule($schedule);

        $request->validate([
            'file'    => 'required|file|mimes:xlsx|max:20480',
            'catatan' => 'nullable|string|max:500',
        ], [
            'file.mimes' => 'Format file harus .xlsx.',
        ]);

        $existing = HasilPortofolio::where([
            'schedule_id'   => $schedule->id,
            'portofolio_id' => $portofolio->id,
        ])->first();

        if ($existing) {
            Storage::disk('private')->delete($existing->file_path);
            $existing->delete();
        }

        $file = $request->file('file');
        $path = $file->store("hasil/portofolio/{$schedule->id}", 'private');

        HasilPortofolio::create([
            'schedule_id'   => $schedule->id,
            'portofolio_id' => $portofolio->id,
            'file_path'     => $path,
            'file_name'     => $file->getClientOriginalName(),
            'uploaded_by'   => Auth::id(),
            'uploaded_at'   => now(),
            'catatan'       => $request->catatan,
        ]);

        $baMessage = $this->parseAndSaveBeritaAcara($schedule, Storage::disk('private')->path($path));

        $msg = "Hasil portofolio '{$portofolio->judul}' berhasil diupload.";
        if ($baMessage) $msg .= ' ' . $baMessage;

        return back()->with('success', $msg);
    }

    // =========================================================================
    // HAPUS & DOWNLOAD
    // =========================================================================

    public function hapusObservasi(Schedule $schedule, SoalObservasi $soalObservasi)
    {
        $this->authorizeSchedule($schedule);

        $hasil = HasilObservasi::where([
            'schedule_id'       => $schedule->id,
            'soal_observasi_id' => $soalObservasi->id,
        ])->firstOrFail();

        Storage::disk('private')->delete($hasil->file_path);
        $hasil->delete();

        return back()->with('success', 'File hasil observasi dihapus.');
    }

    public function downloadObservasi(Schedule $schedule, SoalObservasi $soalObservasi)
    {
        $this->authorizeSchedule($schedule);

        $hasil = HasilObservasi::where([
            'schedule_id'       => $schedule->id,
            'soal_observasi_id' => $soalObservasi->id,
        ])->firstOrFail();

        return Storage::disk('private')->download($hasil->file_path, $hasil->file_name);
    }

    public function hapusPortofolio(Schedule $schedule, Portofolio $portofolio)
    {
        $this->authorizeSchedule($schedule);

        $hasil = HasilPortofolio::where([
            'schedule_id'   => $schedule->id,
            'portofolio_id' => $portofolio->id,
        ])->firstOrFail();

        Storage::disk('private')->delete($hasil->file_path);
        $hasil->delete();

        return back()->with('success', 'File hasil portofolio dihapus.');
    }

    public function downloadPortofolio(Schedule $schedule, Portofolio $portofolio)
    {
        $this->authorizeSchedule($schedule);

        $hasil = HasilPortofolio::where([
            'schedule_id'   => $schedule->id,
            'portofolio_id' => $portofolio->id,
        ])->firstOrFail();

        return Storage::disk('private')->download($hasil->file_path, $hasil->file_name);
    }

    // =========================================================================
    // DOWNLOAD TEMPLATE
    // =========================================================================

    public function downloadTemplateObservasi(Schedule $schedule, SoalObservasi $soalObservasi)
    {
        $this->authorizeSchedule($schedule);

        $paket = $soalObservasi->paket->first();
        abort_unless($paket && Storage::disk('private')->exists($paket->file_path), 404, 'File template tidak ditemukan.');

        $ext        = pathinfo($paket->file_name, PATHINFO_EXTENSION) ?: 'xlsm';
        $outputPath = sys_get_temp_dir() . '/' . uniqid('tpl_obs_') . '.' . $ext;

        return response()->download($outputPath, $paket->file_name)->deleteFileAfterSend();
    }

    public function downloadTemplatePortofolio(Schedule $schedule, Portofolio $portofolio)
    {
        $this->authorizeSchedule($schedule);

        abort_unless(
            $portofolio->file_path && Storage::disk('private')->exists($portofolio->file_path),
            404, 'File template tidak ditemukan.'
        );

        $inputPath  = Storage::disk('private')->path($portofolio->file_path);
        $ext        = strtolower(pathinfo($portofolio->file_name, PATHINFO_EXTENSION) ?: 'xlsm');
        $outputPath = sys_get_temp_dir() . '/' . uniqid('tpl_porto_') . '.' . $ext;
        $names      = $schedule->asesmens->pluck('full_name')->all();

        if ($ext === 'pdf') {
            return Storage::disk('private')->download($portofolio->file_path, $portofolio->file_name);
        }

        $service = new ExcelService();
        $ok      = $service->injectNamaAsesi($inputPath, $outputPath, $names);

        abort_unless($ok && file_exists($outputPath), 500, 'Gagal memproses template portofolio.');

        return response()->download($outputPath, $portofolio->file_name)->deleteFileAfterSend();
    }

    // =========================================================================
    // DOWNLOAD FORM PENILAIAN
    // =========================================================================

    public function downloadFormPenilaianObservasi(Schedule $schedule, SoalObservasi $soalObservasi)
    {
        $this->authorizeSchedule($schedule);

        $dist = \App\Models\DistribusiSoalObservasi::where([
            'schedule_id'       => $schedule->id,
            'soal_observasi_id' => $soalObservasi->id,
        ])->firstOrFail();

        abort_unless(
            $dist->form_penilaian_path && Storage::disk('private')->exists($dist->form_penilaian_path),
            404, 'Form penilaian belum diupload oleh Manajer Sertifikasi.'
        );

        $ext     = strtolower(pathinfo($dist->form_penilaian_name, PATHINFO_EXTENSION));
        $isExcel = in_array($ext, ['xlsx', 'xlsm', 'xls']);

        if (!$isExcel) {
            return Storage::disk('private')->download($dist->form_penilaian_path, $dist->form_penilaian_name);
        }

        $schedule->loadMissing('asesmens');
        $names = $schedule->asesmens->pluck('full_name')->all();

        $inputPath  = Storage::disk('private')->path($dist->form_penilaian_path);
        $outputPath = sys_get_temp_dir() . '/' . uniqid('form_pen_') . '.' . $ext;

        $service = new ExcelService();
        $ok      = $service->injectNamaAsesi($inputPath, $outputPath, $names);

        if (!$ok || !file_exists($outputPath)) {
            \Log::warning('[FormPenilaian] Inject gagal, fallback ke file asli: ' . $dist->form_penilaian_name);
            return Storage::disk('private')->download($dist->form_penilaian_path, $dist->form_penilaian_name);
        }

        return response()->download($outputPath, $dist->form_penilaian_name)->deleteFileAfterSend();
    }

    // =========================================================================
    // BERITA ACARA
    // =========================================================================

    public function beritaAcara(Schedule $schedule)
    {
        $this->authorizeSchedule($schedule);

        $schedule->load([
            'skema', 'tuk', 'asesmens.user',
            'beritaAcara.asesis.asesmen.user',
        ]);

        $beritaAcara = $schedule->beritaAcara;

        $rekomendasiMap = [];
        if ($beritaAcara) {
            foreach ($beritaAcara->asesis as $ba) {
                $rekomendasiMap[$ba->asesmen_id] = $ba->rekomendasi;
            }
        }

        return view('asesor.penilaian.berita-acara', compact(
            'schedule', 'beritaAcara', 'rekomendasiMap'
        ));
    }

    /**
     * TTD & lock berita acara — POST /asesor/jadwal/{schedule}/berita-acara/sign
     */
    public function signBeritaAcara(Request $request, Schedule $schedule)
    {
        $this->authorizeSchedule($schedule);

        $ba = $schedule->beritaAcara;
        abort_if(!$ba, 404, 'Berita acara belum diisi.');

        if ($ba->isSigned()) {
            return response()->json([
                'success' => false,
                'message' => 'Berita acara sudah ditandatangani dan dikunci.',
            ], 400);
        }

        $request->validate(['signature' => 'required|string']);

        $user = auth()->user();
        if (empty($user->signature)) {
            $sig = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature);
            $user->update(['signature' => $sig]);
        }

        $ba->update([
            'signed_at' => now(),
            'signed_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berita acara berhasil ditandatangani dan dikunci.',
        ]);
    }

    public function simpanBeritaAcara(Request $request, Schedule $schedule)
    {
        $this->authorizeSchedule($schedule);

        // Guard: tidak bisa diubah jika sudah TTD
        $ba = $schedule->beritaAcara;
        if ($ba && $ba->isSigned()) {
            return back()->with('error', 'Berita acara sudah ditandatangani dan tidak dapat diubah.');
        }
        $request->validate([
            'tanggal_pelaksanaan' => 'required|date',
            'catatan'             => 'nullable|string|max:1000',
            'rekomendasi'         => 'required|array',
            'rekomendasi.*'       => 'required|in:K,BK',
        ]);

        DB::transaction(function () use ($request, $schedule) {
            $ba = BeritaAcara::updateOrCreate(
                ['schedule_id' => $schedule->id],
                [
                    'tanggal_pelaksanaan' => $request->tanggal_pelaksanaan,
                    'catatan'             => $request->catatan,
                    'dibuat_oleh'         => Auth::id(),
                ]
            );

            foreach ($request->rekomendasi as $asesmenId => $rek) {
                BeritaAcaraAsesi::updateOrCreate(
                    ['berita_acara_id' => $ba->id, 'asesmen_id' => $asesmenId],
                    ['rekomendasi' => $rek]
                );
            }

            foreach ($schedule->asesmens as $asesmen) {
                if ($asesmen->hadir == 0) {
                    continue;
                } else{
                    $asesmen->update([
                        'status' => 'assessed',
                    ]);
                }
            }

        });

        return back()->with('success', 'Berita acara berhasil disimpan.');
    }

    public function uploadFileBeritaAcara(Request $request, Schedule $schedule)
    {
        $this->authorizeSchedule($schedule);

        // Berita acara sudah ditandatangani — tidak bisa diupload ulang
        $existingBa = $schedule->beritaAcara;
        if ($existingBa && $existingBa->isSigned()) {
            return back()->with('error', 'Berita acara sudah ditandatangani dan tidak dapat diubah.');
        }

        // Berita acara file upload juga hanya .xlsx
        $request->validate([
            'file' => 'required|file|mimes:xlsx|max:20480',
        ], [
            'file.mimes' => 'Format file harus .xlsx.',
        ]);

        $ba = BeritaAcara::firstOrCreate(
            ['schedule_id' => $schedule->id],
            [
                'tanggal_pelaksanaan' => $schedule->assessment_date,
                'dibuat_oleh'         => Auth::id(),
            ]
        );

        if ($ba->file_path) {
            Storage::disk('private')->delete($ba->file_path);
        }

        $file = $request->file('file');
        $path = $file->store("hasil/berita-acara/{$schedule->id}", 'private');

        $ba->update([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('success', 'File berita acara berhasil diupload.');
    }

    public function downloadFileBeritaAcara(Schedule $schedule)
    {
        $this->authorizeSchedule($schedule);

        $ba = $schedule->beritaAcara;
        abort_unless($ba && $ba->file_path, 404, 'File belum diupload.');

        return Storage::disk('private')->download($ba->file_path, $ba->file_name);
    }

    public function pdfBeritaAcara(Schedule $schedule)
    {
        $this->authorizeSchedule($schedule);

        $ba = $schedule->beritaAcara;
        abort_unless($ba, 404, 'Berita acara belum diisi.');

        $schedule->load([
            'skema', 'tuk', 'asesor.user',
            'asesmens',
            'beritaAcara.asesis.asesmen',
        ]);

        $rekMap = $ba->asesis->pluck('rekomendasi', 'asesmen_id');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.berita-acara', [
            'schedule'    => $schedule,
            'beritaAcara' => $ba,
            'rekMap'      => $rekMap,
            'asesor'      => $schedule->asesor,
        ])->setPaper('A4', 'portrait');

        $skemaName = str_replace([' ', '/', '\\'], ['_', '-', '-'], $schedule->skema->name);
        $filename  = 'Berita_Acara_' . $skemaName . '_' . $schedule->assessment_date->format('d-m-Y') . '.pdf';

        return request()->boolean('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

    /**
     * Tanda tangani berita acara — mengunci berita acara agar tidak bisa diubah.
     * Dipanggil via modal konfirmasi dengan signature pad.
     */
    public function tandaTanganBeritaAcara(Request $request, Schedule $schedule)
    {
        $this->authorizeSchedule($schedule);

        $ba = $schedule->beritaAcara;
        abort_unless($ba && $ba->asesis->isNotEmpty(), 422, 'Berita acara belum lengkap diisi.');

        if ($ba->isSigned()) {
            return back()->with('info', 'Berita acara sudah ditandatangani sebelumnya.');
        }

        // Jika asesor belum punya TTD, simpan dulu
        if ($request->filled('signature')) {
            $sig = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature);
            auth()->user()->update(['signature' => $sig]);
        }

        abort_if(empty(auth()->user()->refresh()->signature), 422, 'Tanda tangan diperlukan.');

        $ba->update([
            'signed_at' => now(),
            'signed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Berita acara berhasil ditandatangani dan dikunci.');
    }

    // =========================================================================
    // PRIVATE — Auto-parse Berita Acara dari Excel
    // =========================================================================

    private function parseAndSaveBeritaAcara(Schedule $schedule, string $filePath): ?string
    {
        $service = new ExcelService();
        $data    = $service->parseBeritaAcara($filePath);

        if (!$data || isset($data['error'])) {
            \Log::warning('[BA] Parse failed: ' . ($data['error'] ?? 'unknown'));
            return null;
        }

        \Log::info('[BA] Parsed peserta: ' . count($data['peserta']) . ', filled: ' . $data['filled']);

        if (empty($data['peserta'])) return null;

        $pesertaDiisi = array_values(array_filter($data['peserta'], fn($p) => !empty($p['rekomendasi'])));
        if (empty($pesertaDiisi)) {
            return 'Berita Acara ditemukan tapi rekomendasi K/BK belum diisi di file Excel.';
        }

        $schedule->loadMissing('asesmens');
        $asesmens = $schedule->asesmens->values();
        $matched  = 0;
        $rekMap   = [];

        foreach ($pesertaDiisi as $i => $row) {
            $namaExcel = mb_strtolower(trim($row['nama']));
            $best      = null;
            $bestScore = 0;

            foreach ($asesmens as $asesmen) {
                $namaDB = mb_strtolower(trim($asesmen->full_name));
                similar_text($namaExcel, $namaDB, $pct);
                if ($pct > $bestScore && $pct >= 70) {
                    $bestScore = $pct;
                    $best      = $asesmen;
                }
            }

            if (!$best && isset($asesmens[$i])) {
                $best = $asesmens[$i];
                \Log::info("[BA] Fallback posisi untuk '{$row['nama']}' → '{$best->full_name}'");
            }

            if ($best && !isset($rekMap[$best->id])) {
                $rekMap[$best->id] = $row['rekomendasi'];
                $matched++;
            }
        }

        if ($matched === 0) {
            return 'Berita Acara ditemukan tapi tidak ada peserta yang bisa dicocokkan.';
        }

        DB::transaction(function () use ($schedule, $rekMap) {
            $ba = BeritaAcara::updateOrCreate(
                ['schedule_id' => $schedule->id],
                [
                    'tanggal_pelaksanaan' => $schedule->assessment_date,
                    'dibuat_oleh'         => Auth::id(),
                ]
            );
            foreach ($rekMap as $asesmenId => $rek) {
                BeritaAcaraAsesi::updateOrCreate(
                    ['berita_acara_id' => $ba->id, 'asesmen_id' => $asesmenId],
                    ['rekomendasi' => $rek]
                );
            }
        });

        return "Berita Acara otomatis terbaca: {$matched}/" . count($data['peserta']) . " peserta berhasil dicocokkan.";
    }
}