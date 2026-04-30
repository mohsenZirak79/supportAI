<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * کنترل ریزدانهٔ دسترسی روی نام روت.
 * فرض: قبل از این میان‌افزار، {@see EnsureCanAccessAdminWeb} اجرا شده و کاربر «کارمند پنل» است.
 */
class CheckPermissionForRoute
{
    /**
     * نام روت => نام پرمیشن Spatie، یا null یعنی فقط همان گیت کارمند کافی است.
     *
     * @var array<string, string|null>
     */
    private const ROUTE_PERMISSIONS = [
        // پنل ادمین
        'admin.users' => 'read-user',
        'admin.roles' => 'read-role',
        'admin.tickets' => 'read-ticket',
        'admin.tickets.show' => 'read-ticket',
        'admin.tickets.reply' => 'update-ticket',
        'admin.chats' => 'read-chat',
        'admin.chats.detail' => 'read-chat',
        'admin.referrals.respond' => 'update-chat',
        'admin.referrals.assign_me' => 'update-chat',
        'admin.notifications.index' => null,
        'admin.notifications.read' => null,
        'admin.notifications.read-all' => null,
        'admin.dashboard' => null,

        // مدیریت کاربران (وب)
        'users.index' => 'read-user',
        'users.create' => 'create-user',
        'users.store' => 'create-user',
        'users.edit' => 'read-user',
        'users.update' => 'update-user',
        'users.destroy' => 'delete-user',

        // مدیریت نقش‌ها (وب)
        'roles.index' => 'read-role',
        'roles.create' => 'create-role',
        'roles.store' => 'create-role',
        'roles.edit' => 'read-role',
        'roles.update' => 'update-role',
        'roles.destroy' => 'delete-role',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = auth('web')->user();

        if (!$user) {
            abort(403, 'دسترسی ندارید');
        }

        $route = $request->route();
        if (!$route) {
            return $next($request);
        }

        $name = $route->getName();

        if (array_key_exists($name, self::ROUTE_PERMISSIONS)) {
            $permission = self::ROUTE_PERMISSIONS[$name];
            if ($permission !== null && !$user->can($permission)) {
                abort(403, 'دسترسی ندارید');
            }

            return $next($request);
        }

        if ($this->isStrictPanelRouteName($name)) {
            abort(403, 'دسترسی ندارید');
        }

        return $next($request);
    }

    private function isStrictPanelRouteName(string $name): bool
    {
        return str_starts_with($name, 'admin.')
            || str_starts_with($name, 'users.')
            || str_starts_with($name, 'roles.');
    }
}
