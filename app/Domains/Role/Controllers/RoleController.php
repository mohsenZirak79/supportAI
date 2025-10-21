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
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // 2️⃣ تعیین مقدار allow_ticket
        $data = $request->all();
        $data['allow_ticket'] = $request->has('allow_ticket') ? 1 : 0;
        $data['allow_chat'] = $request->has('allow_chat') ? 1 : 0;
        $data['allow_users'] = $request->has('allow_users') ? 1 : 0;
        $data['allow_role'] = $request->has('allow_role') ? 1 : 0;
        $data['is_internal'] = $request->has('is_internal') ? 1 : 0;
        // 3️⃣ ایجاد رول
        $role = Role::create($data);
        $permissions = [];

        if ($data['allow_ticket']) {
            $permissions = array_merge($permissions, [
                'create-ticket', 'read-ticket', 'update-ticket', 'delete-ticket'
            ]);
        }
        if ($data['allow_chat']) {
            $permissions = array_merge($permissions, [
                'create-chat', 'read-chat', 'update-chat', 'delete-chat',
            ]);
        }
        if ($data['allow_users']) {
            $permissions = array_merge($permissions, [
                'create-user', 'read-user', 'update-user', 'delete-user',
            ]);
        }
        if ($data['allow_role']) {
            $permissions = array_merge($permissions, [
                'create-role', 'read-role', 'update-role', 'delete-role',
            ]);
        }

        $role->syncPermissions($permissions);

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
        $data['allow_chat'] = $request->has('allow_chat') ? 1 : 0;
        $data['allow_users'] = $request->has('allow_users') ? 1 : 0;
        $data['allow_role'] = $request->has('allow_role') ? 1 : 0;
        $data['is_internal'] = $request->has('is_internal') ? 1 : 0;
        dd($data);

        // 4️⃣ بروزرسانی نقش
        $role->update($data);
        $permissions = [];

        if ($data['allow_ticket']) {
            $permissions = array_merge($permissions, [
                'create-ticket', 'read-ticket', 'update-ticket', 'delete-ticket'
            ]);
        }
        if ($data['allow_chat']) {
            $permissions = array_merge($permissions, [
                'create-chat', 'read-chat', 'update-chat', 'delete-chat',
            ]);
        }
        if ($data['allow_users']) {
            $permissions = array_merge($permissions, [
                'create-user', 'read-user', 'update-user', 'delete-user',
            ]);
        }
        if ($data['allow_role']) {
            $permissions = array_merge($permissions, [
                'create-role', 'read-role', 'update-role', 'delete-role',
            ]);
        }

        $role->syncPermissions($permissions);


        // 6️⃣ بازگشت به صفحه لیست نقش‌ها با پیام موفقیت
        return redirect()->route('admin.roles')->with('success', 'نقش با موفقیت بروزرسانی شد.');
    }


    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('admin.roles')->with('success', 'نقش با موفقیت حذف شد.');
    }
}
