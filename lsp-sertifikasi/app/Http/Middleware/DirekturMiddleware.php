<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DirekturMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'direktur') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Hanya Direktur yang dapat mengakses halaman ini.'], 403);
            }
            abort(403, 'Akses ditolak. Hanya Direktur yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}