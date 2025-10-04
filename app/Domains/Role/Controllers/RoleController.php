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


    public function update(Role $permission, array $data)
    {
        $permission->update($data);
        return $permission;
    }

    public function delete(Role $permission)
    {
        return $permission->delete();
    }
}
