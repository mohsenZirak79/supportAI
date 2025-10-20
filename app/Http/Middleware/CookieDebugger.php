<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CookieDebugger
{

    public function handle(Request $request, Closure $next): Response
    {
        // 1. کوکی را بررسی کن که آیا رمزنگاری شده است یا نه
        $jwt = $request->cookie('jwt');
        $is_encrypted = str_starts_with($jwt, 'eyJpdi');
        $dot_count = is_string($jwt) ? substr_count($jwt, '.') : 0;

        // 2. اگر رمزنگاری شده باشد، Execution را متوقف کن
        if ($is_encrypted) {
            // dd("FAIL: Cookie is encrypted, Debugger stopped execution.");
            // 🚨 برای اینکه مطمئن شویم از لاگ استفاده می‌کنیم
            Log::error('FATAL DEBUG: JWT is encrypted. Execution stopped to prevent further action.', [
                'cookie_value_start' => substr($jwt, 0, 30),
                'dot_count' => $dot_count
            ]);
        }

        // 3. اگر خام باشد، اجازه ادامه بده
        if ($dot_count === 2) {
            Log::info('SUCCESS DEBUG: JWT is RAW and has 2 dots. Execution continues.', [
                'cookie_value_start' => substr($jwt, 0, 30),
            ]);
        }


        // در حالت عادی، صرفاً اجازه ادامه می‌دهد
        return $next($request);
    }
}
