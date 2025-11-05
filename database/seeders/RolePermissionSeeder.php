<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. نقش‌ها
        $roles = ['برنامه نویس', 'ادمین', 'کاربر عادی'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // 2. پرمیشن‌ها
        $permissions = [
            // CRUD یوزر
            'create-user', 'read-user', 'update-user', 'delete-user',
            // CRUD تیکت
            'create-ticket', 'read-ticket', 'update-ticket', 'delete-ticket',
            // چت
            'create-chat', 'read-chat', 'update-chat', 'delete-chat',
            // Role
            'create-role', 'read-role', 'update-role', 'delete-role',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 3. تخصیص پرمیشن‌ها به رول‌ها
        $programmer = Role::where('name', 'برنامه نویس')->first();
        $programmer->syncPermissions(Permission::all());

        $admin = Role::where('name', 'ادمین')->first();
        $admin->syncPermissions(Permission::all());

        /*$support = Role::where('name', 'پشتیبان')->first();
        $support->syncPermissions([
            'create-ticket', 'read-ticket', 'update-ticket', 'delete-ticket',
            'create-chat', 'read-chat', 'update-chat', 'delete-chat',
        ]);*/

        // 4. یوزرهای تستی + assign role
        $users = [
            ['name' => 'admin', 'password' => '123456789', 'role' => 'ادمین', 'phone' => '09123440912'],
            ['name' => 'programmer', 'password' => '123456789', 'role' => 'برنامه نویس', 'phone' => '09374376595'],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                [
                    'name' => $u['name'],
                    'password' => Hash::make($u['password']),
                    'phone' => $u['phone'],
                    'allow_ticket' => '1',
                    'allow_chat' => '1',
                    'allow_users' => '1',
                    'allow_role' => '1',
                    'is_internal' => '1',
                ]
            );

            // اتصال یوزر به نقش
            $user->assignRole($u['role']);
        }
    }
}
