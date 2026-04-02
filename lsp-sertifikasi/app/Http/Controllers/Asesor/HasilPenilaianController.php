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
    public function uploadObservasi(Request $request, Schedule $schedule, SoalObservasi $soalObservasi)
    {
        $this->authorizeSchedule($schedule);
 
        $request->validate([
            'file'    => 'required|file|mimes:xlsx,xlsm,xls,pdf|max:20480',
            'catatan' => 'nullable|string|max:500',
        ], [
            'file.mimes' => 'File harus berformat Excel (.xlsx/.xlsm) atau PDF.',
            'file.max'   => 'Ukuran file maksimal 20 MB.',
        ]);
 
        // Hapus file lama jika ada
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
 
        return back()->with('success', "Hasil observasi '{$soalObservasi->judul}' berhasil diupload.");
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
 
    // =========================================================================
    // UPLOAD HASIL PORTOFOLIO
    // POST /asesor/jadwal/{schedule}/portofolio/{portofolio}/upload
    // =========================================================================
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
 
        return back()->with('success', "Hasil portofolio '{$portofolio->judul}' berhasil diupload.");
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
}