<?php

namespace App\Domains\Role\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Morilog\Jalali\Jalalian;

class Role extends SpatieRole
{
    protected $fillable = [
        'name', 'guard_name','allow_ticket' , 'allow_chat' , 'allow_users' , 'allow_role' , 'is_internal'
    ];

    protected $appends = ['created_at_jalali'];

    public function getCreatedAtJalaliAttribute()
    {
        return $this->created_at
            ? Jalalian::forge($this->created_at)->format('Y/m/d')
            : null;
    }
}
