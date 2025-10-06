<?php

namespace App\Domains\Role\Controllers;

use App\Domains\Role\Models\Role;
use Illuminate\Http\Request;


class RoleController
{
    public function all()
    {
        return Role::where('id', '!=', 1)->get();
    }

    public function find($id)
    {
        return Role::findOrFail($id);
    }

    public function store(Request $request)
    {
        // 1️⃣ اعتبارسنجی فیلدها
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // 2️⃣ تعیین مقدار allow_ticket
        $data = $request->all();
        $data['allow_ticket'] = $request->has('allow_ticket') ? 1 : 0;

        // 3️⃣ ایجاد رول
        $role = Role::create($data);

        // 4️⃣ اگر allow_ticket فعال بود، پرمیشن‌ها رو سینک کن
        if ($data['allow_ticket']) {
            $role->syncPermissions([
                'create-ticket', 'read-ticket', 'update-ticket', 'delete-ticket',
                'create-chat', 'read-chat', 'update-chat', 'delete-chat',
            ]);
        }

        return redirect()->route('admin.roles');
    }


    public function update(Request $request, Role $role)
    {
        // 1️⃣ جلوگیری از ویرایش نقش‌های سیستمی
        if (in_array($role->id, [1, 2, 3])) {
            return redirect()->back()->withErrors(['error' => 'ویرایش این نقش مجاز نیست.']);
        }

        // 2️⃣ اعتبارسنجی داده‌ها
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // 3️⃣ آماده‌سازی داده‌ها
        $data = $request->all();

        $data['allow_ticket'] = $request->has('allow_ticket') ? 1 : 0;

        // 4️⃣ بروزرسانی نقش
        $role->update($data);

        // 5️⃣ بروزرسانی پرمیشن‌ها در صورت نیاز
        if ($data['allow_ticket']) {
            $role->syncPermissions([
                'create-ticket', 'read-ticket', 'update-ticket', 'delete-ticket',
                'create-chat', 'read-chat', 'update-chat', 'delete-chat',
            ]);
        } else {
            // اگر allow_ticket غیرفعال شد، پرمیشن‌های مرتبط حذف شوند
            $role->revokePermissionTo([
                'create-ticket', 'read-ticket', 'update-ticket', 'delete-ticket',
                'create-chat', 'read-chat', 'update-chat', 'delete-chat',
            ]);
        }

        // 6️⃣ بازگشت به صفحه لیست نقش‌ها با پیام موفقیت
        return redirect()->route('admin.roles')->with('success', 'نقش با موفقیت بروزرسانی شد.');
    }


    public function delete(Role $permission)
    {
        return $permission->delete();
    }
}
