<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
                
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda tidak aktif. Hubungi administrator.',
                ]);
            }

            // ✅ PERBAIKAN: Jangan logout, redirect ke halaman verifikasi
            if ($user->role === 'asesi' && $user->mustVerifyEmail() && !$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice')
                    ->with('warning', 'Harap verifikasi email Anda terlebih dahulu.');
            }

            switch ($user->role) {
                case 'admin':
                    return redirect()->intended(route('admin.dashboard'));
                case 'tuk':
                    return redirect()->intended(route('tuk.dashboard'));
                case 'asesi':
                    if ($user->isFirstLogin()) {
                        return redirect()->route('asesi.first-login');
                    }
                    return redirect()->intended(route('asesi.dashboard'));
                default:
                    return redirect('/');
            }
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /**
     * Show register form (only for Asesi)
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'asesi',
            'is_active' => true,
            'password_changed_at' => now(), // Mandiri user sudah set password sendiri
        ]);

        // ✅ Trigger email verification
        try {
            event(new Registered($user));
            Log::info("Email verification sent - User #{$user->id}, Email: {$user->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send verification email: " . $e->getMessage());
        }

        // ✅ AUTO LOGIN setelah register
        Auth::login($user);
        
        // ✅ Regenerate session untuk keamanan
        $request->session()->regenerate();

        // ✅ Redirect langsung ke halaman verify email
        return redirect()->route('verification.notice')
            ->with('success', 'Registrasi berhasil! Silakan cek email Anda untuk verifikasi akun.');
    }
    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $userName = Auth::user()->name ?? 'Unknown';
        
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info("User logged out: {$userName}");

        return redirect()->route('login')->with('success', 'Berhasil logout!');
    }

    // ==================== PASSWORD RESET ====================

    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Link reset password telah dikirim ke email Anda!')
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show reset password form
     */
    public function showResetPassword(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password berhasil direset! Silakan login.')
            : back()->withErrors(['email' => [__($status)]]);
    }
}