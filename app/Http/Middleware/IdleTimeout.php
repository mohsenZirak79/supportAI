<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdleTimeout
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = cache('last_activity_' . Auth::id());
            if ($lastActivity && now()->diffInMinutes($lastActivity) > 10) {
                Auth::logout();
                return response()->json(['error' => ['code' => 'SESSION_EXPIRED', 'message' => 'Session expired due to inactivity']], 401);
            }
            cache(['last_activity_' . Auth::id() => now()], 10);
        }
        return $next($request);
    }
}
