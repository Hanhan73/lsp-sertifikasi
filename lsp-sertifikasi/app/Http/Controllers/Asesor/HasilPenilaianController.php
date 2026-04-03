<?php

namespace App\Http\Controllers\Asesor;
 
use App\Http\Controllers\Controller;
use App\Models\BeritaAcara;
use App\Models\BeritaAcaraAsesi;
use App\Models\HasilObservasi;
use App\Models\HasilPortofolio;
use App\Models\Schedule;
use App\Models\SoalObservasi;
use App\Models\Portofolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

 
class HasilPenilaianController extends Controller
{
    // ── Helper: pastikan asesor punya akses ke jadwal ─────────────────────
    private function authorizeSchedule(Schedule $schedule): void
    {
        $asesor = Auth::user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403, 'Anda tidak ditugaskan ke jadwal ini.');
    }
 
    // =========================================================================
    // UPLOAD HASIL OBSERVASI
    // POST /asesor/jadwal/{schedule}/observasi/{soalObservasi}/upload
    // =========================================================================
     /**
     * Upload hasil observasi + auto-parse berita acara jika ada
     * POST /asesor/jadwal/{schedule}/observasi/{soalObservasi}/upload
     */
    public function uploadObservasi(Request $request, Schedule $schedule, SoalObservasi $soalObservasi)
    {
        $this->authorizeSchedule($schedule);
 
        $request->validate([
            'file'    => 'required|file|mimes:xlsx,xlsm,xls,pdf|max:20480',
            'catatan' => 'nullable|string|max:500',
        ]);
 
        // Hapus file lama
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
 
        // Auto-parse Berita Acara dari file Excel
        $baMessage = $this->parseAndSaveBeritaAcara($schedule, Storage::disk('private')->path($path));
 
        $msg = "Hasil observasi '{$soalObservasi->judul}' berhasil diupload.";
        if ($baMessage) $msg .= ' ' . $baMessage;
 
        return back()->with('success', $msg);
    }
 
    /**
     * Upload hasil portofolio + auto-parse berita acara jika ada
     * POST /asesor/jadwal/{schedule}/portofolio/{portofolio}/upload
     */
    public function uploadPortofolio(Request $request, Schedule $schedule, Portofolio $portofolio)
    {
        $this->authorizeSchedule($schedule);
 
        $request->validate([
            'file'    => 'required|file|mimes:xlsx,xlsm,xls,pdf|max:20480',
            'catatan' => 'nullable|string|max:500',
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
 
        // Auto-parse Berita Acara dari file Excel
        $baMessage = $this->parseAndSaveBeritaAcara($schedule, Storage::disk('private')->path($path));
 
        $msg = "Hasil portofolio '{$portofolio->judul}' berhasil diupload.";
        if ($baMessage) $msg .= ' ' . $baMessage;
 
        return back()->with('success', $msg);
    }
 
    // Hapus hasil observasi
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
 
    // Download hasil observasi (asesor download kembali file yang sudah diupload)
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
    // BERITA ACARA — Form web
    // GET  /asesor/jadwal/{schedule}/berita-acara
    // POST /asesor/jadwal/{schedule}/berita-acara/simpan
    // POST /asesor/jadwal/{schedule}/berita-acara/upload-file
    // =========================================================================
    public function beritaAcara(Schedule $schedule)
    {
        $this->authorizeSchedule($schedule);
 
        $schedule->load([
            'skema', 'tuk', 'asesmens.user',
            'beritaAcara.asesis.asesmen.user',
        ]);
 
        $beritaAcara = $schedule->beritaAcara;
 
        // Map rekomendasi yang sudah ada
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
 
    public function simpanBeritaAcara(Request $request, Schedule $schedule)
    {
        $this->authorizeSchedule($schedule);
 
        $request->validate([
            'tanggal_pelaksanaan'   => 'required|date',
            'catatan'               => 'nullable|string|max:1000',
            'rekomendasi'           => 'required|array',
            'rekomendasi.*'         => 'required|in:K,BK',
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
        });
 
        return back()->with('success', 'Berita acara berhasil disimpan.');
    }
 
    public function uploadFileBeritaAcara(Request $request, Schedule $schedule)
    {
        $this->authorizeSchedule($schedule);
 
        $request->validate([
            'file' => 'required|file|mimes:pdf,xlsx,xlsm,xls,doc,docx|max:20480',
        ]);
 
        $ba = BeritaAcara::firstOrCreate(
            ['schedule_id' => $schedule->id],
            [
                'tanggal_pelaksanaan' => $schedule->assessment_date,
                'dibuat_oleh'         => Auth::id(),
            ]
        );
 
        // Hapus file lama
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


       /**
     * Download template observasi dengan nama asesi sudah terisi
     * GET /asesor/jadwal/{schedule}/template/observasi/{soalObservasi}
     */
    public function downloadTemplateObservasi(Schedule $schedule, SoalObservasi $soalObservasi)
    {
        $this->authorizeSchedule($schedule);

        $paket = $soalObservasi->paket->first();
        abort_unless($paket && Storage::disk('private')->exists($paket->file_path), 404);

        $names = $schedule->asesmens->pluck('full_name')->all();
        
        $inputPath  = Storage::disk('private')->path($paket->file_path);
        $outputPath = sys_get_temp_dir() . '/' . uniqid('observasi_') . '.xlsm';

        $python = $this->getPythonCommand();
        $script = base_path('scripts/inject_asesi.py');
        
        $nameArgs = implode(' ', array_map('escapeshellarg', $names));
        $cmd = "$python " . escapeshellarg($script) . " " . escapeshellarg($inputPath) . " " . escapeshellarg($outputPath) . " $nameArgs 2>&1";
        
        shell_exec($cmd);

        return response()->download($outputPath, $paket->file_name)->deleteFileAfterSend();
    }
 
    /**
     * Download template portofolio (file original, tanpa modifikasi)
     * GET /asesor/jadwal/{schedule}/template/portofolio/{portofolio}
     */
    public function downloadTemplatePortofolio(Schedule $schedule, Portofolio $portofolio)
    {
        $this->authorizeSchedule($schedule);

        $paket = $portofolio->first();
        abort_unless($paket && Storage::disk('private')->exists($paket->file_path), 404);

        $names = $schedule->asesmens->pluck('full_name')->all();
        
        $inputPath  = Storage::disk('private')->path($paket->file_path);
        $outputPath = sys_get_temp_dir() . '/' . uniqid('portofolio_') . '.xlsm';

        $python = $this->getPythonCommand();
        $script = base_path('scripts/inject_asesi.py');
        
        $nameArgs = implode(' ', array_map('escapeshellarg', $names));
        $cmd = "$python " . escapeshellarg($script) . " " . escapeshellarg($inputPath) . " " . escapeshellarg($outputPath) . " $nameArgs 2>&1";
        
        shell_exec($cmd);

        return response()->download($outputPath, $paket->file_name)->deleteFileAfterSend();
    }

        /**
     * Preview / download Berita Acara sebagai PDF
     * GET /asesor/jadwal/{schedule}/berita-acara/pdf
     */
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
 
        // Buat map rekomendasi
        $rekMap = $ba->asesis->pluck('rekomendasi', 'asesmen_id');
 
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.berita-acara', [
            'schedule'   => $schedule,
            'beritaAcara'=> $ba,
            'rekMap'     => $rekMap,
            'asesor'     => $schedule->asesor,
        ])->setPaper('A4', 'portrait');
 
        $skemaName = preg_replace('/[\/\\\]/', '-', $schedule->skema->name);
        $skemaName = str_replace(' ', '_', $skemaName);
        $filename  = 'Berita_Acara_' . $skemaName . '_' . $schedule->assessment_date->format('d-m-Y') . '.pdf';
 
        return request()->boolean('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

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
 
        return Storage::disk('private')->download($dist->form_penilaian_path, $dist->form_penilaian_name);
    }
   /**
     * Jalankan Python parser, match nama ke asesmens di DB, simpan ke berita_acara.
     * Return string pesan untuk ditampilkan ke user, atau null jika PDF/gagal.
     */
private function parseAndSaveBeritaAcara(Schedule $schedule, string $filePath): ?string
{
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if (!in_array($ext, ['xlsx', 'xlsm', 'xls'])) {
        return null;
    }
 
    $python = $this->getPythonCommand();
    if (!$python) {
        \Log::warning('[BA] Python not found.');
        return null;
    }
 
    $script = base_path('scripts/parse_berita_acara.py');
    $cmd    = $python . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($filePath) . ' 2>&1';
    $output = shell_exec($cmd);
 
    \Log::info('[BA] Parser output: ' . $output);
 
    // Strip warning lines dari openpyxl sebelum json_decode
    $lines  = explode("\n", trim($output));
    $jsonLine = null;
    foreach (array_reverse($lines) as $line) {
        $line = trim($line);
        if (str_starts_with($line, '{')) {
            $jsonLine = $line;
            break;
        }
    }
 
    $data = $jsonLine ? json_decode($jsonLine, true) : null;
 
    if (!$data || isset($data['error'])) {
        \Log::warning('[BA] Parse failed: ' . ($data['error'] ?? $output));
        return null;
    }
 
    \Log::info('[BA] Parsed peserta: ' . count($data['peserta']) . ', filled: ' . $data['filled']);
 
    if (empty($data['peserta'])) {
        return null;
    }
 
    $pesertaDiisi = array_values(array_filter($data['peserta'], fn($p) => !empty($p['rekomendasi'])));
    if (empty($pesertaDiisi)) {
        return 'Berita Acara ditemukan tapi rekomendasi K/BK belum diisi di file Excel.';
    }
 
    $schedule->loadMissing('asesmens');
    $asesmens = $schedule->asesmens->values();
 
    \Log::info('[BA] Asesmens di jadwal: ' . $asesmens->pluck('full_name'));
 
    $matched = 0;
    $rekMap  = [];
 
    foreach ($pesertaDiisi as $i => $row) {
        $namaExcel = mb_strtolower(trim($row['nama']));
        $rek       = $row['rekomendasi'];
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
 
        // Fallback: urutan posisi
        if (!$best && isset($asesmens[$i])) {
            $best = $asesmens[$i];
            \Log::info("[BA] Fallback posisi untuk '{$row['nama']}' → '{$best->full_name}'");
        }
 
        if ($best && !isset($rekMap[$best->id])) {
            $rekMap[$best->id] = $rek;
            $matched++;
            \Log::info("[BA] Match: '{$row['nama']}' → ID {$best->id} ({$best->full_name}) = {$rek}");
        }
    }
 
    \Log::info("[BA] Total matched: {$matched}");
 
    if ($matched === 0) {
        return 'Berita Acara ditemukan tapi tidak ada peserta yang bisa dicocokkan.';
    }
 
    try {
        DB::transaction(function () use ($schedule, $rekMap) {
            $ba = BeritaAcara::updateOrCreate(
                ['schedule_id' => $schedule->id],
                [
                    'tanggal_pelaksanaan' => $schedule->assessment_date,
                    'dibuat_oleh'         => Auth::id(),
                ]
            );
 
            \Log::info('[BA] BA record: id=' . $ba->id . ', wasRecentlyCreated=' . ($ba->wasRecentlyCreated ? 'yes' : 'no'));
 
            foreach ($rekMap as $asesmenId => $rek) {
                $result = BeritaAcaraAsesi::updateOrCreate(
                    ['berita_acara_id' => $ba->id, 'asesmen_id' => $asesmenId],
                    ['rekomendasi' => $rek]
                );
                \Log::info("[BA] BeritaAcaraAsesi: ba_id={$ba->id}, asesmen_id={$asesmenId}, rek={$rek}, created={$result->wasRecentlyCreated}");
            }
        });
    } catch (\Throwable $e) {
        \Log::error('[BA] Transaction failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        return null;
    }
 
    $total = count($data['peserta']);
    return "Berita Acara otomatis terbaca: {$matched}/{$total} peserta berhasil dicocokkan.";
}
 
    /**
     * Cari command python yang tersedia di sistem.
     * Windows: 'python', Linux/Mac: 'python3'
     */
    private function getPythonCommand(): ?string
    {
        // Coba python3 dulu (Linux/Mac/WSL)
        $test = shell_exec('python3 --version 2>&1');
        if ($test && str_contains($test, 'Python 3')) {
            return 'python3';
        }
 
        // Fallback ke python (Windows)
        $test = shell_exec('python --version 2>&1');
        if ($test && str_contains($test, 'Python 3')) {
            return 'python';
        }
 
        return null;
    }
}