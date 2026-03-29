<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show profile — redirect ke view sesuai role
     */
    public function show()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return view('profile.admin', compact('user'));
        }

        if ($user->isTuk()) {
            $user->load('tuk');
            return view('profile.tuk', compact('user'));
        }

        if ($user->isAsesor()) {
            $user->load('asesor');
            return view('profile.asesor', compact('user'));
        }

        if ($user->isAsesi()) {
            $user->load('asesmen');
            return view('profile.asesi', compact('user'));
        }

        return view('profile.admin', compact('user'));
    }

    /**
     * Update info dasar (name / email) — berlaku untuk semua role
     */
    public function updateInfo(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        // Sinkronisasi ke tabel terkait per role
        if ($user->isTuk() && $user->tuk) {
            $user->tuk->update(['name' => $request->name]);
        }

        if ($user->isAsesor() && $user->asesor) {
            $user->asesor->update(['nama' => $request->name]);
        }

        return back()->with('success', 'Informasi profil berhasil diupdate!');
    }

    /**
     * Update password — berlaku untuk semua role
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        $user->update([
            'password'            => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        return back()->with('success', 'Password berhasil diubah!');
    }

    /**
     * Upload foto profil (User.photo_path) — admin, tuk, asesi
     * Asesor punya foto di tabel asesors (foto_path) — pakai uploadFotoAsesor
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = auth()->user();

        // hapus foto lama
        if ($user->photo_path) {
            Storage::disk('public')->delete($user->photo_path);
        }

        // simpan foto baru
        $path = $request->file('photo')->store('profile-photos', 'public');

        $user->update([
            'photo_path' => $path
        ]);

        return back()->with('success', 'Foto profil berhasil diupdate!');
    }

    /**
     * Hapus foto profil
     */
    public function deletePhoto()
    {
        $user = auth()->user();

        if ($user->photo_path) {
            Storage::disk('public')->delete($user->photo_path);
            $user->update(['photo_path' => null]);
            return back()->with('success', 'Foto profil berhasil dihapus!');
        }

        return back()->with('error', 'Tidak ada foto profil untuk dihapus.');
    }

}