<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminPraAsesmenController extends Controller
{
    /**
     * List asesi KOLEKTIF yang sudah diverifikasi TUK, menunggu penetapan biaya Admin
     * Grouped by BATCH
     */
    public function index()
    {
        // Mandiri — semua status relevan (bukan hanya data_completed)
        $mandiri = Asesmen::with(['user', 'tuk', 'skema', 'aplsatu', 'apldua', 'frak01', 'frak04'])
            ->where('is_collective', false)
            ->whereNotIn('status', ['certified', 'assessed', 'scheduled', 'registered']) // sembunyikan yang sudah selesai atau belum mulai
            ->orderBy('created_at')
            ->get();

        // Kolektif — grouped by batch, semua status
        $batches = Asesmen::with(['user', 'tuk', 'skema', 'aplsatu', 'apldua', 'frak01', 'frak04'])
            ->where('is_collective', true)
            ->whereNotIn('status', ['certified', 'assessed', 'scheduled', 'registered'])
            ->orderBy('created_at')
            ->get()
            ->groupBy('collective_batch_id');

        return view('admin.pra-asesmen.index', compact('mandiri', 'batches'));
    }

    /**
     * Show batch detail untuk penetapan biaya
     */
    public function show(Asesmen $asesmen)
    {
        abort_if($asesmen->status !== 'data_completed', 404, 'Asesi ini tidak dalam status data_completed.');

        $asesmen->load(['user', 'tuk', 'skema']);

        return view('admin.pra-asesmen.show', compact('asesmen'));
    }

    /**
     * Process - Single verification (backup only) - UPDATED
     */
    public function process(Request $request, Asesmen $asesmen)
    {
        // Status bisa data_completed (baru daftar) — tidak perlu abort
        // Cukup cek sudah pernah distart atau belum
        if ($asesmen->admin_started_at) {
            return redirect()->route('admin.pra-asesmen.index')
                ->with('warning', "Asesmen {$asesmen->full_name} sudah pernah dimulai sebelumnya.");
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $asesmen->update([
            'status'           => 'asesmen_started',
            'admin_started_by' => auth()->id(),
            'admin_started_at' => now(),
        ]);

        Log::info("Admin #" . auth()->id() . " memulai asesmen untuk Asesmen #{$asesmen->id}");

        return redirect()->route('admin.pra-asesmen.index')
            ->with('success', "Asesmen untuk {$asesmen->full_name} berhasil dimulai!");
    }
    
    /**
     * Batch - Tetapkan biaya untuk seluruh batch kolektif dengan input nominal langsung
     */
    public function processBatch(Request $request)
    {

        $request->validate([
            'batch_id' => 'required|string',
            'notes'    => 'nullable|string|max:1000',
        ]);

        $asesmens = Asesmen::where('collective_batch_id', $request->batch_id)
            ->where('status', 'data_completed')
            ->get();

        abort_if($asesmens->isEmpty(), 422, 'Tidak ada asesi yang bisa diproses dalam batch ini.');

        DB::beginTransaction();
        try {
            foreach ($asesmens as $asesmen) {
                $asesmen->update([
                    'status'           => 'asesmen_started',
                    'admin_started_by' => auth()->id(),
                    'admin_started_at' => now(),
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch start asesmen error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        Log::info("Admin #{auth()->id()} memulai asesmen batch {$request->batch_id}: {$asesmens->count()} asesi.");

        return redirect()->route('admin.pra-asesmen.index')
            ->with('success', "{$asesmens->count()} asesi dalam batch {$request->batch_id} berhasil dimulai!");
    }

/**
 * Show batch detail untuk penetapan biaya
 */
    public function showBatch(string $batchId)
    {
        $asesmens = Asesmen::with(['user', 'tuk', 'skema'])
            ->where('collective_batch_id', $batchId)
            ->get();

        abort_if($asesmens->isEmpty(), 404, 'Batch tidak ditemukan atau sudah diproses.');

        $firstBatch = $asesmens->first();

        return view('admin.pra-asesmen.batch-show', compact('batchId', 'asesmens', 'firstBatch'));
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
        return redirect()->route('admin.pra-asesmen.index')
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

        return redirect()->route('admin.pra-asesmen.index')
            ->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Batch fee setup error: ' . $e->getMessage());

        return redirect()->back()
            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

}