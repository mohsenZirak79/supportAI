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
        $roles = ['برنامه نویس', 'ادمین', 'کاربر', 'پشتیبان'];
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

        $support = Role::where('name', 'پشتیبان')->first();
        $support->syncPermissions([
            'create-ticket', 'read-ticket', 'update-ticket', 'delete-ticket',
            'create-chat', 'read-chat', 'update-chat', 'delete-chat',
        ]);

        $userRole = Role::where('name', 'کاربر')->first();
        $userRole->syncPermissions([
            'read-ticket',
            'create-chat', 'read-chat',
        ]);

        // 4. یوزرهای تستی + assign role
        $users = [
            ['name' => 'Ali', 'email' => 'ali@example.com', 'password' => '123456789', 'phone' => '09123456781', 'role' => 'برنامه نویس'],
            ['name' => 'Sara', 'email' => 'sara@example.com', 'password' => '123456789', 'phone' => '09123456782', 'role' => 'ادمین'],
            ['name' => 'Reza', 'email' => 'reza@example.com', 'password' => '123456789', 'phone' => '09123456783', 'role' => 'کاربر'],
            ['name' => 'Neda', 'email' => 'neda@example.com', 'password' => '123456789', 'phone' => '09123456784', 'role' => 'پشتیبان'],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make($u['password']),
                    'phone' => $u['phone'],
                ]
            );

            // اتصال یوزر به نقش
            $user->assignRole($u['role']);
        }
    }
}
