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
 * Setelah jadwal dibuat, harus menunggu persetujuan Direktur.
 * Status asesi baru berubah ke 'scheduled' setelah Direktur menyetujui.
 */
class AdminScheduleController extends Controller
{
    public function __construct(
        private AsesorAssignmentService $assignmentService
    ) {}

    /**
     * Kriteria asesi siap dijadwalkan:
     * - Status: pra_pra_asesmen_started (atau status yang setara)
     * - APL-01: sudah diverifikasi (status verified/approved)
     * - APL-02: sudah disubmit (tidak null, status bukan draft)
     * - FR.AK.01: sudah disubmit (tidak null, status bukan draft)
     * - Belum punya schedule_id
     */
    private function readyToScheduleQuery()
    {
        return Asesmen::with(['user', 'tuk', 'skema', 'aplsatu', 'apldua', 'frak01'])
            ->whereNull('schedule_id')
            // APL-01 harus sudah diverifikasi
            ->whereHas('aplsatu', function ($q) {
                $q->where('status', 'verified');
                // Sesuaikan nama kolom/nilai status APL-01 di project Anda
                // Contoh alternatif: $q->whereIn('status', ['verified', 'approved']);
            })
            // APL-02 harus sudah disubmit (bukan draft)
            ->whereHas('apldua', function ($q) {
                $q->whereNotIn('status', ['draft'])
                  ->whereNotNull('submitted_at');
                // Sesuaikan: mungkin kolom submitted_at atau status != 'draft'
            })
            // FR.AK.01 harus sudah disubmit
            ->whereHas('frak01', function ($q) {
                $q->whereNotIn('status', ['draft'])
                  ->whereNotNull('submitted_at');
            });
    }

    /**
     * Daftar semua jadwal + asesi siap dijadwalkan.
     */
    public function index()
    {
        $readyToSchedule = $this->readyToScheduleQuery()
            ->orderBy('full_name')
            ->get()
            ->groupBy('tuk_id');

        $schedules = Schedule::with(['tuk', 'skema', 'asesor', 'asesmens.user'])
            ->orderBy('assessment_date', 'desc')
            ->paginate(20);

        $tuks   = Tuk::where('is_active', true)->orderBy('name')->get();
        $skemas = Skema::where('is_active', true)->orderBy('name')->get();

        // Jumlah jadwal menunggu approval (untuk notif)
        $pendingApprovalCount = Schedule::pendingApproval()->count();

        return view('admin.schedules.index', compact(
            'readyToSchedule',
            'schedules',
            'tuks',
            'skemas',
            'pendingApprovalCount'
        ));
    }

    /**
     * Form buat jadwal baru.
     */
public function create(Request $request)
{
    $selectedIds = $request->input('asesmen_ids', []);
    $selectedAsesmens = $selectedIds
        ? Asesmen::with(['tuk', 'skema'])->whereIn('id', $selectedIds)->get()
        : collect();

    $tuks   = Tuk::where('is_active', true)->orderBy('name')->get();
    $skemas = Skema::where('is_active', true)->orderBy('name')->get();

    $availableAsesmens = $this->readyToScheduleQuery()
        ->orderBy('full_name')
        ->get();

    // Ambil semua batch ID yang ada di available asesmens (kolektif saja)
    $batches = $availableAsesmens
        ->whereNotNull('collective_batch_id')
        ->pluck('collective_batch_id')
        ->unique()
        ->sort()
        ->values();

    return view('admin.schedules.create', compact(
        'selectedAsesmens',
        'availableAsesmens',
        'tuks',
        'skemas',
        'batches',
    ));
}

    /**
     * Simpan jadwal baru.
     * Status asesi TIDAK berubah ke 'scheduled' dulu — menunggu approval Direktur.
     * Status asesi tetap, hanya schedule_id yang diisi.
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
            'location_type'   => 'required|in:offline,online',
            'location'        => 'required|string|max:255',
            'meeting_link'    => 'nullable|url|max:500|required_if:location_type,online',
            'notes'           => 'nullable|string',
        ]);
        // Validasi asesi: harus sudah memenuhi kriteria dan belum terjadwal
        $asesmens = $this->readyToScheduleQuery()
            ->whereIn('id', $request->asesmen_ids)
            ->get();

        if ($asesmens->count() !== count($request->asesmen_ids)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Beberapa asesi tidak memenuhi syarat penjadwalan (APL-01 harus terverifikasi, APL-02 dan FR.AK.01 harus sudah disubmit).');
        }

        // Validasi semua asesi punya skema sama
        $skemaIds = $asesmens->pluck('skema_id')->unique();
        if ($skemaIds->count() > 1) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Asesi dalam satu jadwal harus memiliki skema yang sama.');
        }

        DB::beginTransaction();
        try {
            // Buat jadwal dengan status pending_approval
            $schedule = Schedule::create([
                'tuk_id'          => $request->tuk_id,
                'skema_id'        => $skemaIds->first(),
                'assessment_date' => $request->assessment_date,
                'start_time'      => $request->start_time,
                'end_time'        => $request->end_time,
                'location'        => $request->location,
                'location_type'   => $request->location_type,           // ← tambah
                'meeting_link'    => $request->location_type === 'online'
                                        ? $request->meeting_link
                                        : null,                          // ← tambah
                'notes'           => $request->notes,
                'created_by'      => auth()->id(),
                'approval_status' => 'pending_approval',
                'asesor_id'       => $request->asesor_id ?: null,
            ]);

            // Hubungkan asesi ke jadwal — status asesi BELUM berubah ke 'scheduled'
            // Status asesi berubah nanti setelah Direktur approve
            foreach ($asesmens as $asesmen) {
                $asesmen->update(['schedule_id' => $schedule->id]);
            }

            // Assign asesor jika dipilih
            if ($request->asesor_id) {
                $asesor = \App\Models\Asesor::findOrFail($request->asesor_id);
                $this->assignmentService->assignAsesor($schedule, $asesor, 'Ditugaskan saat pembuatan jadwal');
            }

            DB::commit();

            Log::info("Admin #{auth()->id()} membuat jadwal #{$schedule->id} untuk {$asesmens->count()} asesi. Menunggu approval Direktur.");

            return redirect()->route('admin.schedules.index')
                ->with('success', "Jadwal berhasil dibuat untuk {$asesmens->count()} asesi dan sedang menunggu persetujuan Direktur.");

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
        $schedule->load([
            'tuk', 'skema', 'asesor', 'approvedBy',
            'asesmens.user', 'asesmens.aplsatu', 'asesmens.apldua', 'asesmens.frak01',
        ]);

        return view('admin.schedules.show', compact('schedule'));
    }

    /**
     * Edit jadwal — hanya bisa diedit jika masih pending atau ditolak.
     */
    public function edit(Schedule $schedule)
    {
        if ($schedule->isApproved()) {
            return redirect()->route('admin.schedules.show', $schedule)
                ->with('error', 'Jadwal yang sudah disetujui tidak dapat diedit.');
        }

        $schedule->load(['tuk', 'skema', 'asesor', 'asesmens']);
        $tuks = Tuk::where('is_active', true)->orderBy('name')->get();

        return view('admin.schedules.edit', compact('schedule', 'tuks'));
    }

