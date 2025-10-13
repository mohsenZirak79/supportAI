<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermissionForRoute
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth('web')->user();

        if (!$user) {
            abort(403, 'دسترسی ندارید');
        }

        // Map routes to permissions
        $routePermissions = [
            'admin.users'   => 'read-user',
            'admin.roles'   => 'read-role',
            'admin.tickets' => 'read-ticket',
            'admin.chats'   => 'read-chat',
        ];

        $currentRouteName = $request->route()->getName();

        // اگر روت داخل آرایه هست و کاربر پرمیشن نداره => خطای 403
        if (isset($routePermissions[$currentRouteName]) && !$user->can($routePermissions[$currentRouteName])) {
            abort(403, 'دسترسی ندارید');
        }

        return $next($request);
    }
}
