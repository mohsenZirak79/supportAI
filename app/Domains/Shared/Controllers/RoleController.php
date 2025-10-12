<?php

namespace App\Domains\Shared\Controllers;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
class RoleController extends Controller
{
    public function getSupportRoles(Request $request)
    {
        // اگر خواستی از پارامتر model برای فیلترهای آینده استفاده کنی، الان در دسترسه:
        $model = $request->get('model'); // مثلاً 'ticket'

        // فقط نقش‌هایی که اجازه‌ی تیکت دارند
        $roles = Role::query()
            ->where('allow_ticket', '1')
            ->orderBy('name')
            ->get(['id', 'name']);

        // خروجی استاندارد برای فرانت (id = uuid/عدد، name = لیبل فارسی یا نام نقش)
        $payload = $roles->map(fn($r) => [
            'id'   => (string) $r->id,   // ممکنه uuid یا عدد باشه؛ فرانت هندل می‌کنه
            'name' => (string) $r->name, // لیبل فارسی/نام نقش
        ]);

        return response()->json($payload, 200);
    }
}
