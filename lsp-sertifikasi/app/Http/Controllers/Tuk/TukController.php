<?php

namespace App\Http\Controllers\Tuk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tuk;
use App\Models\Asesmen;
use App\Models\Schedule;
use App\Models\Payment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ParticipantsImport;
use Barryvdh\DomPDF\Facade\Pdf;

class TukController extends Controller
{
    // =========================================================================
    // Dashboard
    // =========================================================================

    public function dashboard()
    {
        $tuk = auth()->user()->tuk;

        if (!$tuk) {
            abort(403, 'Akun TUK tidak ditemukan.');
        }

        $stats = [
            'total_asesi'         => Asesmen::where('tuk_id', $tuk->id)->count(),

            // Kolektif: perlu dijadwalkan setelah verified (skip paid)
            // Mandiri : perlu dijadwalkan setelah paid
            'pending_schedule'    => Asesmen::where('tuk_id', $tuk->id)
                ->where(function ($q) {
                    $q->where(function ($sq) {
                        // Kolektif → verified sudah cukup
                        $sq->where('is_collective', true)
                            ->where('collective_paid_by_tuk', true)
                            ->where('status', 'verified');
                    })->orWhere(function ($sq) {
                        // Mandiri → harus sudah paid
                        $sq->where(function ($mq) {
                            $mq->where('is_collective', false)
                                ->orWhere('collective_paid_by_tuk', false);
                        })->where('status', 'paid');
                    });
                })
                ->count(),

            'scheduled'  => Asesmen::where('tuk_id', $tuk->id)->where('status', 'scheduled')->count(),
            'completed'  => Asesmen::where('tuk_id', $tuk->id)->whereIn('status', ['assessed', 'certified'])->count(),

            // Pending verifikasi TUK
            'pending_verification' => Asesmen::where('tuk_id', $tuk->id)
                ->where('status', 'data_completed')->count(),
        ];

        $recent_asesmens = Asesmen::with(['user', 'skema', 'schedule'])
            ->where('tuk_id', $tuk->id)
            ->latest()
            ->take(10)
            ->get();

        return view('tuk.dashboard', compact('stats', 'recent_asesmens', 'tuk'));
    }

    // =========================================================================
    // Collective Registration
    // =========================================================================

    public function collectiveRegistration()
    {
        $tuk = auth()->user()->tuk;
        return view('tuk.collective.form', compact('tuk'));
    }

