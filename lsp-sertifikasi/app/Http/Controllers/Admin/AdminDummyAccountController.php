<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DummyAccountResetService;
use App\Services\DummySimulationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminDummyAccountController extends Controller
{
    public function reset(Request $request, DummyAccountResetService $service)
    {
        $request->validate([
            'scope' => 'required|in:all,tuk,asesor,asesi_mandiri,asesi_kolektif',
        ]);

        try {
            $result = $service->reset($request->scope);
            return response()->json([
                'success' => true,
                'message' => "Reset selesai. {$result['akun_direset']} akun, {$result['asesmen_deleted']} asesmen, {$result['schedule_deleted']} jadwal dibersihkan.",
            ]);
        } catch (\Throwable $e) {
            Log::error('Reset dummy account gagal: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal reset: ' . $e->getMessage()], 500);
        }
    }

    public function createSimulasi(Request $request, DummySimulationService $service)
    {
        $request->validate([
            'skema_id' => 'required|exists:skemas,id',
            'jenis'    => 'required|in:mandiri,kolektif',
        ]);

        try {
            $asesmen = $service->createAsesmen($request->skema_id, $request->jenis);
            return response()->json([
                'success' => true,
                'message' => "Asesmen simulasi ({$request->jenis}) berhasil dibuat.",
                'asesmen_id' => $asesmen->id,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}