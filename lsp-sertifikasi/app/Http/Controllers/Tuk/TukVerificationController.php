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

        // Asesi dengan status data_completed yang belum diverifikasi TUK
        $asesmens = Asesmen::with(['user', 'skema'])
            ->where('tuk_id', $tuk->id)
            ->where('status', 'data_completed')
            ->whereNull('tuk_verified_at') // Belum diverifikasi TUK
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

        // Verify ownership
        if ($asesmen->tuk_id != $tuk->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        // Pastikan statusnya data_completed
        if ($asesmen->status !== 'data_completed') {
            return redirect()->route('tuk.verifications')
                ->with('error', 'Asesi ini tidak dalam status yang dapat diverifikasi.');
        }

        // Pastikan belum diverifikasi TUK
        if ($asesmen->tuk_verified_at) {
            return redirect()->route('tuk.verifications')
                ->with('error', 'Asesi ini sudah diverifikasi.');
        }

        $asesmen->load(['user', 'skema']);

        return view('tuk.verifications.show', compact('asesmen', 'tuk'));
    }

    /**
     * Process verifikasi TUK
     */
    public function process(Request $request, Asesmen $asesmen)
    {
        $tuk = auth()->user()->tuk;

        // Verify ownership
        if ($asesmen->tuk_id != $tuk->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $updateData = [
            'tuk_verified_by' => auth()->id(),
            'tuk_verified_at' => now(),
            'tuk_verification_notes' => $request->notes,
        ];

        // 🔥 Kalau MANDIRI → langsung verified
        if (!$asesmen->is_collective) {
            $updateData['status'] = 'verified';
            $updateData['fee_amount'] = $asesmen->skema->fee;
        } 

        $asesmen->update($updateData);

        // Log
        Log::info("TUK Verification for Asesmen #{$asesmen->id} by TUK {$tuk->name}. Notes: {$request->notes}");

        return redirect()->route('tuk.verifications')
            ->with('success', 'Data asesi berhasil diverifikasi!');
    }

    /**
     * Batch verification
     */
    public function processBatch(Request $request)
    {
        $tuk = auth()->user()->tuk;

        $request->validate([
            'batch_id' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Get all asesmens in this batch
        $asesmens = Asesmen::where('collective_batch_id', $request->batch_id)
            ->where('tuk_id', $tuk->id)
            ->where('status', 'data_completed')
            ->whereNull('tuk_verified_at')
            ->get();

        if ($asesmens->isEmpty()) {
            return redirect()->route('tuk.verifications')
                ->with('error', 'Tidak ada asesi yang perlu diverifikasi dalam batch ini.');
        }

        // Update all
        $count = 0;
        foreach ($asesmens as $asesmen) {
            $asesmen->update([
                'tuk_verified_by' => auth()->id(),
                'tuk_verified_at' => now(),
                'tuk_verification_notes' => $request->notes,
            ]);
            $count++;
        }

        // Log
        Log::info("TUK Batch Verification for {$request->batch_id}: {$count} asesmens by TUK {$tuk->name}. Notes: {$request->notes}");

        return redirect()->route('tuk.verifications')
            ->with('success', "Batch berhasil diverifikasi! {$count} asesi menunggu Admin LSP untuk menetapkan biaya.");
    }
}