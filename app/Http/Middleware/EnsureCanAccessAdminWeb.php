<?php

namespace App\Http\Middleware;

use App\Domains\Shared\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * فقط کاربران داخلی/پشتیبان (نقش ادمین یا برنامه‌نویس، یا داشتن حداقل یکی از پرمیشن‌های پنل)
 * اجازهٔ ورود به مسیرهای وب پنل ادمین و /profile ادمین را دارند.
 */
class EnsureCanAccessAdminWeb
{
    /** نقش‌هایی که به‌طور پیش‌فرض به کل پنل دسترسی دارند (با Spatie هم‌نام دیتابیس) */
    private const STAFF_ROLE_NAMES = ['ادمین', 'برنامه نویس'];

    /** حداقل یکی از این پرمیشن‌ها برای نقش‌های سفارشی (مثلاً پشتیبان آینده) */
    private const STAFF_PERMISSION_FALLBACKS = [
        'read-user',
        'read-role',
        'read-ticket',
        'read-chat',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web');

        if (!$user instanceof User) {
            return redirect()->guest(route('login'));
        }

        if (self::userMayAccessAdminWeb($user)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'دسترسی به پنل مدیریت ندارید.');
        }

        return redirect()
            ->route('user.profile')
            ->with('warning', 'دسترسی به بخش مدیریت برای حساب شما فعال نیست.');
    }

    public static function userMayAccessAdminWeb(User $user): bool
    {
        foreach (self::STAFF_ROLE_NAMES as $roleName) {
            if ($user->hasRole($roleName)) {
                return true;
            }
        }

        foreach (self::STAFF_PERMISSION_FALLBACKS as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }
}
