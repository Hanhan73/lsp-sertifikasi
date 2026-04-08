<?php

namespace App\Http\Controllers\Asesi;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\DistribusiSoalObservasi;
use App\Models\JawabanObservasiAsesi;
use App\Models\SoalTeoriAsesi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Reflection;

class SoalAsesiController extends Controller
{
    // =========================================================================
    // SOAL TEORI — Halaman ujian
    // =========================================================================

    public function teoriMulai(Request $request): \Illuminate\Http\RedirectResponse
    {
        $asesmen = Asesmen::where('user_id', auth()->id())->firstOrFail();
    
        // Set started_at jika belum ada
        $belumMulai = SoalTeoriAsesi::where('asesmen_id', $asesmen->id)
            ->whereNull('started_at')
            ->exists();
    
        if ($belumMulai) {
            SoalTeoriAsesi::where('asesmen_id', $asesmen->id)
                ->update(['started_at' => now()]);
        }
    
        // Selalu redirect ke halaman ujian
        return redirect()->route('asesi.soal.teori.index');
    }
    
    // =========================================================================
    // FIX 2: app/Http/Controllers/Asesi/SoalAsesiController.php
    // Update teoriIndex() — parse Carbon + jangan redirect ke intro lagi
    // (biarkan asesi masuk langsung walau refresh, timer sudah jalan di DB)
    // =========================================================================
    
    public function teoriIndex(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $user    = auth()->user();
        $asesmen = Asesmen::where('user_id', $user->id)->firstOrFail();
    
        $soalAsesi = SoalTeoriAsesi::with('soalTeori')
            ->where('asesmen_id', $asesmen->id)
            ->orderBy('urutan')
            ->get();
    
        if ($soalAsesi->isEmpty()) {
            return redirect()->route('asesi.dashboard')
                ->with('info', 'Soal teori belum didistribusikan oleh Manajer Sertifikasi.');
        }
    
        $distribusi  = $soalAsesi->first()->distribusiSoalTeori;
        $durasi      = $distribusi->durasi_menit ?? 60;
        $sudahSubmit = $soalAsesi->every(fn($s) => $s->submitted_at !== null);
    
        // Parse Carbon dengan benar
        $startedAtRaw = $soalAsesi->whereNotNull('started_at')->min('started_at');
        $startedAt    = $startedAtRaw ? \Carbon\Carbon::parse($startedAtRaw) : null;
    
        // Belum mulai (belum klik tombol di intro) → redirect ke intro
        if (!$startedAt && !$sudahSubmit) {
            return redirect()->route('asesi.soal.teori.intro');
        }
    
        return view('asesi.soal.teori', compact(
            'asesmen', 'soalAsesi', 'distribusi', 'durasi', 'sudahSubmit', 'startedAt'
        ));
    }
    
    // =========================================================================
    // FIX 3: app/Http/Controllers/Asesi/SoalAsesiController.php
    // Update teoriIntro() — parse Carbon dengan benar
    // =========================================================================
    
    public function teoriIntro(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $user    = auth()->user();
        $asesmen = Asesmen::where('user_id', $user->id)->firstOrFail();
    
        $soalAsesi = SoalTeoriAsesi::where('asesmen_id', $asesmen->id)
            ->orderBy('urutan')
            ->get();
    
        if ($soalAsesi->isEmpty()) {
            return redirect()->route('asesi.dashboard')
                ->with('info', 'Soal teori belum didistribusikan oleh Manajer Sertifikasi.');
        }
    
        $distribusi  = $soalAsesi->first()->distribusiSoalTeori;
        $durasi      = $distribusi->durasi_menit ?? 60;
        $jumlahSoal  = $soalAsesi->count();
        $sudahSubmit = $soalAsesi->every(fn($s) => $s->submitted_at !== null);
    
        $startedAtRaw  = $soalAsesi->whereNotNull('started_at')->min('started_at');
        $startedAt     = $startedAtRaw  ? \Carbon\Carbon::parse($startedAtRaw)  : null;
    
        // Sudah mulai tapi belum submit → langsung ke halaman ujian
        if ($startedAt && !$sudahSubmit) {
            return redirect()->route('asesi.soal.teori.index');
        }
    
        // Data tambahan untuk state sudah submit
        $jumlahDijawab    = 0;
        $jumlahKosong     = 0;
        $distribusiJawaban = collect();
        $submittedAt      = null;
    
        if ($sudahSubmit) {
            $jumlahDijawab     = $soalAsesi->whereNotNull('jawaban')->count();
            $jumlahKosong      = $jumlahSoal - $jumlahDijawab;
            $distribusiJawaban = $soalAsesi->whereNotNull('jawaban')
                                        ->groupBy('jawaban')
                                        ->map->count();
    
            $submittedAtRaw = $soalAsesi->whereNotNull('submitted_at')->max('submitted_at');
            $submittedAt    = $submittedAtRaw ? \Carbon\Carbon::parse($submittedAtRaw) : null;
        }
    
        return view('asesi.soal.teori-intro', compact(
            'asesmen', 'durasi', 'jumlahSoal', 'startedAt', 'sudahSubmit',
            'jumlahDijawab', 'jumlahKosong', 'distribusiJawaban', 'submittedAt'
        ));
    }
    /**
     * Auto-save jawaban per soal (AJAX).
     */
    public function teoriSave(Request $request): JsonResponse
    {
        $request->validate([
            'soal_id' => 'required|integer',
            'jawaban' => 'nullable|in:a,b,c,d,e',
        ]);

        $asesmen = Asesmen::where('user_id', auth()->id())->firstOrFail();

        $soal = SoalTeoriAsesi::where('id', $request->soal_id)
            ->where('asesmen_id', $asesmen->id)
            ->whereNull('submitted_at')
            ->firstOrFail();

        $soal->update(['jawaban' => $request->jawaban]);

        // Hitung progress
        $total    = SoalTeoriAsesi::where('asesmen_id', $asesmen->id)->count();
        $answered = SoalTeoriAsesi::where('asesmen_id', $asesmen->id)->whereNotNull('jawaban')->count();

        return response()->json([
            'success'  => true,
            'answered' => $answered,
            'total'    => $total,
        ]);
    }

