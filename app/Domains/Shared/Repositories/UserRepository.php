<?php

namespace App\Domains\Shared\Repositories;

use App\Domains\Shared\Models\User;
use Spatie\QueryBuilder\QueryBuilder;

interface UserRepositoryInterface
{
    public function findById($id);
    // etc.
}
class UserRepository implements UserRepositoryInterface
{
    public function findById($id)
    {
        return QueryBuilder::for(User::class)
            ->allowedFilters(['kyc_status'])
            ->findOrFail($id);
    }
}
