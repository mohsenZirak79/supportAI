<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JwtFromCookie
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->bearerToken() && $request->cookies->has('jwt')) {
            $request->headers->set('Authorization', 'Bearer '.$request->cookies->get('jwt'));
        }
        return $next($request);
    }
}