    /**
     * Store collective registration.
     *
     * Semua akun asesi dibuat otomatis, status dimulai dari 'registered'.
     * Tidak ada proses pembayaran di flow utama — TUK membayar secara manual di luar sistem.
     */
public function storeCollectiveRegistration(Request $request)
{
    $participants = collect($request->participants ?? [])->map(function ($p) {
        $email = strtolower(trim($p['email'] ?? ''));
        // Buang semua karakter non-printable dan non-ASCII
        $email = preg_replace('/[^\x20-\x7E]/', '', $email);
        $email = trim($email); // trim lagi setelah strip

        $name = trim($p['name'] ?? '');
        $name = preg_replace('/[^\x20-\x7E\p{L}\p{N}]/u', ' ', $name);
        $name = trim($name);

        return ['name' => $name, 'email' => $email];
    })->filter(fn($p) => $p['name'] && $p['email'] && str_contains($p['email'], '@') && str_contains($p['email'], '.'))
    ->values()
    ->toArray();

    Log::info('Collective Reg - participants diterima: ' . count($participants), [
        'raw_count'    => count($request->participants ?? []),
        'after_filter' => count($participants),
    ]);

    $request->merge(['participants' => $participants]);

    if (empty($participants)) {
        return redirect()->back()
            ->withErrors(['participants' => 'Tidak ada data peserta yang valid.']);
    }

    // ← TAMBAH LOG INI
    Log::info('Collective Reg - mulai validasi');

    $request->validate([
        'batch_name'           => 'nullable|string|max:255',
        'participants'         => 'required|array|min:1',
        'participants.*.name'  => 'required|string|max:255',
        'participants.*.email' => 'required|string|unique:users,email',
        'skema_id'             => 'required|exists:skemas,id',
        'payment_phases'       => 'required|in:single,two_phase',
        'preferred_date'       => 'required|date|after:today',
        'training_flag'        => 'required|boolean',
    ]);

    // ← TAMBAH LOG INI
    Log::info('Collective Reg - validasi OK, mulai DB transaction');

    $tuk       = auth()->user()->tuk;
    $batchName = $request->batch_name
        ? \Illuminate\Support\Str::slug($request->batch_name, '-')
        : 'BATCH';
    $suffix    = strtoupper(\Illuminate\Support\Str::random(6));
    $batchId   = strtoupper($batchName) . '-' . $tuk->code . '-' . $suffix;

    $registeredCount = 0;
    $errors          = [];
    $skippedEmails   = [];

    DB::beginTransaction();
    try {
        foreach ($request->participants as $index => $participant) {
            $email = strtolower(trim($participant['email']));
            $name  = trim($participant['name']);

            // ← LOG SETIAP 10 PESERTA
            if ($index % 10 === 0) {
                Log::info("Collective Reg - progress: peserta ke-{$index}");
            }

            if (User::where('email', $email)->exists()) {
                $errors[]        = "Baris " . ($index + 1) . ": Email {$email} sudah terdaftar";
                $skippedEmails[] = $email;
                Log::warning("Collective Reg SKIP: {$email}");
                continue;
            }

            $user = User::create([
                'name'               => $name,
                'email'              => $email,
                'password'           => Hash::make('password123'),
                'role'               => 'asesi',
                'is_active'          => true,
                'password_changed_at' => null,
                'email_verified_at'  => now(),
            ]);

            Asesmen::create([
                'user_id'                => $user->id,
                'tuk_id'                 => $tuk->id,
                'skema_id'               => $request->skema_id,
                'full_name'              => $name,
                'preferred_date'         => $request->preferred_date,
                'training_flag'          => $request->training_flag,
                'registration_date'      => now(),
                'status'                 => 'registered',
                'registered_by'          => auth()->id(),
                'is_collective'          => true,
                'collective_batch_id'    => $batchId,
                'payment_phases'         => $request->payment_phases,
                'collective_paid_by_tuk' => true,
                'skip_payment'           => true,
            ]);

            $registeredCount++;
        }

        // ← TAMBAH LOG INI
        Log::info("Collective Reg - loop selesai, registeredCount: {$registeredCount}");

        DB::commit();

        Log::info("Collective Reg - DB commit OK");

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Collective Registration Error: ' . $e->getMessage(), [
            'batch_id'    => $batchId ?? null,
            'tuk_id'      => $tuk->id,
            'participant' => $participant ?? null,
            'trace'       => $e->getTraceAsString(),
        ]);

