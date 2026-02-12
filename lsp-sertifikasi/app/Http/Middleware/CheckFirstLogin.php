<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFirstLogin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Only check for collective asesi (not mandiri)
        if ($user && $user->isFirstLogin()) {
            // Allow access to first-login routes
            if ($request->routeIs('asesi.first-login') || 
                $request->routeIs('asesi.update-first-password')) {
                return $next($request);
            }

            // Redirect to first login page
            return redirect()->route('asesi.first-login')
                ->with('info', 'Silakan ubah password default Anda terlebih dahulu.');
        }

        return $next($request);
    }
}