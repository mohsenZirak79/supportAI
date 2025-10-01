<?php

namespace App\Domains\Role\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name', 'guard_name',
    ];
}
