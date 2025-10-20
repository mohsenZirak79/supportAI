<?php

use Illuminate\Support\Facades\Broadcast;

// این خط را به ابتدای فایل اضافه کن تا مسیر /broadcasting/auth با میدلورها ثبت شود:
Broadcast::routes([
    'middleware' => ['api', 'jwt.cookie', 'auth:jwt'], // ← فقط همین‌ها
]);

//// حالا قوانین کانال‌ها:
//// نمونه ۱: کانال عمومیِ فقط لاگین‌کرده‌ها
//Broadcast::channel('private-any-authenticated', function ($user) {
//    return (bool) $user;
//});
//
//// نمونه ۲: اگر واقعاً می‌خوای به نقش وصلش کنی (اسم کانال‌ت اگر همینه)
//Broadcast::channel('role.support_technical', function ($user) {
//    return $user && $user->hasRole('support_technical');
//});
//
//// یا اگر کلاینت از الگوها استفاده می‌کند:
//Broadcast::channel('role.{role}', function ($user, $role) {
//    return $user && $user->hasRole($role);
//});
