<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// ⬇️ اینها را اضافه کن
use Illuminate\Cookie\Middleware\EncryptCookies as FrameworkEncryptCookies;
use App\Http\Middleware\EncryptCookies as AppEncryptCookies;
use App\Http\Middleware\JwtFromCookie;
use App\Http\Middleware\CookieDebugger;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // (اختیاری) فقط برای لاگ
        $middleware->prependToGroup('api', CookieDebugger::class);

        // ✅ مهم‌ترین خط: EncryptCookies پیش‌فرض لاراول را
        //    در گروه web با نسخه‌ی خودت که `$except = ['jwt']` دارد، «جایگزین» کن
        $middleware->replaceInGroup('web', FrameworkEncryptCookies::class, AppEncryptCookies::class);

        // ✅ گروه web: نیاز به JwtFromCookie در web نداری، ولی اگر می‌خواهی بگذار مشکلی نیست
        // $middleware->web(append: [JwtFromCookie::class]);

        // ✅ گروه api: اصلاً EncryptCookies نگذار، فقط JwtFromCookie
        $middleware->api(append: [
            JwtFromCookie::class,
        ]);

        // alias ها بمانند
        $middleware->alias([
            'jwt.cookie' => JwtFromCookie::class,
            'ensure.jwt.cookie' => \App\Http\Middleware\EnsureJwtCookie::class,
            'checkPermissionForRoute' => \App\Http\Middleware\CheckPermissionForRoute::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
