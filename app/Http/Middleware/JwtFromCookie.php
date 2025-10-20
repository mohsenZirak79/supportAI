<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class JwtFromCookie
{
    public function handle($request, Closure $next)
    {
        Log::info('JwtFromCookie executing', [
            'has_cookie' => $request->hasCookie('jwt'),
            'cookie_value_sample' => substr((string)$request->cookie('jwt'), 0, 20)
        ]);

        if ($request->headers->has('Authorization')) {
            return $next($request);
        }

        $jwt = $request->cookie('jwt');
        $original_jwt = $jwt; // برای لاگ گرفتن از مقدار اصلی

        if (is_string($jwt)) {
            $jwt = trim($jwt, " \t\n\r\0\x0B\"'");
        }

        $dot_count = $jwt ? substr_count($jwt, '.') : 0;

        // ⬅️ لاگ گرفتن از وضعیت
        Log::info('JWT Debug Check', [
            'url' => $request->fullUrl(),
            'jwt_present' => (bool)$original_jwt,
            'trimmed_jwt_sample' => substr($jwt, 0, 30) . '...',
            'dot_count' => $dot_count,
            'condition_met' => ($jwt && $dot_count === 2)
        ]);

        // شرطی که قبلاً شکست می‌خورد، اینجا باید TRUE شود
        if ($jwt && $dot_count === 2) {
            $request->headers->set('Authorization', 'Bearer ' . $jwt);
        }

        return $next($request);
    }
}
