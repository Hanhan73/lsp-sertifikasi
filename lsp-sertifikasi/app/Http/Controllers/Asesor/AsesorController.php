<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Asesmen;
use App\Models\AplDua;
use App\Models\AplSatu;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AsesorController extends Controller
{
    /**
     * Dashboard asesor
     */
    public function dashboard()
    {
        $asesor = auth()->user()->asesor;

        $schedules = Schedule::with(['tuk', 'skema', 'asesmens'])
            ->where('asesor_id', $asesor->id)
            ->orderBy('assessment_date', 'asc')
            ->get();

        $stats = [
            'upcoming'    => $schedules->filter(fn($s) => $s->assessment_date->isFuture())->count(),
            'today'       => $schedules->filter(fn($s) => $s->assessment_date->isToday())->count(),
            'past'        => $schedules->filter(fn($s) => $s->assessment_date->isPast())->count(),
            'total_asesi' => $schedules->sum(fn($s) => $s->asesmens->count()),
        ];

        $todaySchedules    = $schedules->filter(fn($s) => $s->assessment_date->isToday());
        $upcomingSchedules = $schedules->filter(fn($s) => $s->assessment_date->isFuture())->take(5);

        return view('asesor.dashboard', compact('stats', 'todaySchedules', 'upcomingSchedules', 'asesor'));
    }

    /**
     * Daftar semua jadwal asesmen asesor ini
     */
    public function schedule(Request $request)
    {
        $asesor = auth()->user()->asesor;

        $query = Schedule::with(['tuk', 'skema', 'asesmens.aplsatu', 'asesmens.apldua'])
            ->where('asesor_id', $asesor->id);

        $filter = $request->input('filter', 'upcoming');
        if ($filter === 'upcoming') {
            $query->where('assessment_date', '>=', now()->toDateString());
        } elseif ($filter === 'past') {
            $query->where('assessment_date', '<', now()->toDateString());
        } elseif ($filter === 'today') {
            $query->whereDate('assessment_date', now()->toDateString());
        }

        $schedules = $query->orderBy('assessment_date', $filter === 'past' ? 'desc' : 'asc')->get();

        return view('asesor.schedule.index', compact('schedules', 'filter'));
    }

    /**
     * Detail jadwal — daftar asesi di jadwal ini
     */
    public function scheduleDetail(Schedule $schedule)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);

        $schedule->load([
            'tuk',
            'skema',
            'asesmens.user',
            'asesmens.aplsatu',
            'asesmens.apldua.jawabans',
            'asesmens.soalTeoriAsesi.soalTeori',
            'asesmens.jawabanObservasi',
            'distribusiSoalTeori',
            'distribusiSoalObservasi.soalObservasi.paket',
            'distribusiPortofolio.portofolio',
            'distribusiSoalObservasi.paketSoalObservasi',
            'hasilObservasi',
            'hasilPortofolio',
            'beritaAcara.asesis',
        ]);

        // Build rekomendasiMap dari beritaAcara yang sudah ada
        $rekomendasiMap = [];
        if ($schedule->beritaAcara) {
            foreach ($schedule->beritaAcara->asesis as $ba) {
                $rekomendasiMap[$ba->asesmen_id] = $ba->rekomendasi;
            }
        }

        // Hitung apakah asesmen sudah bisa dimulai (ada asesi yang sudah verified apl02 & frak01)
        $canStartAsesmen = $this->canStartAsesmen($schedule);

        return view('asesor.schedule.detail', compact('schedule', 'asesor', 'rekomendasiMap', 'canStartAsesmen'));
    }

    /**
     * Cek apakah jadwal sudah siap untuk memulai asesmen.
     * Syarat: setidaknya ada 1 asesi yang frak01 = verified DAN apldua = verified.
     */
    private function canStartAsesmen(Schedule $schedule): bool
    {
        foreach ($schedule->asesmens as $asesmen) {
            $frak01Ready = $asesmen->frak01 && in_array($asesmen->frak01->status, ['verified', 'approved', 'submitted']);
            $apl02Ready  = $asesmen->apldua && in_array($asesmen->apldua->status, ['verified', 'approved', 'submitted']);
            if ($frak01Ready && $apl02Ready) {
                return true;
            }
        }
        return false;
    }

    /**
     * Mulai asesmen — hanya bisa dilakukan pada hari-H jadwal.
     * Mengubah status asesi yang sudah ready menjadi 'assessed' (atau status berikutnya).
     * Endpoint: POST /asesor/jadwal/{schedule}/mulai
     */
    public function mulaiAsesmen(Request $request, Schedule $schedule)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);

        // Hanya bisa dilakukan hari-H
        if (! $schedule->assessment_date->isToday()) {
            return response()->json([
                'success' => false,
                'message' => 'Asesmen hanya bisa dimulai pada hari pelaksanaan.',
            ], 422);
        }

        $schedule->loadMissing(['asesmens.frak01', 'asesmens.apldua']);

        $started = 0;
        foreach ($schedule->asesmens as $asesmen) {
            $frak01Ready = $asesmen->frak01 && in_array($asesmen->frak01->status, ['verified', 'approved', 'submitted']);
            $apl02Ready  = $asesmen->apldua && in_array($asesmen->apldua->status, ['verified', 'approved', 'submitted']);

            if ($frak01Ready && $apl02Ready && $asesmen->status !== 'assessed') {
                $asesmen->update([
                    'status'      => 'asesmen_started',
                    'assessed_by' => auth()->id(),
                    'assessed_at' => now(),
                ]);
                $started++;
            }
        }

        if ($started > 0) {
            $schedule->update(['assessment_start' => true]);
        }

        if ($started === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada asesi yang siap untuk dimulai (FR.AK.01 dan APL-02 harus sudah diverifikasi).',
            ], 422);
        }

        Log::info('[ASESMEN-MULAI] Asesor memulai asesmen', [
            'schedule_id' => $schedule->id,
            'asesor_id'   => $asesor->id,
            'started'     => $started,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Asesmen dimulai untuk {$started} asesi.",
        ]);
    }

    /**
     * Detail asesi — semua dokumen APL-01, APL-02, dll
     */
    public function asesiDetail(Schedule $schedule, Asesmen $asesmen)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);
        abort_if($asesmen->schedule_id !== $schedule->id, 403);

        $asesmen->load([
            'user',
            'tuk',
            'skema.unitKompetensis.elemens.kuks',
            'aplsatu.buktiKelengkapan',
            'apldua.jawabans.elemen',
            'frak01',
            'certificate',
        ]);

        return view('asesor.asesi.detail', compact('schedule', 'asesmen', 'asesor'));
    }

    /**
     * Verifikasi APL-02
     */
    public function verifyApl02(Request $request, Schedule $schedule, Asesmen $asesmen)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);
        abort_if($asesmen->schedule_id !== $schedule->id, 403);

        $request->validate([
            'rekomendasi' => 'required|in:lanjut,tidak_lanjut',
            'catatan'     => 'nullable|string|max:1000',
            'signature'   => 'required|string',
            'nama_asesor' => 'required|string|max:255',
        ]);

        $apldua = $asesmen->apldua;

        if (!$apldua || $apldua->status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'APL-02 belum disubmit oleh asesi atau sudah diverifikasi.',
            ], 400);
        }

        $signature = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature);

        $apldua->update([
            'status'             => 'verified',
            'rekomendasi_asesor' => $request->rekomendasi,
            'catatan_asesor'     => $request->catatan,
            'ttd_asesor'         => $signature,
            'nama_ttd_asesor'    => $request->nama_asesor,
            'tanggal_ttd_asesor' => now(),
            'verified_by'        => auth()->id(),
            'verified_at'        => now(),
        ]);

        Log::info('[APL02-VERIFY] Asesor verified APL-02', [
            'apldua_id'   => $apldua->id,
            'asesmen_id'  => $asesmen->id,
            'asesor_id'   => $asesor->id,
            'rekomendasi' => $request->rekomendasi,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'APL-02 berhasil diverifikasi!',
        ]);
    }

    /**
     * Preview PDF APL-01
     */
    public function previewApl01(Schedule $schedule, Asesmen $asesmen)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);
        abort_if($asesmen->schedule_id !== $schedule->id, 403);

        $aplsatu = $asesmen->aplsatu()->with('buktiKelengkapan')->first();
        abort_if(!$aplsatu, 404, 'APL-01 belum ada');

        $asesmen->load(['skema.unitKompetensis', 'tuk']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.aplsatu', [
            'aplsatu' => $aplsatu,
            'asesmen' => $asesmen,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('APL-01_' . str_replace(' ', '_', $asesmen->full_name) . '.pdf');
    }

    /**
     * Preview PDF APL-02
     */
    public function previewApl02(Schedule $schedule, Asesmen $asesmen)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);
        abort_if($asesmen->schedule_id !== $schedule->id, 403);

        $apldua = $asesmen->apldua()->with('jawabans')->first();
        abort_if(!$apldua, 404, 'APL-02 belum ada');
        abort_if(!in_array($apldua->status, ['verified', 'approved']), 403, 'APL-02 belum diverifikasi');

        $asesmen->load(['skema.unitKompetensis.elemens.kuks', 'schedule.asesor']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.apldua', [
            'apldua'        => $apldua,
            'asesmen'       => $asesmen,
            'asesor_no_reg' => $asesor->no_reg_met,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('APL-02_' . str_replace(' ', '_', $asesmen->full_name) . '.pdf');
    }

    /**
     * Download SK jadwal
     */
    public function downloadSk(Schedule $schedule)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);

        if (!$schedule->hasSk()) {
            abort(404, 'SK belum tersedia untuk jadwal ini.');
        }

        if (!Storage::disk('private')->exists($schedule->sk_path)) {
            abort(404, 'File SK tidak ditemukan.');
        }

        $ext      = pathinfo($schedule->sk_path, PATHINFO_EXTENSION);
        $filename = 'SK_' . str_replace('/', '-', $schedule->sk_number) . '.' . $ext;

        return response()->streamDownload(function () use ($schedule) {
            echo Storage::disk('private')->get($schedule->sk_path);
        }, $filename, [
            'Content-Type' => $ext === 'pdf' ? 'application/pdf' : 'application/octet-stream',
        ]);
    }

    /**
     * Download paket soal observasi
     */
    public function downloadPaketObservasi(\App\Models\PaketSoalObservasi $paket)
    {
        $asesor = auth()->user()->asesor;

        $skemaId = $paket->soalObservasi->skema_id;
        $boleh   = Schedule::where('asesor_id', $asesor->id)
            ->where('skema_id', $skemaId)
            ->exists();

        abort_if(!$boleh, 403, 'Akses ditolak.');
        abort_if(!$paket->file_path, 404, 'File tidak tersedia.');

        if (!Storage::disk('private')->exists($paket->file_path)) {
            abort(404, 'File paket tidak ditemukan.');
        }

        $filename = $paket->file_name ?? 'Paket_' . $paket->kode_paket . '.pdf';

        return response()->streamDownload(function () use ($paket) {
            echo Storage::disk('private')->get($paket->file_path);
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Toggle kehadiran asesi — AJAX dari detail jadwal.
     *
     * Logika:
     * - Default asesi = hadir (hadir = true / null).
     * - Toggle menjadi tidak hadir jika sebelumnya hadir, dan sebaliknya.
     * - Request body: { hadir: true|false } — nilai yang INGIN diset.
     */
    public function toggleHadir(Request $request, Asesmen $asesmen)
    {
        $asesor   = auth()->user()->asesor;
        $schedule = $asesmen->schedule;

        abort_if(!$schedule || $schedule->asesor_id !== $asesor->id, 403);

        // Daftar hadir sudah ditandatangani — tidak bisa diubah
        if ($schedule->isDaftarHadirSigned()) {
            return response()->json([
                'success' => false,
                'message' => 'Daftar hadir sudah ditandatangani dan tidak dapat diubah.',
            ], 403);
        }

        $request->validate(['hadir' => 'required|boolean']);

        $asesmen->update(['hadir' => $request->boolean('hadir')]);

        return response()->json([
            'success' => true,
            'hadir'   => $asesmen->hadir,
            'label'   => $asesmen->hadir ? 'Hadir' : 'Tidak Hadir',
        ]);
    }

    /**
     * Verifikasi daftar hadir — asesor mengkonfirmasi daftar hadir sudah benar
     * dan menandatanganinya. Dipanggil via AJAX dari modal konfirmasi.
     */
    public function verifikasiDaftarHadir(Request $request, Schedule $schedule)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);

        // Guard
        $schedule->loadMissing(['asesmens.apldua', 'asesmens.frak01']);
        $ready = $schedule->asesmens->contains(function ($a) {
            return $a->apldua && in_array($a->apldua->status, ['verified', 'approved'])
                && $a->frak01 && in_array($a->frak01->status, ['verified', 'approved', 'submitted']);
        });
        if (!$ready) {
            return response()->json([
                'success' => false,
                'message' => 'APL-02 dan FR.AK.01 minimal 1 asesi harus sudah diverifikasi.',
            ], 422);
        }

        if ($schedule->isDaftarHadirSigned()) {
            return response()->json(['success' => true, 'already' => true]);
        }

        $schedule->update([
            'daftar_hadir_signed_at' => now(),
            'daftar_hadir_signed_by' => auth()->id(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Download / preview daftar hadir PDF.
     * Hanya bisa diakses jika asesor sudah punya tanda tangan di profil.
     */
    public function daftarHadir(Request $request, Schedule $schedule)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);

        // Guard: asesor harus sudah TTD
        if (empty(auth()->user()->signature)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanda tangan belum ada. Silakan tambahkan tanda tangan terlebih dahulu.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Tanda tangan belum ada. Silakan tambahkan tanda tangan terlebih dahulu.');
        }

        $schedule->load(['tuk', 'skema', 'asesor.user', 'asesmens.user']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.daftar-hadir', [
            'schedule' => $schedule,
            'asesor'   => $schedule->asesor,
            'asesmens' => $schedule->asesmens,
        ])->setPaper('A4', 'portrait');

        $skemaName = preg_replace('/[\/\\\]/', '-', $schedule->skema->name);
        $skemaName = str_replace(' ', '_', $skemaName);
        $filename  = 'Daftar_Hadir_' . $skemaName . '.pdf';

        if ($request->boolean('preview')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    /**
     * Upload SK Pengangkatan oleh asesor sendiri
     */
    public function uploadSk(Request $request)
    {
        $asesor = auth()->user()->asesor;

        $request->validate([
            'sk_number'      => 'required|string|max:100',
            'sk_date'        => 'required|date|before_or_equal:today',
            'sk_valid_until' => 'nullable|date|after:sk_date',
            'sk_file'        => ($asesor->sk_pengangkatan_path ? 'nullable' : 'required')
                                . '|file|mimes:pdf|max:5120',
        ], [
            'sk_file.required'        => 'File SK wajib diupload.',
            'sk_file.mimes'           => 'File harus berformat PDF.',
            'sk_file.max'             => 'Ukuran file maksimal 5 MB.',
            'sk_date.before_or_equal' => 'Tanggal SK tidak boleh di masa depan.',
        ]);

        if ($asesor->sk_pengangkatan_path) {
            Storage::disk('private')->delete($asesor->sk_pengangkatan_path);
        }

        $path     = null;
        $filename = null;

        if ($request->hasFile('sk_file')) {
            $file     = $request->file('sk_file');
            $filename = 'SK_' . str_replace(' ', '_', $asesor->nama) . '_' . now()->format('Ymd') . '.pdf';
            $path     = $file->storeAs('asesors/sk', $filename, 'private');
        }

        $asesor->update([
            'sk_pengangkatan_number'      => $request->sk_number,
            'sk_pengangkatan_date'        => $request->sk_date,
            'sk_pengangkatan_valid_until' => $request->sk_valid_until,
            'sk_pengangkatan_path'        => $path ?? $asesor->sk_pengangkatan_path,
            'sk_pengangkatan_filename'    => $filename ?? $asesor->sk_pengangkatan_filename,
        ]);

        return redirect()->route('asesor.dokumen.sk')
            ->with('success', 'SK Pengangkatan berhasil ' . ($path ? 'diupload' : 'diperbarui') . '.');
    }

    /**
     * Download SK Pengangkatan asesor sendiri
     */
    public function downloadSkPengangkatan()
    {
        $asesor = auth()->user()->asesor;

        abort_unless($asesor->sk_pengangkatan_path, 404, 'SK belum tersedia.');
        abort_unless(
            Storage::disk('private')->exists($asesor->sk_pengangkatan_path),
            404, 'File SK tidak ditemukan.'
        );

        return response()->streamDownload(function () use ($asesor) {
            echo Storage::disk('private')->get($asesor->sk_pengangkatan_path);
        }, $asesor->sk_pengangkatan_filename ?? 'SK_Pengangkatan.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Hapus SK Pengangkatan
     */
    public function deleteSkPengangkatan()
    {
        $asesor = auth()->user()->asesor;

        if ($asesor->sk_pengangkatan_path) {
            Storage::disk('private')->delete($asesor->sk_pengangkatan_path);
        }

        $asesor->update([
            'sk_pengangkatan_number'      => null,
            'sk_pengangkatan_date'        => null,
            'sk_pengangkatan_valid_until' => null,
            'sk_pengangkatan_path'        => null,
            'sk_pengangkatan_filename'    => null,
        ]);

        return redirect()->route('asesor.dokumen.sk')
            ->with('success', 'SK Pengangkatan berhasil dihapus.');
    }

    /**
     * Halaman Dokumen SK
     */
    public function documentSk()
    {
        $asesor = auth()->user()->asesor;

        $schedules = Schedule::with(['tuk', 'skema'])
            ->where('asesor_id', $asesor->id)
            ->where('approval_status', 'approved')
            ->withCount('asesmens')
            ->orderBy('assessment_date', 'desc')
            ->get();

        return view('asesor.document.sk', compact('asesor', 'schedules'));
    }
    public function downloadLampiranObservasi(\App\Models\PaketSoalObservasi $paket)
    {
        $asesor  = auth()->user()->asesor;
        $skemaId = $paket->soalObservasi->skema_id;

        $boleh = Schedule::where('asesor_id', $asesor->id)
            ->where('skema_id', $skemaId)
            ->exists();

        abort_if(!$boleh, 403, 'Akses ditolak.');
        abort_unless(
            $paket->lampiran_path && Storage::disk('private')->exists($paket->lampiran_path),
            404, 'File lampiran tidak tersedia.'
        );

        return response()->streamDownload(function () use ($paket) {
            echo Storage::disk('private')->get($paket->lampiran_path);
        }, $paket->lampiran_name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }
public function uploadFotoDokumentasi(Request $request, Schedule $schedule)
{
    $asesor = auth()->user()->asesor;
    abort_if($schedule->asesor_id !== $asesor->id, 403);

    $request->validate([
        'foto_1' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        'foto_2' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
    ], [
        'foto_1.image' => 'File harus berupa gambar.',
        'foto_1.max'   => 'Ukuran foto maksimal 5 MB.',
        'foto_2.image' => 'File harus berupa gambar.',
        'foto_2.max'   => 'Ukuran foto maksimal 5 MB.',
    ]);

    $data = [];

    foreach (['foto_1', 'foto_2'] as $slot) {
        if ($request->hasFile($slot)) {
            $col = 'foto_dokumentasi_' . substr($slot, -1);
            // Hapus file lama
            if ($schedule->$col) {
                Storage::disk('private')->delete($schedule->$col);
            }
            $data[$col] = $request->file($slot)->store("dokumentasi/{$schedule->id}", 'private');
        }
    }

    if (empty($data)) {
        return back()->with('error', 'Tidak ada foto yang diupload.');
    }

    $data['foto_uploaded_by'] = auth()->id();
    $data['foto_uploaded_at'] = now();
    $schedule->update($data);

    return back()->with('success', 'Foto dokumentasi berhasil diupload.');
}

public function hapusFotoDokumentasi(Schedule $schedule, int $slot)
{
    $asesor = auth()->user()->asesor;
    abort_if($schedule->asesor_id !== $asesor->id, 403);
    abort_if(!in_array($slot, [1, 2]), 404);

    $col = "foto_dokumentasi_{$slot}";
    if ($schedule->$col) {
        Storage::disk('private')->delete($schedule->$col);
        $schedule->update([$col => null]);
    }

    return back()->with('success', "Foto dokumentasi {$slot} berhasil dihapus.");

}

public function previewFotoDokumentasi(Schedule $schedule, int $slot)
{
    $asesor = auth()->user()->asesor;
    abort_if($schedule->asesor_id !== $asesor->id, 403);
    abort_if(!in_array($slot, [1, 2]), 404);

    $col  = "foto_dokumentasi_{$slot}";
    $path = $schedule->$col;
    abort_unless($path && Storage::disk('private')->exists($path), 404);

    return response(Storage::disk('private')->get($path), 200, [
        'Content-Type' => Storage::disk('private')->mimeType($path),
    ]);
}
}