<?php

namespace App\Domains\Permission\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name', 'guard_name',
    ];
}
