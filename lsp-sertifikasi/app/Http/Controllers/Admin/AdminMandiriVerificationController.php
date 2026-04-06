<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\Tuk;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminMandiriVerificationController extends Controller
{
    /**
     * List asesi mandiri yang perlu diverifikasi
     */
    public function index()
    {
        $asesmens = Asesmen::with(['user', 'skema'])
            ->where('is_collective', false)
            ->where('status', 'data_completed')
            ->whereNull('admin_verified_at')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.mandiri.index', compact('asesmens'));
    }

    /**
     * Show detail untuk verifikasi
     */
    public function show(Asesmen $asesmen)
    {
        if ($asesmen->is_collective) {
            return redirect()->route('admin.mandiri.verifications')
                ->with('error', 'Asesi kolektif diverifikasi melalui jalur berbeda.');
        }

        if ($asesmen->admin_verified_at) {
            return redirect()->route('admin.mandiri.verifications')
                ->with('error', 'Asesi ini sudah diverifikasi.');
        }

        $asesmen->load(['user', 'skema']);

        return view('admin.mandiri.verify', compact('asesmen'));
    }

    /**
     * Process verifikasi dan penetapan biaya
     */
    public function process(Request $request, Asesmen $asesmen)
    {
        if ($asesmen->is_collective) {
            return redirect()->route('admin.mandiri.verifications')
                ->with('error', 'Asesi kolektif tidak bisa diproses di sini.');
        }
 
        $request->validate([
            'fee_amount' => 'required|numeric|min:0',
            'notes'      => 'nullable|string',
        ]);
 
        // Verifikasi biodata + tetapkan biaya → status TETAP data_completed
        // (bukan 'verified' — itu flow lama Midtrans)
        // Asesi selanjutnya upload bukti bayar, bendahara verifikasi
        $asesmen->update([
            'fee_amount'        => $request->fee_amount,
            'admin_verified_by' => auth()->id(),
            'admin_verified_at' => now(),
            'status'            => 'data_completed', // ← FIX: bukan 'verified'
        ]);
 
        Log::info("Admin verified biodata mandiri Asesmen #{$asesmen->id}: Rp {$request->fee_amount}");
 
        return redirect()->route('admin.mandiri.verifications')
            ->with('success', 'Biodata asesi terverifikasi! Biaya Rp ' . number_format($request->fee_amount, 0, ',', '.') . ' ditetapkan. Asesi sekarang bisa upload bukti pembayaran.');
    }

    /**
     * List asesi mandiri yang sudah verified, perlu di-assign ke TUK
     */
    public function assignmentIndex()
    {
        $asesmens = Asesmen::with(['user', 'skema', 'assignedTuk'])
            ->where('is_collective', false)
            ->where('status', 'verified')
            ->whereNull('assigned_tuk_id')
            ->orderBy('admin_verified_at', 'asc')
            ->get();

        // ✅ FIXED: Get schedules with asesmens count
        $tuksWithSchedules = Tuk::where('is_active', true)
            ->get()
            ->map(function($tuk) {
                $allSchedules = Schedule::where('tuk_id', $tuk->id)
                    ->where('assessment_date', '>=', now())
                    ->with(['skema', 'asesmens']) // ✅ Load asesmens
                    ->orderBy('assessment_date', 'asc')
                    ->get();

                $schedulesBySkema = $allSchedules->groupBy('skema_id');
                $skemas = $allSchedules->pluck('skema')->unique('id')->values();

                return [
                    'tuk' => $tuk,
                    'schedules' => $allSchedules,
                    'schedules_by_skema' => $schedulesBySkema,
                    'skemas' => $skemas
                ];
            })
            ->filter(function($tukData) {
                return $tukData['schedules']->isNotEmpty();
            });

        return view('admin.mandiri.assignment', compact('asesmens', 'tuksWithSchedules'));
    }

    /**
     * Assign asesi ke TUK dan Schedule - FIXED
     */
    public function assignToTuk(Request $request, Asesmen $asesmen)
    {
        $request->validate([
            'tuk_id' => 'required|exists:tuks,id',
            'schedule_id' => 'required|exists:schedules,id',
            'notes' => 'nullable|string',
        ]);

        $tuk = Tuk::findOrFail($request->tuk_id);
        $schedule = Schedule::with('tuk', 'skema')->findOrFail($request->schedule_id);

        // Validate schedule belongs to the selected TUK
        if ($schedule->tuk_id != $request->tuk_id) {
            return redirect()->back()
                ->with('error', 'Jadwal yang dipilih tidak sesuai dengan TUK yang dipilih!');
        }

        // Validate schedule is for the same skema
        if ($schedule->skema_id != $asesmen->skema_id) {
            return redirect()->back()
                ->with('error', 'Jadwal yang dipilih untuk skema yang berbeda!');
        }

        DB::beginTransaction();
        try {
            // ✅ UPDATE: Assign asesi ke jadwal yang sudah ada
            $asesmen->update([
                'tuk_id' => $tuk->id,
                'assigned_tuk_id' => $tuk->id,
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
                'schedule_id' => $schedule->id, // ✅ Link ke jadwal existing
                'status' => 'verified', // ✅ Status tetap verified, bisa bayar
            ]);

            DB::commit();

            Log::info("Admin assigned Asesmen #{$asesmen->id} to TUK #{$tuk->id} with Schedule #{$schedule->id}. Notes: {$request->notes}");

            return redirect()->route('admin.mandiri.assignment')
                ->with('success', "Asesi {$asesmen->full_name} berhasil di-assign ke TUK {$tuk->name} pada jadwal " . $schedule->assessment_date->format('d/m/Y') . "!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Get TUK schedules by skema - FIXED
     */
    public function getTukSchedules($tukId, $skemaId)
    {
        try {
            $schedules = Schedule::where('tuk_id', $tukId)
                ->where('skema_id', $skemaId)
                ->where('assessment_date', '>=', now())
                ->with(['asesmens', 'skema']) // ✅ Load all asesmens in this schedule
                ->orderBy('assessment_date', 'asc')
                ->get()
                ->map(function($schedule) {
                    return [
                        'id' => $schedule->id,
                        'assessment_date' => $schedule->assessment_date->format('Y-m-d'),
                        'assessment_date_formatted' => $schedule->assessment_date->format('d/m/Y'),
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'location' => $schedule->location,
                        'skema_name' => $schedule->skema->name,
                        'asesmens_count' => $schedule->asesmens->count(), // ✅ Count asesmens
                        'asesmens_names' => $schedule->asesmens->pluck('full_name')->join(', '), // ✅ List names
                    ];
                });

            return response()->json([
                'success' => true,
                'schedules' => $schedules
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getTukSchedules: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}