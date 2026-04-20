<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesor;
use App\Models\Schedule;
use App\Models\Tuk;
use App\Services\AsesorAssignmentService;
use App\Services\SkGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AsesorAssignmentController extends Controller
{
        public function __construct(
        private AsesorAssignmentService $service,
        private SkGeneratorService $skGenerator,   // ← tambah inject
    ) {}

    // =========================================================
    // INDEX
    // =========================================================

    public function index(Request $request)
    {
        $query = Schedule::with(['tuk', 'skema', 'asesor', 'asesmens'])
            ->where('assessment_date', '>=', now()->subDays(7));

        if ($tukId = $request->input('tuk_id')) {
            $query->where('tuk_id', $tukId);
        }

        match ($request->input('status')) {
            'assigned'   => $query->whereNotNull('asesor_id'),
            'unassigned' => $query->whereNull('asesor_id'),
            default      => null,
        };

        $schedules = $query->orderBy('assessment_date')->get();
        $tuks      = Tuk::orderBy('name')->get();

        $stats = [
            'total'      => Schedule::where('assessment_date', '>=', now())->count(),
            'assigned'   => Schedule::where('assessment_date', '>=', now())->whereNotNull('asesor_id')->count(),
            'unassigned' => Schedule::where('assessment_date', '>=', now())->whereNull('asesor_id')->count(),
        ];

        return view('admin.asesor-assignments.index', compact('schedules', 'tuks', 'stats'));
    }

    // =========================================================
    // AVAILABLE ASESORS (AJAX)
    // =========================================================

    public function availableAsesors(Schedule $schedule)
    {
        $asesors = $this->service->getAvailableAsesors($schedule);

        return response()->json([
            'success' => true,
            'asesors' => $asesors->map(fn($a) => [
                'id'         => $a->id,
                'nama'       => $a->nama,
                'no_reg_met' => $a->no_reg_met,
                'email'      => $a->email,
                'foto_url'   => $a->foto_url,
            ]),
        ]);
    }

    // =========================================================
    // ASSIGN
    // =========================================================

    public function assign(Request $request, Schedule $schedule)
        {
            $request->validate([
                'asesor_id' => 'required|exists:asesors,id',
                'notes'     => 'nullable|string|max:500',
            ]);

            try {
                $asesor = Asesor::findOrFail($request->asesor_id);

                if (!$this->service->assignAsesor($schedule, $asesor, $request->notes)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal menugaskan asesor.',
                    ], 500);
                }

                $schedule->refresh();

                if ($schedule->asesor_id !== $asesor->id) {
                    Log::error('[ASESOR-ASSIGN] Verification failed after assign', [
                        'schedule_id' => $schedule->id,
                        'expected'    => $asesor->id,
                        'actual'      => $schedule->asesor_id,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Assignment berhasil tapi verifikasi gagal. Silakan refresh.',
                    ], 500);
                }

                // ── Auto re-generate SK jika jadwal sudah approved ──────────
                if ($schedule->isApproved() && $schedule->hasSk()) {
                    try {
                        $skPath = $this->skGenerator->generate(
                            $schedule->load(['tuk', 'skema', 'asesor', 'asesmens', 'approvedBy'])
                        );
                        $schedule->update(['sk_path' => $skPath]);
                        Log::info("[SK-REGEN] SK di-generate ulang untuk jadwal #{$schedule->id} karena asesor diganti ke {$asesor->nama}");
                    } catch (\Exception $e) {
                        // Jangan gagalkan assign hanya karena SK gagal di-generate
                        Log::error("[SK-REGEN] Gagal re-generate SK untuk jadwal #{$schedule->id}: " . $e->getMessage());
                    }
                }
                // ─────────────────────────────────────────────────────────────

                return response()->json([
                    'success' => true,
                    'message' => "Asesor {$asesor->nama} berhasil ditugaskan!",
                    'data'    => [
                        'schedule_id' => $schedule->id,
                        'asesor_id'   => $asesor->id,
                        'asesor_nama' => $asesor->nama,
                    ],
                ]);

            } catch (\Exception $e) {
                Log::error('[ASESOR-ASSIGN] ' . $e->getMessage(), [
                    'schedule_id' => $schedule->id,
                    'asesor_id'   => $request->asesor_id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                ], 500);
            }
        }

    // =========================================================
    // UNASSIGN
    // =========================================================

    public function unassign(Request $request, Schedule $schedule)
    {
        $request->validate(['notes' => 'nullable|string|max:500']);

        if (!$schedule->asesor) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal ini tidak memiliki asesor.',
            ], 400);
        }

        $nama = $schedule->asesor->nama;

        if (!$this->service->unassignAsesor($schedule, $request->notes)) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan penugasan.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Penugasan asesor {$nama} berhasil dibatalkan.",
        ]);
    }

    // =========================================================
    // HISTORY (AJAX)
    // =========================================================

    public function history(Schedule $schedule)
    {
        $schedule->load(['assignmentHistories.asesor', 'assignmentHistories.assignedBy']);

        $html = view('admin.asesor-assignments.partials.history', compact('schedule'))->render();

        return response()->json(['success' => true, 'html' => $html]);
    }
}