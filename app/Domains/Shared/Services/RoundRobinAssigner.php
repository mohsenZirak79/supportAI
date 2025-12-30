<?php

namespace App\Domains\Shared\Services;

use App\Domains\Role\Models\Role;
use App\Domains\Shared\Models\User;
use Illuminate\Support\Facades\Cache;

class RoundRobinAssigner
{
    public function pickActiveUserIdForRole(string $role): ?string
    {
        if (ctype_digit($role)) {
            $roleModel = Role::query()->find((int) $role);
            if ($roleModel) {
                $role = $roleModel->name;
            }
        }

        $users = User::role($role)
            ->orderBy('created_at')
            ->get(['id']);

        if ($users->isEmpty()) {
            return null;
        }
        $ids = $users->pluck('id')->values();
        $cacheKey = "rr:role:{$role}";
        $lastAssignedId = Cache::get($cacheKey);

        if (!$lastAssignedId) {
            $nextId = $ids->first();
            Cache::forever($cacheKey, $nextId);
            return $nextId;
        }

        $index = $ids->search($lastAssignedId);
        if ($index === false || $index + 1 >= $ids->count()) {
            $nextId = $ids->first();
        } else {
            $nextId = $ids->get($index + 1);
        }

        Cache::forever($cacheKey, $nextId);
        return $nextId;
    }
}
