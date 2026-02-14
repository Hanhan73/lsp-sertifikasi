<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFirstLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // ✅ Only apply to asesi role
        if (!$user || !$user->isAsesi()) {
            return $next($request);
        }

        // ✅ Check if first login
        if ($user->isFirstLogin()) {
            // Allow access to first-login routes only
            if ($request->routeIs('asesi.first-login')) {
                return $next($request);
            }

            // Redirect all other routes to first login
            return redirect()->route('asesi.first-login')
                ->with('info', 'Silakan ubah password default Anda terlebih dahulu.');
        }

        return $next($request);
    }
}