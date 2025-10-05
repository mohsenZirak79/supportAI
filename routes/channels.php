<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('role.{role}', function ($user, string $role) {
    // فقط کاربرانی که این نقش را دارند، به این کانال دسترسی داشته باشند
    return $user && $user->hasRole($role);
});
