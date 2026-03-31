<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\AplSatu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * AdminRejectController
 *
 * Menangani verifikasi, penolakan, dan approval biodata & APL-01 oleh admin.
 */
class AdminRejectController extends Controller
{
    // =========================================================================
    // VERIFY BIODATA
    // =========================================================================

    /**
     * Admin memverifikasi (menyetujui) biodata asesi.
     * Tandai biodata_verified_at dan clear revision flag jika ada.
     */
    public function verifyBiodata(Request $request, Asesmen $asesmen)
    {
        if (!in_array($asesmen->status, [
            'pra_asesmen_started',
            'scheduled',
            'pra_asesmen_completed',
            'assessed',
        ])) {
            return response()->json([
                'success' => false,
                'message' => 'Biodata hanya bisa diverifikasi saat status asesmen sudah dimulai.',
            ], 422);
        }

        $asesmen->update([
            'biodata_verified_at'     => now(),
            'biodata_verified_by'     => auth()->id(),
            'biodata_needs_revision'  => false,
            'biodata_rejection_notes' => null,
            'biodata_rejected_at'     => null,
            'biodata_rejected_by'     => null,
        ]);

        Log::info('[ADMIN][VERIFY-BIODATA] Asesmen #' . $asesmen->id . ' biodata diverifikasi oleh Admin #' . auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Biodata asesi berhasil diverifikasi.',
        ]);
    }

    // =========================================================================
    // REJECT BIODATA
    // =========================================================================

    /**
     * Admin menolak/mengembalikan biodata asesi untuk direvisi.
     * Status asesmen TIDAK berubah, hanya flag revision yang di-set.
     */
    public function rejectBiodata(Request $request, Asesmen $asesmen)
    {
        $request->validate([
            'rejection_notes' => 'required|string|min:10|max:1000',
        ], [
            'rejection_notes.required' => 'Catatan alasan penolakan wajib diisi.',
            'rejection_notes.min'      => 'Catatan minimal 10 karakter.',
        ]);

        if (!in_array($asesmen->status, [
            'pra_asesmen_started',
            'scheduled',
            'pra_asesmen_completed',
            'assessed',
        ])) {
            return response()->json([
                'success' => false,
                'message' => 'Biodata hanya bisa dikembalikan saat status asesmen sudah dimulai.',
            ], 422);
        }

        $asesmen->update([
            'biodata_rejection_notes' => $request->rejection_notes,
            'biodata_rejected_at'     => now(),
            'biodata_rejected_by'     => auth()->id(),
            'biodata_needs_revision'  => true,
            // Clear verified status karena dikembalikan
            'biodata_verified_at'     => null,
            'biodata_verified_by'     => null,
        ]);

        Log::info('[ADMIN][REJECT-BIODATA] Asesmen #' . $asesmen->id . ' biodata dikembalikan oleh Admin #' . auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Biodata berhasil dikembalikan ke asesi untuk direvisi.',
        ]);
    }

    // =========================================================================
    // APPROVE BIODATA (setelah asesi submit ulang — alias verifyBiodata)
    // =========================================================================

    public function approveBiodata(Request $request, Asesmen $asesmen)
    {
        if (!$asesmen->biodata_needs_revision) {
            return response()->json([
                'success' => false,
                'message' => 'Biodata tidak dalam status menunggu persetujuan.',
            ], 422);
        }

        return $this->verifyBiodata($request, $asesmen);
    }

    // =========================================================================
    // REJECT APL-01
    // =========================================================================

    /**
     * Admin menolak/mengembalikan APL-01 ke asesi untuk direvisi.
     */
    public function rejectApl01(Request $request, AplSatu $aplsatu)
    {
        $request->validate([
            'catatan' => 'required|string|min:10|max:1000',
        ], [
            'catatan.required' => 'Catatan alasan penolakan wajib diisi.',
            'catatan.min'      => 'Catatan minimal 10 karakter.',
        ]);

        if (!in_array($aplsatu->status, ['submitted', 'verified'])) {
            return response()->json([
                'success' => false,
                'message' => 'APL-01 hanya bisa dikembalikan dari status submitted atau verified.',
            ], 422);
        }

        $aplsatu->update([
            'status'          => 'returned',
            'verified_by'     => auth()->id(),
            'verified_at'     => now(),
            'rejection_notes' => $request->catatan,
        ]);

        Log::info('[ADMIN][REJECT-APL01] APL-01 #' . $aplsatu->id . ' dikembalikan oleh Admin #' . auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'APL-01 berhasil dikembalikan ke asesi untuk direvisi.',
        ]);
    }
}