        return redirect()->back()
            ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }

    $message = "{$registeredCount} dari " . count($request->participants) . " peserta berhasil didaftarkan.";
    $message .= ' Password default: "password123".';

    return redirect()->route('tuk.asesi')->with('success', $message);
}

    // =========================================================================
    // Asesi List
    // =========================================================================

    public function asesi()
    {
        $tuk = auth()->user()->tuk;

        $asesmens = Asesmen::with(['user', 'skema', 'payment', 'schedule'])
            ->where('tuk_id', $tuk->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $collectiveBatches = $asesmens->where('is_collective', true)
            ->groupBy('collective_batch_id')
            ->map(function ($batch) {
                $batchData = $batch->first();
                return [
                    'batch_id'        => $batchData->collective_batch_id,
                    'count'           => $batch->count(),
                    'skema'           => $batchData->skema,
                    'payment_timing'  => $batchData->collective_payment_timing,
                    'payment_status'  => $batchData->getBatchPaymentStatus(),
                    'ready_for_payment' => $batchData->isBatchReadyForPayment(),
                    'members'         => $batch,
                ];
            });

        return view('tuk.asesi.index', compact('asesmens', 'collectiveBatches', 'tuk'));
    }

    public function asesiDetail(Asesmen $asesmen)
    {
        $tuk = auth()->user()->tuk;

        if ($asesmen->tuk_id != $tuk->id) {
            abort(403);
        }

        $asesmen->load(['user', 'skema', 'payment', 'schedule', 'certificate']);

        $batchMembers = $asesmen->is_collective ? $asesmen->fullBatch() : collect([]);

        return view('tuk.asesi.detail', compact('asesmen', 'tuk', 'batchMembers'));
    }

    // =========================================================================
    // Scheduling
    // =========================================================================

    /**
     * Daftar asesi yang perlu dijadwalkan.
     *
     * Kolektif : status 'verified' (skip paid)
     * Mandiri  : status 'paid'
     */
    public function schedules()
    {
        $tuk = auth()->user()->tuk;

        // Kolektif yang sudah verified (tidak perlu paid dulu)
        $collectiveReady = Asesmen::with(['user', 'skema'])
            ->where('tuk_id', $tuk->id)
            ->where('is_collective', true)
            ->where('collective_paid_by_tuk', true)
            ->where('status', 'verified')
            ->whereNull('schedule_id')
            ->get();

        // Mandiri yang sudah paid
        $mandiriPaid = Asesmen::with(['user', 'skema'])
            ->where('tuk_id', $tuk->id)
            ->where(function ($q) {
                $q->where('is_collective', false)
                    ->orWhere('collective_paid_by_tuk', false);
            })
            ->where('status', 'paid')
            ->whereNull('schedule_id')
            ->get();

        // Gabung semua yang perlu dijadwalkan
        $asesmens = $collectiveReady->merge($mandiriPaid);

        $scheduled = Schedule::with(['asesmens.user', 'asesmens.skema', 'tuk', 'skema'])
            ->where('tuk_id', $tuk->id)
            ->orderBy('assessment_date', 'asc')
            ->get();

        return view('tuk.schedules.index', compact('asesmens', 'scheduled', 'tuk'));
    }

    /**
     * Batch create schedule.
     *
     * Menerima asesi dengan status 'verified' (kolektif) ATAU 'paid' (mandiri).
     */
    public function batchCreateSchedule(Request $request)
    {
        $tuk = auth()->user()->tuk;

        $request->validate([
            'asesmen_ids'       => 'required|array|min:1',
            'asesmen_ids.*'     => 'exists:asesmens,id',
            'assessment_date'   => 'required|date|after_or_equal:today',
            'start_time'        => 'required',
            'end_time'          => 'required|after:start_time',
            'location'          => 'required|string|max:255',
            'notes'             => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $scheduledCount = 0;
            $errors         = [];

            $firstAsesmen = Asesmen::find($request->asesmen_ids[0]);
            $skemaId      = $firstAsesmen->skema_id;

            $schedule = Schedule::create([
                'tuk_id'          => $tuk->id,
                'skema_id'        => $skemaId,
                'assessment_date' => $request->assessment_date,
                'start_time'      => $request->start_time,
                'end_time'        => $request->end_time,
                'location'        => $request->location,
                'notes'           => $request->notes,
                'created_by'      => auth()->id(),
            ]);

            foreach ($request->asesmen_ids as $asesmenId) {
                $asesmen = Asesmen::find($asesmenId);

                if (!$asesmen || $asesmen->tuk_id != $tuk->id) {
                    $errors[] = "Asesmen #{$asesmenId} tidak ditemukan atau bukan milik TUK ini";
                    continue;
                }

                // Izinkan 'verified' (kolektif) atau 'paid' (mandiri)
                $allowedStatuses = $asesmen->shouldSkipPayment()
                    ? ['verified']
                    : ['paid'];

                if (!in_array($asesmen->status, $allowedStatuses)) {
                    $statusLabel = $asesmen->shouldSkipPayment() ? "'Terverifikasi'" : "'Sudah Bayar'";
                    $errors[]    = "Asesmen #{$asesmenId} belum dalam status {$statusLabel}";
                    continue;
                }

                if ($asesmen->schedule_id) {
                    $errors[] = "Asesmen #{$asesmenId} sudah memiliki jadwal";
                    continue;
                }

                $asesmen->update([
                    'schedule_id' => $schedule->id,
                    'status'      => 'scheduled',
                ]);

                $scheduledCount++;
            }

            DB::commit();

            $message = "{$scheduledCount} asesi berhasil dijadwalkan!";
            if (!empty($errors)) {
                $message .= ' Namun ' . count($errors) . ' gagal: ' . implode(', ', $errors);
                Log::warning('Batch Schedule Errors: ' . implode(' | ', $errors));
            }

            return redirect()->route('tuk.schedules')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch Schedule Error: ' . $e->getMessage());

            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function viewSchedule(Schedule $schedule)
    {
        $tuk = auth()->user()->tuk;

        if ($schedule->tuk_id != $tuk->id) {
            abort(403);
        }

        $firstAsesmen = $schedule->asesmens()->first();

        return response()->json([
            'success'  => true,
            'schedule' => [
                'id'              => $schedule->id,
                'asesmen_name'    => $firstAsesmen->full_name ?? $firstAsesmen->user->name,
                'skema'           => $schedule->skema->name,
                'assessment_date' => $schedule->assessment_date->format('d F Y'),
                'start_time'      => $schedule->start_time,
                'end_time'        => $schedule->end_time,
                'location'        => $schedule->location,
                'notes'           => $schedule->notes,
                'status'          => $firstAsesmen->status_label,
                'total_asesmens'  => $schedule->asesmens->count(),
            ],
        ]);
    }

    public function editSchedule(Schedule $schedule)
    {
        $tuk = auth()->user()->tuk;

        if ($schedule->tuk_id != $tuk->id) {
            abort(403);
        }

        $asesmensNames = $schedule->asesmens->pluck('full_name')->join(', ');

        return response()->json([
            'success'  => true,
            'schedule' => [
                'id'              => $schedule->id,
                'asesmen_names'   => $asesmensNames,
                'total_asesmens'  => $schedule->asesmens->count(),
                'assessment_date' => $schedule->assessment_date->format('Y-m-d'),
                'start_time'      => $schedule->start_time,
                'end_time'        => $schedule->end_time,
                'location'        => $schedule->location,
                'notes'           => $schedule->notes,
            ],
        ]);
    }

    /**
     * Delete schedule — kembalikan asesi ke status sebelum scheduled.
     * Kolektif → verified, Mandiri → paid.
     */
    public function deleteSchedule(Schedule $schedule)
    {
        $tuk = auth()->user()->tuk;

        if ($schedule->tuk_id != $tuk->id) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            foreach ($schedule->asesmens as $asesmen) {
                $prevStatus = $asesmen->shouldSkipPayment() ? 'verified' : 'paid';
                $asesmen->update([
                    'schedule_id' => null,
                    'status'      => $prevStatus,
                ]);
            }

            $schedule->delete();
            DB::commit();

            return redirect()->route('tuk.schedules')
                ->with('success', 'Jadwal berhasil dihapus. Status asesi dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete Schedule Error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function deleteScheduleAjax(Request $request, Schedule $schedule)
    {
        $tuk = auth()->user()->tuk;

        if ($schedule->tuk_id != $tuk->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            $asesmensCount = $schedule->asesmens->count();

            foreach ($schedule->asesmens as $asesmen) {
                $prevStatus = $asesmen->shouldSkipPayment() ? 'verified' : 'paid';
                $asesmen->update([
                    'schedule_id' => null,
                    'status'      => $prevStatus,
                ]);
            }

            $schedule->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Jadwal berhasil dihapus! {$asesmensCount} asesi dikembalikan ke status sebelumnya.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete Schedule Error: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function updateScheduleAjax(Request $request, Schedule $schedule)
    {
        $tuk = auth()->user()->tuk;

        if ($schedule->tuk_id != $tuk->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'assessment_date' => 'required|date',
            'start_time'      => 'required',
            'end_time'        => 'required|after:start_time',
            'location'        => 'required|string|max:255',
            'notes'           => 'nullable|string',
        ]);

        try {
            $schedule->update([
                'assessment_date' => $request->assessment_date,
                'start_time'      => $request->start_time,
                'end_time'        => $request->end_time,
                'location'        => $request->location,
                'notes'           => $request->notes,
            ]);

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
        } catch (\Exception $e) {
            Log::error('Update Schedule Error: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // Export
    // =========================================================================

    public function exportScheduleBatch(Request $request, $groupKey)
    {
        $tuk       = auth()->user()->tuk;
        $groupData = $request->input('group_data');

        if (!$groupData) {
            return response()->json(['success' => false, 'message' => 'Group data tidak ditemukan'], 400);
        }

        $groupArray = explode('|', $groupData);
        if (count($groupArray) !== 4) {
            return response()->json(['success' => false, 'message' => 'Invalid group data format'], 400);
        }

        [$date, $startTime, $endTime, $location] = $groupArray;

        $schedules = Schedule::with(['asesmens.user', 'asesmens.skema'])
            ->where('tuk_id', $tuk->id)
            ->whereDate('assessment_date', $date)
            ->where('start_time', $startTime)
            ->where('end_time', $endTime)
            ->where('location', $location)
            ->orderBy('assessment_date', 'asc')
            ->get();

        if ($schedules->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada jadwal yang ditemukan'], 404);
        }

        $groupInfo = [
            'date'     => \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMMM Y'),
            'time'     => $startTime . ' - ' . $endTime . ' WIB',
            'location' => $location,
        ];

        try {
            $scheduleIds = $schedules->pluck('id')->toArray();
            $filename    = 'Daftar_Asesmen_' . \Carbon\Carbon::parse($date)->format('Ymd') . '_' . str_replace(':', '', $startTime) . '.xlsx';

            return Excel::download(
                new \App\Exports\ScheduleExport($scheduleIds, $groupInfo),
                $filename
            );
        } catch (\Exception $e) {
            Log::error('Export Schedule Error: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error saat export: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // Collective Payment (Manual TF/QRIS) — Midtrans disembunyikan sementara
    // =========================================================================

    /**
     * Daftar batch — halaman ringkasan pembayaran kolektif.
     * Midtrans dinonaktifkan sementara; TUK membayar via TF/QRIS di luar sistem.
     */
    public function collectivePayments()
    {
        $tuk = auth()->user()->tuk;

        $batches = Asesmen::where('tuk_id', $tuk->id)
            ->whereNotNull('collective_batch_id')
            ->where('is_collective', true)
            ->where('collective_paid_by_tuk', true)
            ->with(['skema', 'payments'])
            ->get()
            ->groupBy('collective_batch_id')
            ->map(function ($batch) {
                $firstAsesmen = $batch->first();

                // Hitung status berdasarkan payment record manual
                $paymentStatus  = 'manual';  // Default: pembayaran manual di luar sistem
                $canPayPhase1   = false;
                $canPayPhase2   = false;
                $currentPhase   = 'full';

                if ($firstAsesmen->payment_phases === 'two_phase') {
                    $currentPhase = 'phase_1';
                    $phase1Paid   = $batch->every(fn($a) => $a->payments()->where('payment_phase', 'phase_1')->where('status', 'verified')->exists());
                    $phase2Paid   = $batch->every(fn($a) => $a->payments()->where('payment_phase', 'phase_2')->where('status', 'verified')->exists());

                    if ($phase1Paid && $phase2Paid) {
                        $paymentStatus = 'fully_paid';
                        $currentPhase  = 'phase_2';
                    } elseif ($phase1Paid) {
                        $paymentStatus = 'phase_1_paid';
                        $currentPhase  = 'phase_2';
                    }
                } else {
                    $allPaid = $batch->every(fn($a) => $a->payments()->where('payment_phase', 'full')->where('status', 'verified')->exists());
                    if ($allPaid) {
                        $paymentStatus = 'paid';
                    }
                }

                return [
                    'batch_id'           => $firstAsesmen->collective_batch_id,
                    'skema'              => $firstAsesmen->skema,
                    'total_participants' => $batch->count(),
                    'registration_date'  => $firstAsesmen->registration_date,
                    'payment_phases'     => $firstAsesmen->payment_phases,
                    'payment_status'     => $paymentStatus,
                    'current_phase'      => $currentPhase,
                    'can_pay_phase_1'    => $canPayPhase1,
                    'can_pay_phase_2'    => $canPayPhase2,
                    'total_amount'       => $batch->sum('fee_amount'),
                    'phase_1_amount'     => $batch->sum('phase_1_amount'),
                    'phase_2_amount'     => $batch->sum('phase_2_amount'),
                    'asesmens'           => $batch,
                ];
            })
            ->sortByDesc('registration_date')
            ->values();

        return view('tuk.payments.index', compact('batches', 'tuk'));
    }

    /**
     * Detail batch — view status pembayaran manual.
     */
    public function collectivePayment($batchId)
    {
        $tuk = auth()->user()->tuk;

        $asesmens = Asesmen::with(['user', 'skema', 'payments'])
            ->where('collective_batch_id', $batchId)
            ->where('tuk_id', $tuk->id)
            ->get();

        if ($asesmens->isEmpty()) {
            return redirect()->route('tuk.asesi')->with('error', 'Batch tidak ditemukan');
        }

        $firstAsesmen  = $asesmens->first();
        $paymentPhases = $firstAsesmen->payment_phases ?? 'single';

        // Hitung status phase
        $phase1Status = 'not_paid';
        $phase2Status = 'not_paid';
        $currentPhase = 'full';

        if ($paymentPhases === 'two_phase') {
            $phase1Paid = $asesmens->every(fn($a) => $a->payments()->where('payment_phase', 'phase_1')->where('status', 'verified')->exists());
            $phase2Paid = $asesmens->every(fn($a) => $a->payments()->where('payment_phase', 'phase_2')->where('status', 'verified')->exists());

            if ($phase1Paid) $phase1Status = 'paid';
            if ($phase2Paid) $phase2Status = 'paid';

            $currentPhase = !$phase1Paid ? 'phase_1' : (!$phase2Paid ? 'phase_2' : 'done');
        }

        $allPaid = false;
        if ($paymentPhases === 'single') {
            $allPaid = $asesmens->every(fn($a) => $a->payments()->where('payment_phase', 'full')->where('status', 'verified')->exists());
        } else {
            $allPaid = $phase1Status === 'paid' && $phase2Status === 'paid';
        }

        $totalAmount = $asesmens->sum('fee_amount');

        return view('tuk.collective.payment', [
            'asesmens'      => $asesmens,
            'batchId'       => $batchId,
            'paymentPhases' => $paymentPhases,
            'phase1Status'  => $phase1Status,
            'phase2Status'  => $phase2Status,
            'currentPhase'  => $currentPhase,
            'totalAmount'   => $totalAmount,
            'allPaid'       => $allPaid,
            'tuk'           => $tuk,
            // Midtrans-related: selalu false untuk saat ini
            'canPay'        => false,
            'paymentStatus' => $firstAsesmen->getBatchPaymentStatus() ?? 'manual',
        ]);
    }

    // =========================================================================
    // Midtrans Collective Payment — DISEMBUNYIKAN SEMENTARA (kode tetap ada)
    // =========================================================================

    /**
     * @deprecated Midtrans dinonaktifkan sementara. Gunakan pembayaran manual (TF/QRIS).
     */
    public function createCollectiveSnapToken(Request $request, $batchId)
    {
        // Blokir akses selama Midtrans dinonaktifkan
        return response()->json([
            'success' => false,
            'message' => 'Pembayaran via gateway sementara tidak tersedia. Silakan hubungi admin untuk melakukan pembayaran manual.',
        ], 503);
    }

    /**
     * @deprecated
     */
    public function collectivePaymentFinish($batchId)
    {
        return redirect()->route('tuk.asesi')
            ->with('info', 'Pembayaran via gateway sementara tidak tersedia.');
    }

    /**
     * @deprecated
     */
    public function checkCollectivePaymentStatus(Request $request, $batchId)
    {
        return response()->json([
            'success' => false,
            'message' => 'Fitur ini sementara tidak tersedia.',
        ], 503);
    }

    // =========================================================================
    // Batch Detail
    // =========================================================================

    public function batchDetail($batchId)
    {
        $tuk = auth()->user()->tuk;

        $asesmens = Asesmen::with(['user', 'skema', 'payments'])
            ->where('collective_batch_id', $batchId)
            ->where('tuk_id', $tuk->id)
            ->get();

        if ($asesmens->isEmpty()) {
            return redirect()->route('tuk.asesi')->with('error', 'Batch tidak ditemukan');
        }

        $firstAsesmen  = $asesmens->first();
        $paymentPhases = $firstAsesmen->payment_phases ?? 'single';

        $phase1Status = 'not_paid';
        $phase2Status = 'not_paid';

        if ($paymentPhases === 'two_phase') {
            $phase1Paid = $asesmens->every(fn($a) => $a->payments()->where('payment_phase', 'phase_1')->where('status', 'verified')->exists());
            $phase2Paid = $asesmens->every(fn($a) => $a->payments()->where('payment_phase', 'phase_2')->where('status', 'verified')->exists());

            if ($phase1Paid) $phase1Status = 'paid';
            if ($phase2Paid) $phase2Status = 'paid';
        }

        $paymentStatus      = $firstAsesmen->getBatchPaymentStatus() ?? 'manual';
        $hasVerifiedPayment = $asesmens->flatMap->payments->where('status', 'verified')->isNotEmpty();

        $payments = Payment::whereIn('asesmen_id', $asesmens->pluck('id'))
            ->where('status', 'verified')
            ->orderBy('verified_at', 'desc')
            ->get()
            ->unique('payment_phase');

        $totalAmount = $paymentPhases === 'single'
            ? $asesmens->sum('fee_amount')
            : $asesmens->sum('phase_1_amount') + $asesmens->sum('phase_2_amount');

        return view('tuk.batch.detail', compact(
            'asesmens',
            'batchId',
            'firstAsesmen',
            'paymentPhases',
            'phase1Status',
            'phase2Status',
            'paymentStatus',
            'hasVerifiedPayment',
            'payments',
            'totalAmount',
            'tuk'
        ));
    }

    // =========================================================================
    // Invoice
    // =========================================================================

    public function downloadCollectiveInvoice($batchId)
    {
        $tuk = auth()->user()->tuk;

        $asesmens = Asesmen::with(['skema', 'payments'])
            ->where('collective_batch_id', $batchId)
            ->where('tuk_id', $tuk->id)
            ->get();

        if ($asesmens->isEmpty()) {
            return redirect()->route('tuk.asesi')->with('error', 'Batch tidak ditemukan');
        }

        $payment = Payment::whereIn('asesmen_id', $asesmens->pluck('id'))
            ->where('status', 'verified')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$payment) {
            return redirect()->back()->with('error', 'Belum ada pembayaran yang terverifikasi untuk batch ini');
        }

        $invoiceNumber = 'INV-BATCH-' . $payment->order_id;
        $isCollective  = true;
        $phase         = $payment->payment_phase ?? 'full';
        $asesmen       = $asesmens->first();

        $pdf      = Pdf::loadView('pdf.invoice', compact(
            'payment',
            'invoiceNumber',
            'isCollective',
            'phase',
            'asesmen',
            'asesmens',
            'batchId',
            'tuk'
        ));
        $filename = 'Invoice_Kolektif_' . $batchId . '_' . date('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    // =========================================================================
    // Verification (single)
    // =========================================================================

    public function showVerification(Asesmen $asesmen)
    {
        $tuk = auth()->user()->tuk;

        if ($asesmen->tuk_id != $tuk->id) {
            abort(403);
        }

        return view('tuk.verifications.show', compact('asesmen', 'tuk'));
    }

    // =========================================================================
    // File Helpers
    // =========================================================================

    public function downloadTemplate($type = 'excel')
    {
        $headers = [
            ['Nama Lengkap', 'Email'],
            ['John Doe', 'john@example.com'],
            ['Jane Smith', 'jane@example.com'],
            ['Bob Wilson', 'bob@example.com'],
        ];

        if ($type === 'csv') {
            $filename = 'Template_Peserta_Kolektif.csv';
            $callback = function () use ($headers) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                foreach ($headers as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        return Excel::download(new class implements
            \Maatwebsite\Excel\Concerns\FromArray,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\WithStyles
        {
            public function array(): array
            {
                return [
                    ['John Doe', 'john@example.com'],
                    ['Jane Smith', 'jane@example.com'],
                    ['Bob Wilson', 'bob@example.com'],
                ];
            }

            public function headings(): array
            {
                return ['Nama Lengkap', 'Email'];
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [
                    1 => [
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '4472C4'],
                        ],
                    ],
                ];
            }
        }, 'Template_Peserta_Kolektif.xlsx');
    }

    public function parseParticipantsFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $file   = $request->file('file');
            $import = new ParticipantsImport();
            Excel::import($import, $file);

            if (!$import->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terdapat data yang tidak valid',
                    'errors'  => $import->getErrors(),
                    'data'    => $import->data,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'File berhasil diparse',
                'data'    => $import->data,
                'count'   => count($import->data),
            ]);
        } catch (\Exception $e) {
            Log::error('Parse Participants File Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file: ' . $e->getMessage(),
            ], 500);
        }
    }
}