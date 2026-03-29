<?php
// app/Http/Controllers/SignatureController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SignatureController extends Controller
{
    /**
     * Simpan TTD ke profil user (dipanggil AJAX dari signature pad).
     */
    public function store(Request $request)
    {
        $request->validate([
            'signature' => 'required|string',
        ]);

        $user = auth()->user();

        // Strip prefix jika ada, simpan raw base64
        $sig = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature);

        $user->update(['signature' => $sig]);

        Log::info('[SIGNATURE] Saved for user #' . $user->id);

        return response()->json([
            'success'        => true,
            'message'        => 'Tanda tangan berhasil disimpan ke profil.',
            'signature_image' => $user->signature_image,
        ]);
    }

    /**
     * Hapus TTD dari profil user.
     */
    public function destroy(Request $request)
    {
        auth()->user()->update(['signature' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Tanda tangan dihapus dari profil.',
        ]);
    }
}