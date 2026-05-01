<?php

namespace App\Http\Controllers\Asesi;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\FrAk03UmpanBalik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FrAk03Controller extends Controller
{
    // =========================================================================
    // ASESI — Tampilkan form
    // =========================================================================

    public function index()
    {
        $user    = auth()->user();
        $asesmen = Asesmen::with([
            'schedule.asesor',
            'skema',
            'frAk03',
            'soalTeoriAsesi',
            'schedule.distribusiSoalObservasi',
            'schedule.distribusiPortofolio',
            'schedule.hasilPortofolio',
        ])->where('user_id', $user->id)->firstOrFail();

        // Gate: belum boleh akses
        if (!$asesmen->canShowFrAk03()) {
            return redirect()->route('asesi.dashboard')
                ->with('info', 'Form umpan balik belum tersedia. Selesaikan semua tahapan asesmen terlebih dahulu.');
        }

        $frAk03 = $asesmen->frAk03;

        return view('asesi.frak03.form', compact('asesmen', 'frAk03'));
    }

    // =========================================================================
    // ASESI — Submit form
    // =========================================================================

    public function submit(Request $request)
    {
        $user    = auth()->user();
        $asesmen = Asesmen::with([
            'frAk03',
            'soalTeoriAsesi',
            'schedule.distribusiSoalObservasi',
            'schedule.distribusiPortofolio',
            'schedule.hasilPortofolio',
        ])->where('user_id', $user->id)->firstOrFail();

        if (!$asesmen->canShowFrAk03()) {
            return redirect()->route('asesi.dashboard')
                ->with('error', 'Akses ditolak.');
        }

        // Kalau sudah submit, tidak bisa submit lagi
        if ($asesmen->frAk03 && $asesmen->frAk03->isSubmitted()) {
            return redirect()->route('asesi.frak03.index')
                ->with('info', 'Umpan balik sudah pernah disubmit.');
        }

        $request->validate([
            'jawaban'        => 'required|array|size:10',
            'jawaban.*.jawaban' => 'required|in:ya,tidak',
            'jawaban.*.catatan' => 'nullable|string|max:500',
            'catatan_lain'   => 'nullable|string|max:1000',
        ], [
            'jawaban.required'           => 'Semua pertanyaan wajib dijawab.',
            'jawaban.size'               => 'Semua 10 pertanyaan harus dijawab.',
            'jawaban.*.jawaban.required' => 'Pilih Ya atau Tidak untuk setiap pertanyaan.',
            'jawaban.*.jawaban.in'       => 'Jawaban tidak valid.',
        ]);

        FrAk03UmpanBalik::updateOrCreate(
            ['asesmen_id' => $asesmen->id],
            [
                'schedule_id'  => $asesmen->schedule_id,
                'jawaban'      => $request->jawaban,
                'catatan_lain' => $request->catatan_lain,
                'submitted_at' => now(),
            ]
        );

        Log::info('[FR.AK.03] Asesi submit umpan balik', [
            'asesmen_id' => $asesmen->id,
            'user_id'    => $user->id,
        ]);

        return redirect()->route('asesi.dashboard')
            ->with('success', 'Umpan balik berhasil dikirim. Terima kasih!');
    }
}