    /**
     * Update jadwal.
     * Jika jadwal sebelumnya ditolak, kembalikan ke pending_approval setelah diedit.
     */
    public function update(Request $request, Schedule $schedule)
    {
        if ($schedule->isApproved()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Jadwal sudah disetujui, tidak dapat diedit.'], 403);
            }
            return redirect()->back()->with('error', 'Jadwal sudah disetujui, tidak dapat diedit.');
        }

        $request->validate([
            'assessment_date' => 'required|date',
            'start_time'      => 'required',
            'end_time'        => 'required|after:start_time',
            'location_type'   => 'required|in:offline,online',
            'location'        => 'required|string|max:255',
            'meeting_link'    => 'nullable|url|max:500|required_if:location_type,online',
            'notes'           => 'nullable|string',
        ]);
        
        $data = $request->only(['assessment_date', 'start_time', 'end_time', 'location', 'location_type', 'notes']);
        $data['meeting_link'] = $request->location_type === 'online' ? $request->meeting_link : null;
        
        // Jika sebelumnya ditolak, kembalikan ke pending_approval setelah admin perbaiki
        if ($schedule->isRejected()) {
            $data['approval_status'] = 'pending_approval';
            $data['approval_notes']  = null;
            $data['rejected_at']     = null;
        }
        
        $schedule->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Jadwal berhasil diupdate dan dikembalikan ke antrian persetujuan Direktur.',
                'schedule' => $schedule->fresh()->toArray(),
            ]);
        }

        return redirect()->route('admin.schedules.show', $schedule)
            ->with('success', 'Jadwal berhasil diupdate dan dikembalikan ke antrian persetujuan Direktur.');
    }

    /**
     * Hapus jadwal — kembalikan asesi ke status sebelumnya.
     * Hanya bisa dihapus jika belum disetujui.
     */
    public function destroy(Schedule $schedule)
    {
        if ($schedule->isApproved()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Jadwal yang sudah disetujui tidak dapat dihapus.'], 403);
            }
            return redirect()->back()->with('error', 'Jadwal yang sudah disetujui tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            foreach ($schedule->asesmens as $asesmen) {
                // Kembalikan asesi — hapus schedule_id, status tetap karena belum berubah
                $asesmen->update(['schedule_id' => null]);
            }
            $schedule->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Jadwal dihapus.']);
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * AJAX: Available asesors untuk jadwal tertentu.
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

    /**
     * AJAX: Assign asesor ke jadwal.
     */
    public function assignAsesor(Request $request, Schedule $schedule)
    {
        $request->validate(['asesor_id' => 'required|exists:asesors,id']);

        $asesor = \App\Models\Asesor::findOrFail($request->asesor_id);
        $this->assignmentService->assignAsesor($schedule, $asesor);

        return response()->json([
            'success' => true,
            'message' => "Asesor {$asesor->nama} berhasil ditugaskan.",
        ]);
    }

    /**
     * AJAX: Unassign asesor dari jadwal.
     */
    public function unassignAsesor(Schedule $schedule)
    {
        $schedule->update(['asesor_id' => null, 'assigned_by' => null, 'assigned_at' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Asesor berhasil dilepas.',
        ]);
    }

    public function downloadSk(Schedule $schedule)
    {
        if (!$schedule->hasSk()) {
            abort(404, 'SK belum tersedia untuk jadwal ini.');
        }

        if (!\Illuminate\Support\Facades\Storage::disk('private')->exists($schedule->sk_path)) {
            abort(404, 'File SK tidak ditemukan.');
        }

        $ext      = pathinfo($schedule->sk_path, PATHINFO_EXTENSION);
        $filename = 'SK_' . str_replace('/', '-', $schedule->sk_number) . '.' . $ext;

        return response()->streamDownload(function () use ($schedule) {
            echo \Illuminate\Support\Facades\Storage::disk('private')->get($schedule->sk_path);
        }, $filename, [
            'Content-Type' => $ext === 'pdf' ? 'application/pdf' : 'text/html',
        ]);
    }
}