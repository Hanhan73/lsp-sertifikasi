<?php

namespace App\Http\Controllers\Tuk;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TukVerificationController extends Controller
{
    /**
     * List asesi yang perlu diverifikasi TUK
     */
    public function index()
    {
        $tuk = auth()->user()->tuk;

        if (!$tuk) {
            abort(403, 'Akun TUK tidak ditemukan.');
        }

        $asesmens = Asesmen::with(['user', 'skema'])
            ->where('tuk_id', $tuk->id)
            ->where('status', 'data_completed')
            ->whereNull('tuk_verified_at')
            ->orderBy('created_at', 'asc')
            ->get();

        $pendingVerifications = $asesmens->count();

        return view('tuk.verifications.index', compact('asesmens', 'pendingVerifications', 'tuk'));
    }

    /**
     * Show detail asesi untuk verifikasi
     */
    public function show(Asesmen $asesmen)
    {
        $tuk = auth()->user()->tuk;

        if ($asesmen->tuk_id != $tuk->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        if ($asesmen->status !== 'data_completed') {
            return redirect()->route('tuk.verifications.index')
                ->with('error', 'Asesi ini tidak dalam status yang dapat diverifikasi.');
        }

        if ($asesmen->tuk_verified_at) {
            return redirect()->route('tuk.verifications.index')
                ->with('error', 'Asesi ini sudah diverifikasi.');
        }

        $asesmen->load(['user', 'skema']);

        return view('tuk.verifications.show', compact('asesmen', 'tuk'));
    }

    /**
     * Process verifikasi TUK untuk satu asesi
     *
     * Alur baru:
     * - Mandiri  → verified  (Admin LSP yang set biaya, lalu asesi bayar)
     * - Kolektif → verified  (biaya di-set Admin LSP, pembayaran manual oleh TUK — terpisah dari flow asesi)
     */
     public function process(Request $request, Asesmen $asesmen)
    {
        $tuk = auth()->user()->tuk;
 
        if ($asesmen->tuk_id != $tuk->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }
 
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);
 
        $updateData = [
            'tuk_verified_by'        => auth()->id(),
            'tuk_verified_at'        => now(),
            'tuk_verification_notes' => $request->notes,
            // Kolektif: tetap pakai flow yang ada (verified → scheduled langsung)
            // Mandiri: tidak melewati TUK verification, jadi ini seharusnya
            // tidak pernah dipanggil untuk mandiri. Tapi defensive tetap data_completed.
            'status' => $asesmen->is_collective ? 'verified' : 'data_completed',
        ];
 
        // Untuk mandiri: set fee dari skema jika belum di-set admin
        if (!$asesmen->is_collective && !$asesmen->fee_amount) {
            $updateData['fee_amount'] = $asesmen->skema->fee;
        }
 
        $asesmen->update($updateData);
 
        Log::info("TUK Verification for Asesmen #{$asesmen->id} by TUK {$tuk->name}.");
 
        return redirect()->route('tuk.verifications.index')
            ->with('success', 'Data asesi berhasil diverifikasi!');
    }

    /**
     * Batch verification untuk pendaftaran kolektif
     *
     * Semua asesi dalam batch langsung ke status 'verified'.
     * Untuk kolektif, tidak ada langkah pembayaran di flow utama asesi —
     * TUK membayar secara manual (TF/QRIS) di luar sistem.
     */
    public function processBatch(Request $request)
    {
        $tuk = auth()->user()->tuk;

        $request->validate([
            'batch_id' => 'required|string',
            'notes'    => 'nullable|string|max:1000',
        ]);

        $asesmens = Asesmen::where('collective_batch_id', $request->batch_id)
            ->where('tuk_id', $tuk->id)
            ->where('status', 'data_completed')
            ->whereNull('tuk_verified_at')
            ->get();

        if ($asesmens->isEmpty()) {
            return redirect()->route('tuk.verifications.index')
                ->with('error', 'Tidak ada asesi yang perlu diverifikasi dalam batch ini.');
        }

        $count = 0;
        foreach ($asesmens as $asesmen) {
            $asesmen->update([
                'tuk_verified_by'        => auth()->id(),
                'tuk_verified_at'        => now(),
                'tuk_verification_notes' => $request->notes,
                'status'                 => 'verified',
                // fee_amount akan di-set Admin LSP
            ]);
            $count++;
        }

        Log::info("TUK Batch Verification for {$request->batch_id}: {$count} asesmens by TUK {$tuk->name}.");

        return redirect()->route('tuk.verifications.index')
            ->with('success', "{$count} asesi berhasil diverifikasi! Selanjutnya Admin LSP akan menetapkan biaya dan jadwal asesmen.");
    }
}