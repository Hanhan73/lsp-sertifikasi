<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\Schedule;
use App\Models\Tuk;
use App\Models\Skema;
use App\Services\AsesorAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AdminScheduleController
 *
 * Admin membuat jadwal asesmen dan menugaskan asesor.
 * TUK hanya bisa melihat jadwal yang terkait dengan TUK mereka.
 */
class AdminScheduleController extends Controller
{
    public function __construct(
        private AsesorAssignmentService $assignmentService
    ) {}

    /**
     * Daftar semua asesi yang siap dijadwalkan (status: asesmen_started).
     */
    public function index()
    {
        // Asesi yang siap dijadwalkan
        $readyToSchedule = Asesmen::with(['user', 'tuk', 'skema'])
            ->where('status', 'asesmen_started')
            ->whereNull('schedule_id')
            ->orderBy('admin_started_at')
            ->get()
            ->groupBy('tuk_id'); // group by TUK untuk memudahkan pemilihan

        // Semua jadwal yang sudah dibuat
        $schedules = Schedule::with(['tuk', 'skema', 'asesor', 'asesmens.user'])
            ->orderBy('assessment_date', 'desc')
            ->paginate(20);

        $tuks   = Tuk::where('is_active', true)->orderBy('name')->get();
        $skemas = Skema::where('is_active', true)->orderBy('name')->get();

        return view('admin.schedules.index', compact('readyToSchedule', 'schedules', 'tuks', 'skemas'));
    }

    /**
     * Form buat jadwal baru.
     */
    public function create(Request $request)
    {
        // Bisa pre-select asesi dari query string ?asesmen_ids[]=1&asesmen_ids[]=2
        $selectedIds = $request->input('asesmen_ids', []);
        $selectedAsesmens = $selectedIds
            ? Asesmen::with(['tuk', 'skema'])->whereIn('id', $selectedIds)->get()
            : collect();

        $tuks   = Tuk::where('is_active', true)->orderBy('name')->get();
        $skemas = Skema::where('is_active', true)->orderBy('name')->get();

        // Asesi yang belum terjadwal dan sudah asesmen_started
        $availableAsesmens = Asesmen::with(['user', 'tuk', 'skema'])
            ->where('status', 'asesmen_started')
            ->whereNull('schedule_id')
            ->orderBy('full_name')
            ->get();

        return view('admin.schedules.create', compact(
            'selectedAsesmens',
            'availableAsesmens',
            'tuks',
            'skemas'
        ));
    }

    /**
     * Simpan jadwal baru + assign asesor sekaligus.
     */
    public function store(Request $request)
    {
        $request->validate([
            'asesmen_ids'     => 'required|array|min:1',
            'asesmen_ids.*'   => 'exists:asesmens,id',
            'tuk_id'          => 'required|exists:tuks,id',
            'asesor_id'       => 'nullable|exists:asesors,id',
            'assessment_date' => 'required|date|after_or_equal:today',
            'start_time'      => 'required',
            'end_time'        => 'required|after:start_time',
            'location'        => 'required|string|max:255',
            'notes'           => 'nullable|string',
        ]);

        // Validasi semua asesi punya skema sama
        $asesmens = Asesmen::whereIn('id', $request->asesmen_ids)
            ->where('status', 'asesmen_started')
            ->whereNull('schedule_id')
            ->get();

        if ($asesmens->count() !== count($request->asesmen_ids)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Beberapa asesi tidak valid atau sudah terjadwal.');
        }

        $skemaIds = $asesmens->pluck('skema_id')->unique();
        if ($skemaIds->count() > 1) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Asesi dalam satu jadwal harus memiliki skema yang sama.');
        }

        DB::beginTransaction();
        try {
            // Buat jadwal
            $schedule = Schedule::create([
                'tuk_id'          => $request->tuk_id,
                'skema_id'        => $skemaIds->first(),
                'assessment_date' => $request->assessment_date,
                'start_time'      => $request->start_time,
                'end_time'        => $request->end_time,
                'location'        => $request->location,
                'notes'           => $request->notes,
                'created_by'      => auth()->id(),
            ]);

            // Update setiap asesi
            foreach ($asesmens as $asesmen) {
                $asesmen->update([
                    'schedule_id' => $schedule->id,
                    'status'      => 'scheduled',
                ]);
            }

            // Assign asesor jika dipilih
            if ($request->asesor_id) {
                $asesor = \App\Models\Asesor::findOrFail($request->asesor_id);
                $this->assignmentService->assignAsesor($schedule, $asesor, 'Ditugaskan saat pembuatan jadwal');
            }

            DB::commit();

            Log::info("Admin #{auth()->id()} membuat jadwal #{$schedule->id} untuk {$asesmens->count()} asesi.");

            return redirect()->route('admin.schedules.index')
                ->with('success', "Jadwal berhasil dibuat untuk {$asesmens->count()} asesi!" .
                    ($request->asesor_id ? ' Asesor sudah ditugaskan.' : ' Asesor belum ditugaskan.'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin create schedule error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Detail jadwal.
     */
    public function show(Schedule $schedule)
    {
        $schedule->load(['tuk', 'skema', 'asesor', 'asesmens.user', 'asesmens.aplsatu', 'asesmens.apldua', 'asesmens.frak01']);

        return view('admin.schedules.show', compact('schedule'));
    }

    /**
     * Edit jadwal.
     */
    public function edit(Schedule $schedule)
    {
        $schedule->load(['tuk', 'skema', 'asesor', 'asesmens']);
        $tuks = Tuk::where('is_active', true)->orderBy('name')->get();

        return view('admin.schedules.edit', compact('schedule', 'tuks'));
    }

    /**
     * Update jadwal (AJAX).
     */
    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'assessment_date' => 'required|date',
            'start_time'      => 'required',
            'end_time'        => 'required|after:start_time',
            'location'        => 'required|string|max:255',
            'notes'           => 'nullable|string',
        ]);

        $schedule->update($request->only(['assessment_date', 'start_time', 'end_time', 'location', 'notes']));

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Jadwal berhasil diupdate!',
                'schedule' => [
                    'id'              => $schedule->id,
                    'assessment_date' => $schedule->assessment_date->format('d F Y'),
                    'start_time'      => $schedule->start_time,
                    'end_time'        => $schedule->end_time,
                    'location'        => $schedule->location,
                ],
            ]);
        }

        return redirect()->route('admin.schedules.show', $schedule)
            ->with('success', 'Jadwal berhasil diupdate!');
    }

    /**
     * Hapus jadwal — kembalikan asesi ke asesmen_started.
     */
    public function destroy(Schedule $schedule)
    {
        DB::beginTransaction();
        try {
            foreach ($schedule->asesmens as $asesmen) {
                $asesmen->update([
                    'schedule_id' => null,
                    'status'      => 'asesmen_started',
                ]);
            }
            $schedule->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Jadwal dihapus. Asesi dikembalikan ke status Asesmen Dimulai.']);
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * AJAX: Available asesors untuk jadwal tertentu (untuk assign/reassign).
     * Reuse dari AsesorAssignmentService.
     */
    public function availableAsesors(Schedule $schedule)
    {
        $asesors = $this->assignmentService->getAvailableAsesors($schedule);

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
}