    /**
     * Submit ujian soal teori.
     */
    public function teoriSubmit(Request $request): JsonResponse
    {
        $asesmen = Asesmen::where('user_id', auth()->id())->firstOrFail();

        $soalAsesi = SoalTeoriAsesi::where('asesmen_id', $asesmen->id)
            ->whereNull('submitted_at')
            ->get();

        if ($soalAsesi->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Ujian sudah disubmit sebelumnya.'], 400);
        }

        SoalTeoriAsesi::where('asesmen_id', $asesmen->id)->update(['submitted_at' => now()]);

        $total    = SoalTeoriAsesi::where('asesmen_id', $asesmen->id)->count();
        $answered = SoalTeoriAsesi::where('asesmen_id', $asesmen->id)->whereNotNull('jawaban')->count();

        return response()->json([
            'success'  => true,
            'message'  => 'Ujian berhasil disubmit.',
            'answered' => $answered,
            'total'    => $total,
        ]);
    }
    

    // =========================================================================
    // SOAL OBSERVASI — Lihat paket dan upload GDrive
    // =========================================================================

    /**
     * Halaman lihat soal observasi dan upload link GDrive.
     */
    public function observasiIndex(): View|RedirectResponse
    {
        $user    = auth()->user();
        $asesmen = Asesmen::with(['schedule'])->where('user_id', $user->id)->firstOrFail();

        if (!$asesmen->schedule_id) {
            return redirect()->route('asesi.dashboard')
                ->with('info', 'Belum ada jadwal asesmen.');
        }

        $distribusiObservasi = DistribusiSoalObservasi::with([
            'soalObservasi',
            'paketSoalObservasi', // ← hanya paket yang dipilih manajer
        ])
        ->where('schedule_id', $asesmen->schedule_id)
        ->get();

        if ($distribusiObservasi->isEmpty()) {
            return redirect()->route('asesi.dashboard')
                ->with('info', 'Soal observasi belum didistribusikan.');
        }

        $jawabanMap = JawabanObservasiAsesi::where('asesmen_id', $asesmen->id)
            ->get()
            ->keyBy('paket_soal_observasi_id');

        return view('asesi.soal.observasi', compact('asesmen', 'distribusiObservasi', 'jawabanMap'));
    }

    /**
     * Simpan link GDrive untuk satu paket observasi (AJAX).
     */
    public function observasiSaveLink(Request $request): JsonResponse
    {
        $request->validate([
            'distribusi_id' => 'required|integer|exists:distribusi_soal_observasi,id',
            'paket_id'      => 'required|integer|exists:paket_soal_observasi,id',
            'gdrive_link'   => [
                'nullable',
                'string',
                'max:500',
                function ($attr, $value, $fail) {
                    if ($value && !preg_match('/^https?:\/\/(drive|docs)\.google\.com\//i', $value)) {
                        $fail('Link harus berupa URL Google Drive/Docs yang valid.');
                    }
                },
            ],
        ]);

        $asesmen = Asesmen::where('user_id', auth()->id())->firstOrFail();

        // Pastikan distribusi ini memang untuk schedule asesi ini
        $distribusi = DistribusiSoalObservasi::where('id', $request->distribusi_id)
            ->where('schedule_id', $asesmen->schedule_id)
            ->firstOrFail();

        $jawaban = JawabanObservasiAsesi::updateOrCreate(
            [
                'asesmen_id'                  => $asesmen->id,
                'paket_soal_observasi_id'     => $request->paket_id,
            ],
            [
                'distribusi_soal_observasi_id' => $request->distribusi_id,
                'gdrive_link'                  => $request->gdrive_link ?: null,
                'uploaded_at'                  => $request->gdrive_link ? now() : null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => $request->gdrive_link ? 'Link berhasil disimpan.' : 'Link dihapus.',
        ]);
    }

    public function downloadPaket(\App\Models\PaketSoalObservasi $paket): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $asesmen = Asesmen::where('user_id', auth()->id())->firstOrFail();
    
        // Pastikan paket ini memang untuk jadwal asesi ini
        $valid = DistribusiSoalObservasi::where('schedule_id', $asesmen->schedule_id)
            ->where('soal_observasi_id', $paket->soal_observasi_id)
            ->exists();
    
        abort_unless($valid, 403, 'Akses ditolak.');
    
        return \Illuminate\Support\Facades\Storage::disk('private')
            ->download($paket->file_path, $paket->file_name);
    }


    public function downloadLampiran(\App\Models\PaketSoalObservasi $paket): \Symfony\Component\HttpFoundation\StreamedResponse
{
    $asesmen = Asesmen::where('user_id', auth()->id())->firstOrFail();

    $valid = DistribusiSoalObservasi::where('schedule_id', $asesmen->schedule_id)
        ->where('soal_observasi_id', $paket->soal_observasi_id)
        ->exists();

    abort_unless($valid, 403, 'Akses ditolak.');
    abort_unless(
        $paket->lampiran_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($paket->lampiran_path),
        404, 'Lampiran tidak tersedia.'
    );

    return \Illuminate\Support\Facades\Storage::disk('private')
        ->download($paket->lampiran_path, $paket->lampiran_name);
}
}