<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestChat
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            $fingerprint = $request->header('X-Device-Fingerprint') ?? \Illuminate\Support\Str::random(32); // Fallback
            $request->merge(['device_id' => $fingerprint]);
        }
        return $next($request);
    }
}
