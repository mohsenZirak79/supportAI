<?php

namespace App\Http\Middleware;

use App\Domains\Shared\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForceTestUser
{
    public function handle(Request $request, Closure $next)
    {
        // فقط در محیط local و اگر فلگ فعال باشه
        if (app()->environment('local') && config('app.force_test_user')) {
            $testUserId = config('app.test_user_id');
            if ($testUserId) {
                $user = User::find($testUserId);
                if ($user) {
                    Auth::setUser($user);
                }
            }
        }

        return $next($request);
    }
}
