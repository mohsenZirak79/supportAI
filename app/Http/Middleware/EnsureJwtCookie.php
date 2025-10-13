<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureJwtCookie
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->cookie('jwt')) {
            return redirect()->route('login');
        }
        return $next($request);
    }
}
