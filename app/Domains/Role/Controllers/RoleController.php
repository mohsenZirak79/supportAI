<?php

namespace App\Domain\Role\Controllers;

use App\Domains\Role\Models\Role;

class RoleController
{
    public function all()
    {
        return Role::all();
    }

    public function find($id)
    {
        return Role::findOrFail($id);
    }

    public function create(array $data)
    {
        return Role::create($data);
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
