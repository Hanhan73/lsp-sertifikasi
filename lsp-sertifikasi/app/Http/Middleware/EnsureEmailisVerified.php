<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Skip verification check for non-asesi users
        if (!$user || $user->role !== 'asesi') {
            return $next($request);
        }

        // ✅ Load asesmen untuk cek is_collective
        $user->load('asesmen');

        // Skip verification for collective users
        if ($user->asesmen && $user->asesmen->is_collective) {
            return $next($request);
        }

        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}