<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminVerificationController extends Controller
{
    /**
     * List asesi yang sudah diverifikasi TUK, menunggu penetapan biaya Admin
     * UPDATED: Hanya tampilkan COLLECTIVE (mandiri auto-verified)
     */
    public function index()
    {
        // HANYA collective yang perlu admin verification untuk set fee
        $asesmens = Asesmen::with(['user', 'tuk', 'skema', 'tukVerifier'])
            ->where('is_collective', true) // 🔥 HANYA COLLECTIVE
            ->whereNotNull('tuk_verified_at') // Sudah diverifikasi TUK
            ->whereNull('admin_verified_at') // Belum ditetapkan biaya Admin
            ->where('status', 'data_completed')
            ->orderBy('tuk_verified_at', 'asc')
            ->get();

        return view('admin.verifications.index', compact('asesmens'));
    }

    /**
     * Show detail untuk penetapan biaya
     */
    public function show(Asesmen $asesmen)
    {
        // Pastikan ini collective
        if (!$asesmen->is_collective) {
            return redirect()->route('admin.verifications')
                ->with('error', 'Asesi mandiri tidak memerlukan penetapan biaya manual (sudah otomatis).');
        }

        // Pastikan sudah diverifikasi TUK
        if (!$asesmen->tuk_verified_at) {
            return redirect()->route('admin.verifications')
                ->with('error', 'Asesi ini belum diverifikasi oleh TUK.');
        }

        // Pastikan belum ditetapkan biaya Admin
        if ($asesmen->admin_verified_at) {
            return redirect()->route('admin.verifications')
                ->with('error', 'Biaya untuk asesi ini sudah ditetapkan.');
        }

        $asesmen->load(['user', 'tuk', 'skema', 'tukVerifier']);

        return view('admin.verifications.verify', compact('asesmen'));
    }

    /**
     * Process - Tetapkan biaya setelah TUK verifikasi
     * UPDATED: Handle payment phases (single / two_phase)
     */
    public function process(Request $request, Asesmen $asesmen)
    {
        // Validasi sudah diverifikasi TUK
        if (!$asesmen->tuk_verified_at) {
            return redirect()->route('admin.verifications')
                ->with('error', 'Asesi ini belum diverifikasi oleh TUK.');
        }

        // Validasi ini collective
        if (!$asesmen->is_collective) {
            return redirect()->route('admin.verifications')
                ->with('error', 'Asesi mandiri tidak memerlukan penetapan biaya manual.');
        }

        $request->validate([
            'fee_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $feeAmount = $request->fee_amount;
        
        // 🔥 HANDLE PAYMENT PHASES
        $updateData = [
            'fee_amount' => $feeAmount,
            'admin_verified_by' => auth()->id(),
            'admin_verified_at' => now(),
            'status' => 'verified',
        ];

        // Jika 2 fase, split fee
        if ($asesmen->payment_phases === 'two_phase') {
            $phase1Amount = $feeAmount / 2;
            $phase2Amount = $feeAmount / 2;
            
            $updateData['phase_1_amount'] = $phase1Amount;
            $updateData['phase_2_amount'] = $phase2Amount;

            Log::info("Admin set 2-phase fee for Asesmen #{$asesmen->id}: Phase 1: Rp {$phase1Amount}, Phase 2: Rp {$phase2Amount}");
        } else {
            // Single phase - full payment
            Log::info("Admin set single-phase fee for Asesmen #{$asesmen->id}: Rp {$feeAmount}");
        }

        $asesmen->update($updateData);

        // Log
        if ($request->notes) {
            Log::info("Admin fee setup for Asesmen #{$asesmen->id}: Rp {$feeAmount}. Notes: {$request->notes}");
        }

        $message = 'Biaya berhasil ditetapkan! Rp ' . number_format($feeAmount, 0, ',', '.');
        
        if ($asesmen->payment_phases === 'two_phase') {
            $message .= ' (2 Fase: ' . number_format($phase1Amount, 0, ',', '.') . ' + ' . number_format($phase2Amount, 0, ',', '.') . ')';
        }

        return redirect()->route('admin.verifications')
            ->with('success', $message);
    }

    /**
     * Batch - Tetapkan biaya untuk seluruh batch kolektif
     * UPDATED: Handle payment phases
     */
    public function processBatch(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string',
            'fee_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Get all asesmens in this batch yang sudah diverifikasi TUK
        $asesmens = Asesmen::where('collective_batch_id', $request->batch_id)
            ->where('is_collective', true) // Must be collective
            ->whereNotNull('tuk_verified_at') // Sudah diverifikasi TUK
            ->whereNull('admin_verified_at') // Belum ditetapkan biaya
            ->get();

        if ($asesmens->isEmpty()) {
            return redirect()->route('admin.verifications')
                ->with('error', 'Tidak ada asesi yang perlu ditetapkan biaya dalam batch ini. Pastikan TUK sudah memverifikasi semua peserta.');
        }

        $feeAmount = $request->fee_amount;
        $firstAsesmen = $asesmens->first();
        $paymentPhases = $firstAsesmen->payment_phases;

        // 🔥 HANDLE PAYMENT PHASES untuk batch
        $count = 0;
        foreach ($asesmens as $asesmen) {
            $updateData = [
                'fee_amount' => $feeAmount,
                'admin_verified_by' => auth()->id(),
                'admin_verified_at' => now(),
                'status' => 'verified',
            ];

            // Jika 2 fase, split fee
            if ($paymentPhases === 'two_phase') {
                $phase1Amount = $feeAmount / 2;
                $phase2Amount = $feeAmount / 2;
                
                $updateData['phase_1_amount'] = $phase1Amount;
                $updateData['phase_2_amount'] = $phase2Amount;
            }

            $asesmen->update($updateData);
            $count++;
        }

        $totalAmount = $feeAmount * $count;

        // Log batch
        $logMessage = "Admin batch fee setup for {$request->batch_id}: {$count} asesmens, Rp {$totalAmount} total";
        if ($paymentPhases === 'two_phase') {
            $logMessage .= " (2 Fase: " . ($feeAmount / 2) . " + " . ($feeAmount / 2) . " per peserta)";
        }
        if ($request->notes) {
            $logMessage .= ". Notes: {$request->notes}";
        }
        Log::info($logMessage);

        $message = "Batch berhasil ditetapkan biaya! {$count} asesi dengan total biaya Rp " . number_format($totalAmount, 0, ',', '.');
        
        if ($paymentPhases === 'two_phase') {
            $message .= ' (2 Fase: ' . number_format($feeAmount / 2, 0, ',', '.') . ' + ' . number_format($feeAmount / 2, 0, ',', '.') . ' per peserta)';
        }

        return redirect()->route('admin.verifications')
            ->with('success', $message);
    }
}