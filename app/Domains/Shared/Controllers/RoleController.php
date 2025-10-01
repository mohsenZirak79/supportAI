<?php

namespace App\Domains\Shared\Controllers;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function getSupportRoles()
    {
        // فقط نقش‌های پشتیبانی (طبق PDF Agent: support_agent, senior_agent, supervisor)
//        $roles = Role::whereIn('name', [
//            'support_sales',
//            'support_finance',
//            'support_technical',
//            'support_agent',
//            'senior_agent',
//            'supervisor'
//        ])->get(['name as id', 'name']);
//
//        // تبدیل به لیبل‌های فارسی (اختیاری)
//        $labels = [
//            'support_sales' => 'پشتیبانی فروش',
//            'support_finance' => 'پشتیبانی مالی',
//            'support_technical' => 'پشتیبانی فنی',
//            'support_agent' => 'پشتیبان عمومی',
//            'senior_agent' => 'پشتیبان ارشد',
//            'supervisor' => 'سرپرست'
//        ];
//
//        $roles = $roles->map(function ($role) use ($labels) {
//            $role->label = $labels[$role->id] ?? $role->id;
//            return $role;
//        });
//        return response()->json($roles);
        $departments = [
            'support_website' => 'پشتیبانی وب سایت',
            'support_sales' => 'پشتیبانی فروش',
            'support_admin' => 'پشتیبانی اداری',
            'support_finance' => 'پشتیبانی مالی'
        ];

// تبدیل آرایه به فرمت مورد نیاز
        $formattedDepartments = array_map(function ($key, $value) {
            return [
                'id' => $key,
                'name' => $value
            ];
        }, array_keys($departments), $departments);

        return response()->json($formattedDepartments);

    }
}
