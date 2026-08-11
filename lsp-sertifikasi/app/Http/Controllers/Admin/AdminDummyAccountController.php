<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DummyAccountResetService;
use Illuminate\Support\Facades\Log;

class AdminDummyAccountController extends Controller
{
    public function reset(DummyAccountResetService $service)
    {
        try {
            $result = $service->reset();

        return response()->json([
            'success' => true,
            'message' => "Akun dummy berhasil direset. {$result['asesi_direset']} akun asesi, {$result['asesmen_deleted']} asesmen, dan {$result['schedule_deleted']} jadwal simulasi dihapus/dibersihkan.",
        ]);
        } catch (\Throwable $e) {
            Log::error('Reset dummy account gagal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal reset: ' . $e->getMessage(),
            ], 500);
        }
    }
}