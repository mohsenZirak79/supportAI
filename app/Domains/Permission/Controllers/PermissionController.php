<?php

namespace App\Domain\Permission\Controllers;

use App\Domains\Permission\Models\Permission;

class PermissionController
{
    public function all()
    {
        return Permission::all();
    }

    public function find($id)
    {
        return Permission::findOrFail($id);
    }

    public function create(array $data)
    {
        return Permission::create($data);
    }

    public function update(Permission $permission, array $data)
    {
        $permission->update($data);
        return $permission;
    }

    public function delete(Permission $permission)
    {
        return $permission->delete();
    }
}
