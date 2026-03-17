<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\Payment;
use App\Models\Tuk;
use App\Models\Skema;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * AdminController
 *
 * Tanggung jawab: Dashboard + hal-hal yang benar-benar lintas domain.
 * Semua CRUD domain sudah dipecah ke controller terpisah:
 *
 *   TUK         → Admin\TukController
 *   Skema       → Admin\SkemaController
 *   Payment     → Admin\PaymentController
 *   Asesmen     → Admin\AsesmenController
 *   APL (01/02) → Admin\AplController
 *   Asesor Assg → Admin\AsesorAssignmentController
 *   Reports     → Admin\ReportController
 */
class AdminController extends Controller
{
    // =========================================================
    // DASHBOARD
    // =========================================================

    public function dashboard()
    {
        $stats = [
            'total_asesi' => Asesmen::count(),
            'total_tuk' => Tuk::count(),
            'total_skema' => Skema::count(),
            'pending_verification' => Asesmen::where('status', 'data_completed')->count(),
            'pending_payment' => Payment::where('status', 'pending')->count(),
            'certified' => Asesmen::where('status', 'certified')->count(),
        ];

        $asesmens = Asesmen::with(['user', 'tuk', 'skema'])
            ->latest()
            ->take(10)
            ->get();

        $batchInfo = null;
        $batchId = Asesmen::whereNotNull('collective_batch_id')
            ->latest()
            ->value('collective_batch_id');

        if ($batchId) {
            $batch = Asesmen::with(['tuk', 'registrar', 'payment'])
                ->where('collective_batch_id', $batchId)
                ->get();

            $batchInfo = [
                'batch_id' => $batchId,
                'total_members' => $batch->count(),
                'tuk' => $batch->first()->tuk,
                'registered_by' => $batch->first()->registrar,
                'payment_timing' => $batch->first()->collective_payment_timing,
                'payment_status' => $batch->every(fn ($a) => $a->payment?->status === 'verified')
                    ? 'paid'
                    : ($batch->contains(fn ($a) => $a->payment?->status === 'pending')
                        ? 'pending'
                        : 'not_paid'),
            ];
        }

        return view('admin.dashboard', compact('stats', 'asesmens', 'batchInfo'));
    }
    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    private function getLatestBatchInfo(): ?array
    {
        $batchId = Asesmen::whereNotNull('collective_batch_id')
            ->latest()
            ->value('collective_batch_id');

        if (!$batchId) {
            return null;
        }

        $batch = Asesmen::with(['tuk', 'registrar', 'payment'])
            ->where('collective_batch_id', $batchId)
            ->get();

        $allPaid    = $batch->every(fn($a) => $a->payment?->status === 'verified');
        $anyPending = $batch->contains(fn($a) => $a->payment?->status === 'pending');

        return [
            'batch_id'        => $batchId,
            'total_members'   => $batch->count(),
            'tuk'             => $batch->first()->tuk,
            'registered_by'   => $batch->first()->registrar,
            'payment_timing'  => $batch->first()->collective_payment_timing,
            'payment_status'  => $allPaid ? 'paid' : ($anyPending ? 'pending' : 'not_paid'),
        ];
    }
}