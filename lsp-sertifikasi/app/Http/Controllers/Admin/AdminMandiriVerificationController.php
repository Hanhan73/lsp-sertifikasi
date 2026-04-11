<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\Tuk;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        // Ambil semua kolektif, group by full_name lowercase
        // Matching by nama + skema_id supaya tidak salah flag kalau ada nama sama tapi skema beda
        $kolektifByNama = Asesmen::where('is_collective', true)
            ->whereNotNull('collective_batch_id')
            ->whereNotNull('full_name')
            ->with('tuk')
            ->get()
            ->groupBy(fn($a) => strtolower(trim($a->full_name)));

        $asesmens->each(function ($a) use ($kolektifByNama) {
            $key     = strtolower(trim($a->full_name ?? ''));
            $matches = $kolektifByNama->get($key, collect());

            // Prioritas: nama sama + skema sama
            $match = $matches->firstWhere('skema_id', $a->skema_id) ?? $matches->first();

            $a->_kolektif_batch = $match?->collective_batch_id ?? null;
            $a->_kolektif_tuk   = $match?->tuk?->name ?? null;
        });

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

        $asesmen->update([
            'fee_amount'        => $request->fee_amount,
            'admin_verified_by' => auth()->id(),
            'admin_verified_at' => now(),
            'status'            => 'data_completed',
        ]);

        Log::info("Admin verified biodata mandiri Asesmen #{$asesmen->id}: Rp {$request->fee_amount}");

        return redirect()->route('admin.mandiri.verifications')
            ->with('success', 'Biodata asesi terverifikasi! Biaya Rp ' . number_format($request->fee_amount, 0, ',', '.') . ' ditetapkan. Asesi sekarang bisa upload bukti pembayaran.');
    }

    /**
     * Hapus akun mandiri (beserta user-nya)
     */
    public function destroyMandiri(Asesmen $asesmen)
    {
        if ($asesmen->is_collective) {
            return response()->json([
                'success' => false,
                'message' => 'Asesi kolektif tidak bisa dihapus lewat sini.',
            ], 422);
        }

        if (in_array($asesmen->status, ['certified', 'assessed', 'asesmen_started'])) {
            return response()->json([
                'success' => false,
                'message' => 'Asesi dengan status ' . $asesmen->status_label . ' tidak bisa dihapus.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $nama = $asesmen->full_name ?? $asesmen->user?->name ?? 'Unknown';

            // Hapus file storage
            foreach (['photo_path', 'ktp_path', 'document_path', 'pre_assessment_file'] as $col) {
                if ($asesmen->$col) {
                    Storage::disk('public')->delete($asesmen->$col);
                }
            }

            $user = $asesmen->user;
            $asesmen->delete();
            if ($user) {
                $user->delete();
            }

            DB::commit();

            Log::info("[ADMIN] Hapus akun mandiri: {$nama} (Asesmen #{$asesmen->id}) oleh Admin #" . auth()->id());

            return response()->json([
                'success' => true,
                'message' => "Akun mandiri {$nama} berhasil dihapus.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error destroyMandiri: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
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

        $tuksWithSchedules = Tuk::where('is_active', true)
            ->get()
            ->map(function ($tuk) {
                $allSchedules = Schedule::where('tuk_id', $tuk->id)
                    ->where('assessment_date', '>=', now())
                    ->with(['skema', 'asesmens'])
                    ->orderBy('assessment_date', 'asc')
                    ->get();

                $schedulesBySkema = $allSchedules->groupBy('skema_id');
                $skemas = $allSchedules->pluck('skema')->unique('id')->values();

                return [
                    'tuk'              => $tuk,
                    'schedules'        => $allSchedules,
                    'schedules_by_skema' => $schedulesBySkema,
                    'skemas'           => $skemas,
                ];
            })
            ->filter(fn($tukData) => $tukData['schedules']->isNotEmpty());

        return view('admin.mandiri.assignment', compact('asesmens', 'tuksWithSchedules'));
    }

    /**
     * Assign asesi ke TUK dan Schedule
     */
    public function assignToTuk(Request $request, Asesmen $asesmen)
    {
        $request->validate([
            'tuk_id'      => 'required|exists:tuks,id',
            'schedule_id' => 'required|exists:schedules,id',
            'notes'       => 'nullable|string',
        ]);

        $tuk      = Tuk::findOrFail($request->tuk_id);
        $schedule = Schedule::with('tuk', 'skema')->findOrFail($request->schedule_id);

        if ($schedule->tuk_id != $request->tuk_id) {
            return redirect()->back()
                ->with('error', 'Jadwal yang dipilih tidak sesuai dengan TUK yang dipilih!');
        }

        if ($schedule->skema_id != $asesmen->skema_id) {
            return redirect()->back()
                ->with('error', 'Jadwal yang dipilih untuk skema yang berbeda!');
        }

        DB::beginTransaction();
        try {
            $asesmen->update([
                'tuk_id'          => $tuk->id,
                'assigned_tuk_id' => $tuk->id,
                'assigned_at'     => now(),
                'assigned_by'     => auth()->id(),
                'schedule_id'     => $schedule->id,
                'status'          => 'verified',
            ]);

            DB::commit();

            Log::info("Admin assigned Asesmen #{$asesmen->id} to TUK #{$tuk->id} with Schedule #{$schedule->id}.");

            return redirect()->route('admin.mandiri.assignment')
                ->with('success', "Asesi {$asesmen->full_name} berhasil di-assign ke TUK {$tuk->name} pada jadwal " . $schedule->assessment_date->format('d/m/Y') . "!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get TUK schedules by skema (AJAX)
     */
    public function getTukSchedules($tukId, $skemaId)
    {
        try {
            $schedules = Schedule::where('tuk_id', $tukId)
                ->where('skema_id', $skemaId)
                ->where('assessment_date', '>=', now())
                ->with(['asesmens', 'skema'])
                ->orderBy('assessment_date', 'asc')
                ->get()
                ->map(fn($schedule) => [
                    'id'                        => $schedule->id,
                    'assessment_date'           => $schedule->assessment_date->format('Y-m-d'),
                    'assessment_date_formatted' => $schedule->assessment_date->format('d/m/Y'),
                    'start_time'                => $schedule->start_time,
                    'end_time'                  => $schedule->end_time,
                    'location'                  => $schedule->location,
                    'skema_name'                => $schedule->skema->name,
                    'asesmens_count'            => $schedule->asesmens->count(),
                    'asesmens_names'            => $schedule->asesmens->pluck('full_name')->join(', '),
                ]);

            return response()->json(['success' => true, 'schedules' => $schedules]);

        } catch (\Exception $e) {
            Log::error('Error in getTukSchedules: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}