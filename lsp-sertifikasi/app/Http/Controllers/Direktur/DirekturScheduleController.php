<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Services\SkGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * DirekturScheduleController
 *
 * Direktur melihat jadwal yang menunggu approval, menyetujui atau menolak,
 * dan setelah disetujui SK otomatis di-generate.
 */
class DirekturScheduleController extends Controller
{
    public function __construct(private SkGeneratorService $skGenerator) {}

    /**
     * Dashboard direktur — daftar jadwal menunggu persetujuan + statistik.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending');

        $pendingSchedules = Schedule::with(['tuk', 'skema', 'asesor', 'asesmens', 'creator'])
            ->pendingApproval()
            ->orderBy('created_at', 'asc')
            ->paginate(15, ['*'], 'pending_page');

        $approvedSchedules = Schedule::with(['tuk', 'skema', 'asesor', 'asesmens', 'approvedBy'])
            ->approved()
            ->orderBy('approved_at', 'desc')
            ->paginate(15, ['*'], 'approved_page');

        $rejectedSchedules = Schedule::with(['tuk', 'skema', 'asesmens', 'creator'])
            ->where('approval_status', 'rejected')
            ->orderBy('rejected_at', 'desc')
            ->paginate(15, ['*'], 'rejected_page');

        $stats = [
            'pending'  => Schedule::pendingApproval()->count(),
            'approved' => Schedule::approved()->count(),
            'rejected' => Schedule::where('approval_status', 'rejected')->count(),
        ];

        return view('direktur.schedules.index', compact(
            'pendingSchedules',
            'approvedSchedules',
            'rejectedSchedules',
            'stats',
            'tab'
        ));
    }

    /**
     * Detail jadwal untuk review Direktur.
     */
    public function show(Schedule $schedule)
    {
        $schedule->load([
            'tuk', 'skema', 'asesor', 'creator', 'approvedBy',
            'asesmens.user', 'asesmens.aplsatu', 'asesmens.apldua', 'asesmens.frak01',
        ]);

        return view('direktur.schedules.show', compact('schedule'));
    }

    /**
     * Setujui jadwal.
     * - Generate nomor SK
     * - Generate PDF SK
     * - Update status asesi ke 'scheduled'
     * - Update approval_status ke 'approved'
     */
    public function approve(Request $request, Schedule $schedule)
    {
        if (!$schedule->isPendingApproval()) {
            return $this->jsonOrRedirect(
                $request,
                false,
                'Jadwal ini sudah diproses sebelumnya.',
                route('direktur.schedules.show', $schedule)
            );
        }

        DB::beginTransaction();
        try {
            // 1. Generate nomor SK
            $skNumber = $this->skGenerator->generateSkNumber($schedule);

            // 2. Update jadwal
            $schedule->update([
                'approval_status' => 'approved',
                'approval_notes'  => $request->input('notes'),
                'approved_by'     => auth()->id(),
                'approved_at'     => now(),
                'sk_number'       => $skNumber,
            ]);

            // 3. Update semua asesi ke 'scheduled'
            $schedule->asesmens()->update(['status' => 'scheduled']);

            // 4. Generate SK PDF
            $skPath = $this->skGenerator->generate($schedule->fresh(['tuk', 'skema', 'asesor', 'asesmens', 'approvedBy']));
            $schedule->update(['sk_path' => $skPath]);

            DB::commit();

            Log::info("Direktur #{auth()->id()} menyetujui jadwal #{$schedule->id}. SK: {$skNumber}");

            return $this->jsonOrRedirect(
                $request,
                true,
                "Jadwal disetujui. Nomor SK: {$skNumber}. Status {$schedule->asesmens->count()} asesi berubah ke Terjadwal.",
                route('direktur.schedules.show', $schedule)
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Direktur approve schedule error: {$e->getMessage()}");

            return $this->jsonOrRedirect(
                $request,
                false,
                'Terjadi kesalahan: ' . $e->getMessage(),
                route('direktur.schedules.show', $schedule)
            );
        }
    }

    /**
     * Tolak jadwal.
     * - Admin harus memperbaiki dan mengajukan ulang.
     * - Asesi tidak berubah status (masih belum scheduled).
     */
    public function reject(Request $request, Schedule $schedule)
    {
        $request->validate([
            'rejection_notes' => 'required|string|min:10|max:1000',
        ], [
            'rejection_notes.required' => 'Catatan penolakan wajib diisi.',
            'rejection_notes.min'      => 'Catatan penolakan minimal 10 karakter.',
        ]);

        if (!$schedule->isPendingApproval()) {
            return $this->jsonOrRedirect(
                $request,
                false,
                'Jadwal ini sudah diproses sebelumnya.',
                route('direktur.schedules.show', $schedule)
            );
        }

        $schedule->update([
            'approval_status' => 'rejected',
            'approval_notes'  => $request->rejection_notes,
            'rejected_at'     => now(),
        ]);

        Log::info("Direktur #{auth()->id()} menolak jadwal #{$schedule->id}. Alasan: {$request->rejection_notes}");

        return $this->jsonOrRedirect(
            $request,
            true,
            'Jadwal ditolak. Admin akan diberitahu untuk melakukan perbaikan.',
            route('direktur.schedules.index')
        );
    }

    /**
     * Download/view SK PDF yang sudah di-generate.
     */
    public function downloadSk(Schedule $schedule)
    {
        if (!$schedule->hasSk()) {
            abort(404, 'SK belum tersedia untuk jadwal ini.');
        }

        if (!Storage::disk('private')->exists($schedule->sk_path)) {
            abort(404, 'File SK tidak ditemukan.');
        }

        $ext      = pathinfo($schedule->sk_path, PATHINFO_EXTENSION);
        $filename = 'SK_' . str_replace('/', '-', $schedule->sk_number) . '.' . $ext;

        return response()->streamDownload(function () use ($schedule) {
            echo Storage::disk('private')->get($schedule->sk_path);
        }, $filename, [
            'Content-Type' => $ext === 'pdf' ? 'application/pdf' : 'text/html',
        ]);
    }

    /**
     * Re-generate SK (jika ada perubahan data).
     */
    public function regenerateSk(Schedule $schedule)
    {
        if (!$schedule->isApproved()) {
            return back()->with('error', 'SK hanya bisa di-regenerate untuk jadwal yang sudah disetujui.');
        }

        try {
            $skPath = $this->skGenerator->generate(
                $schedule->load(['tuk', 'skema', 'asesor', 'asesmens', 'approvedBy'])
            );
            $schedule->update(['sk_path' => $skPath]);

            return back()->with('success', 'SK berhasil di-generate ulang.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate SK: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // Helper
    // =========================================================================

    private function jsonOrRedirect(Request $request, bool $success, string $message, string $redirectUrl)
    {
        if ($request->wantsJson()) {
            return response()->json(['success' => $success, 'message' => $message], $success ? 200 : 422);
        }

        $flashKey = $success ? 'success' : 'error';
        return redirect($redirectUrl)->with($flashKey, $message);
    }
}