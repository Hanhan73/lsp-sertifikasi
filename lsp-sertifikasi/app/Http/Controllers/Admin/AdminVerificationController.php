<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminVerificationController extends Controller
{
    /**
     * List asesi KOLEKTIF yang sudah diverifikasi TUK, menunggu penetapan biaya Admin
     * Grouped by BATCH
     */
    public function index()
    {
        // Get all collective asesmens yang perlu penetapan biaya, grouped by batch
        $batches = Asesmen::with(['user', 'tuk', 'skema', 'tukVerifier'])
            ->where('is_collective', true)
            ->whereNotNull('tuk_verified_at')
            ->whereNull('admin_verified_at')
            ->where('status', 'data_completed')
            ->get()
            ->groupBy('collective_batch_id');

        return view('admin.verifications.index', compact('batches'));
    }

    /**
     * Show batch detail untuk penetapan biaya
     */
    public function show(Asesmen $asesmen)
    {
        // Redirect ke index jika bukan kolektif
        if (!$asesmen->is_collective) {
            return redirect()->route('admin.mandiri.verifications')
                ->with('error', 'Asesi mandiri diverifikasi melalui jalur berbeda.');
        }

        if (!$asesmen->tuk_verified_at) {
            return redirect()->route('admin.verifications')
                ->with('error', 'Asesi ini belum diverifikasi oleh TUK.');
        }

        if ($asesmen->admin_verified_at) {
            return redirect()->route('admin.verifications')
                ->with('error', 'Biaya untuk asesi ini sudah ditetapkan.');
        }

        $asesmen->load(['user', 'tuk', 'skema', 'tukVerifier']);

        return view('admin.verifications.show', compact('asesmen'));
    }

    /**
     * Process - Single verification (backup only) - UPDATED
     */
    public function process(Request $request, Asesmen $asesmen)
    {
        if (!$asesmen->tuk_verified_at) {
            return redirect()->route('admin.verifications')
                ->with('error', 'Asesi ini belum diverifikasi oleh TUK.');
        }

        if (!$asesmen->is_collective) {
            return redirect()->route('admin.verifications')
                ->with('error', 'Asesi mandiri tidak memerlukan penetapan biaya manual.');
        }

        $request->validate([
            'fee_amount' => 'required|numeric|min:0',
            'phase_1_amount' => 'nullable|numeric|min:0', // UPDATED: Input nominal langsung
            'notes' => 'nullable|string',
        ]);

        $feeAmount = $request->fee_amount;
        
        $updateData = [
            'fee_amount' => $feeAmount,
            'admin_verified_by' => auth()->id(),
            'admin_verified_at' => now(),
            'status' => 'verified',
        ];

        // UPDATED: Calculate from nominal input
        if ($asesmen->payment_phases === 'two_phase') {
            $phase1Amount = $request->phase_1_amount ?? ($feeAmount / 2);
            $phase2Amount = $feeAmount - $phase1Amount;
            
            // Validate
            if ($phase1Amount > $feeAmount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Fase 1 tidak boleh lebih besar dari total biaya!');
            }
            
            $updateData['phase_1_amount'] = $phase1Amount;
            $updateData['phase_2_amount'] = $phase2Amount;

            Log::info("Admin set 2-phase fee for Asesmen #{$asesmen->id}: Phase 1: Rp {$phase1Amount}, Phase 2: Rp {$phase2Amount}");
        } else {
            Log::info("Admin set single-phase fee for Asesmen #{$asesmen->id}: Rp {$feeAmount}");
        }

        $asesmen->update($updateData);

        if ($request->notes) {
            Log::info("Admin fee setup for Asesmen #{$asesmen->id}: Rp {$feeAmount}. Notes: {$request->notes}");
        }

        $message = 'Biaya berhasil ditetapkan! Rp ' . number_format($feeAmount, 0, ',', '.');
        
        if ($asesmen->payment_phases === 'two_phase') {
            $message .= ' (Fase 1: Rp ' . number_format($phase1Amount, 0, ',', '.') . ', Fase 2: Rp ' . number_format($phase2Amount, 0, ',', '.') . ')';
        }

        return redirect()->route('admin.verifications')
            ->with('success', $message);
    }
    
    /**
     * Batch - Tetapkan biaya untuk seluruh batch kolektif dengan input nominal langsung
     */
    public function processBatch(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string',
            'fee_calculation_mode' => 'required|in:per_person,total,per_person_with_training',
            'fee_amount' => 'required|numeric|min:0',
            'training_fee' => 'nullable|numeric|min:0',
            'phase_1_amount' => 'nullable|numeric|min:0', // UPDATED: Input nominal langsung
            'notes' => 'nullable|string',
        ]);

        $asesmens = Asesmen::where('collective_batch_id', $request->batch_id)
            ->where('is_collective', true)
            ->whereNotNull('tuk_verified_at')
            ->whereNull('admin_verified_at')
            ->get();

        if ($asesmens->isEmpty()) {
            return redirect()->route('admin.verifications')
                ->with('error', 'Tidak ada asesi yang perlu ditetapkan biaya dalam batch ini.');
        }

        $firstAsesmen = $asesmens->first();
        $paymentPhases = $firstAsesmen->payment_phases;
        $count = $asesmens->count();
        $trainingCount = $asesmens->where('training_flag', true)->count();

        // Calculate fee per person based on mode
        $feePerPerson = 0;
        
        switch ($request->fee_calculation_mode) {
            case 'per_person':
                $feePerPerson = $request->fee_amount;
                break;
                
            case 'total':
                $feePerPerson = $request->fee_amount / $count;
                break;
                
            case 'per_person_with_training':
                $feePerPerson = $request->fee_amount;
                break;
        }

        $totalAmount = 0;

        DB::beginTransaction();
        try {
            foreach ($asesmens as $asesmen) {
                // Adjust fee if with training mode
                $finalFee = $feePerPerson;
                
                if ($request->fee_calculation_mode === 'per_person_with_training' && $asesmen->training_flag) {
                    $trainingFee = $request->training_fee ?? 1500000;
                    $finalFee += $trainingFee;
                }

                $updateData = [
                    'fee_amount' => $finalFee,
                    'admin_verified_by' => auth()->id(),
                    'admin_verified_at' => now(),
                    'status' => 'verified',
                ];

                // UPDATED: If two_phase, calculate amounts from input nominal
                if ($paymentPhases === 'two_phase') {
                    $phase1Amount = $request->phase_1_amount ?? ($finalFee / 2); // Default 50% jika tidak diisi
                    $phase2Amount = $finalFee - $phase1Amount;
                    
                    // Validate
                    if ($phase1Amount > $finalFee) {
                        throw new \Exception("Fase 1 tidak boleh lebih besar dari total biaya!");
                    }
                    
                    if ($phase1Amount < 0 || $phase2Amount < 0) {
                        throw new \Exception("Nominal fase tidak boleh negatif!");
                    }
                    
                    $updateData['phase_1_amount'] = $phase1Amount;
                    $updateData['phase_2_amount'] = $phase2Amount;
                }

                $asesmen->update($updateData);
                $totalAmount += $finalFee;
            }

            DB::commit();

            // Detailed log
            $logMessage = "Admin batch fee setup for {$request->batch_id}: {$count} asesmens, Mode: {$request->fee_calculation_mode}, Total: Rp {$totalAmount}";
            if ($paymentPhases === 'two_phase') {
                $phase1Amt = $request->phase_1_amount ?? ($feePerPerson / 2);
                $phase2Amt = $feePerPerson - $phase1Amt;
                $logMessage .= " (Phase 1: Rp " . number_format($phase1Amt, 0, ',', '.') . ", Phase 2: Rp " . number_format($phase2Amt, 0, ',', '.') . ")";
            }
            if ($request->notes) {
                $logMessage .= ". Notes: {$request->notes}";
            }
            Log::info($logMessage);

            $message = "Batch berhasil ditetapkan biaya! {$count} asesi dengan total biaya Rp " . number_format($totalAmount, 0, ',', '.');
            
            if ($paymentPhases === 'two_phase') {
                $phase1Amt = $request->phase_1_amount ?? ($feePerPerson / 2);
                $phase2Amt = $feePerPerson - $phase1Amt;
                $message .= ' (Fase 1: Rp ' . number_format($phase1Amt, 0, ',', '.') . ', Fase 2: Rp ' . number_format($phase2Amt, 0, ',', '.') . ')';
            }

            if ($request->fee_calculation_mode === 'per_person_with_training' && $trainingCount > 0) {
                $message .= ". {$trainingCount} peserta dengan pelatihan.";
            }

            return redirect()->route('admin.verifications')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch fee setup error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

/**
 * Show batch detail untuk penetapan biaya
 */
public function showBatch($batchId)
{
    $asesmens = Asesmen::with(['user', 'tuk', 'skema'])
        ->where('collective_batch_id', $batchId)
        ->where('is_collective', true)
        ->whereNotNull('tuk_verified_at')
        ->whereNull('admin_verified_at')
        ->get();

    if ($asesmens->isEmpty()) {
        return redirect()->route('admin.verifications')
            ->with('error', 'Batch tidak ditemukan atau sudah diverifikasi');
    }

    $firstBatch = $asesmens->first();
    $trainingCount = $asesmens->where('training_flag', true)->count();
    $noTrainingCount = $asesmens->where('training_flag', false)->count();
    $paymentPhases = $firstBatch->payment_phases;

    return view('admin.verifications.batch-show', compact(
        'batchId',
        'asesmens',
        'firstBatch',
        'trainingCount',
        'noTrainingCount',
        'paymentPhases'
    ));
}

/**
 * Process batch fee - SIMPLIFIED
 */
public function processBatchFee(Request $request)
{
    $request->validate([
        'batch_id' => 'required|string',
        'calculation_mode' => 'required|in:per_person,total',
        'asesmen_fee' => 'required|numeric|min:0',
        'training_fee' => 'nullable|numeric|min:0',
        'phase_1_amount' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string',
    ]);

    $asesmens = Asesmen::where('collective_batch_id', $request->batch_id)
        ->where('is_collective', true)
        ->whereNotNull('tuk_verified_at')
        ->whereNull('admin_verified_at')
        ->get();

    if ($asesmens->isEmpty()) {
        return redirect()->route('admin.verifications')
            ->with('error', 'Tidak ada asesi yang perlu ditetapkan biaya');
    }

    $firstBatch = $asesmens->first();
    $paymentPhases = $firstBatch->payment_phases;
    $count = $asesmens->count();
    $trainingFee = $request->training_fee ?? 1500000;

    // Calculate fee per person
    if ($request->calculation_mode === 'per_person') {
        $feePerPerson = $request->asesmen_fee;
    } else { // total
        $feePerPerson = $request->asesmen_fee / $count;
    }

    DB::beginTransaction();
    try {
        $totalAmount = 0;

        foreach ($asesmens as $asesmen) {
            // Final fee = asesmen + training (if applicable)
            $finalFee = $feePerPerson;
            if ($asesmen->training_flag) {
                $finalFee += $trainingFee;
            }

            $updateData = [
                'fee_amount' => $finalFee,
                'admin_verified_by' => auth()->id(),
                'admin_verified_at' => now(),
                'status' => 'verified',
            ];

            // Calculate phases if two_phase
            if ($paymentPhases === 'two_phase') {
                $phase1Amount = $request->phase_1_amount ?? ($feePerPerson / 2);
                $phase2Amount = $finalFee - $phase1Amount;

                if ($phase1Amount > $finalFee || $phase1Amount < 0 || $phase2Amount < 0) {
                    throw new \Exception("Nominal fase tidak valid untuk peserta {$asesmen->full_name}!");
                }

                $updateData['phase_1_amount'] = $phase1Amount;
                $updateData['phase_2_amount'] = $phase2Amount;
            }

            $asesmen->update($updateData);
            $totalAmount += $finalFee;
        }

        DB::commit();

        Log::info("Batch fee setup for {$request->batch_id}: {$count} asesmens, Total: Rp {$totalAmount}. Notes: {$request->notes}");

        $message = "Batch berhasil ditetapkan biaya! {$count} asesi dengan total biaya Rp " . number_format($totalAmount, 0, ',', '.');

        return redirect()->route('admin.verifications')
            ->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Batch fee setup error: ' . $e->getMessage());

        return redirect()->back()
            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

}