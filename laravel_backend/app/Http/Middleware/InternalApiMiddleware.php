<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log; // <--- PASTIKAN INI ADA

class InternalApiMiddleware
{
    public function handle($request, Closure $next)
{
    $internalKey = trim($request->header('X-INTERNAL-KEY'));
    $expected = trim(config('services.internal_api_key'));

    if ($internalKey !== $expected) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    // 🔥 lanjut ke controller
    return $next($request);
}
}