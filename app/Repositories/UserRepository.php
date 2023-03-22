<?php

namespace App\Repositories;

use App\Contracts\RepositoryInterfaces\UserRepositoryContract;
use App\Models\User;

class UserRepository extends BaseRepository implements UserRepositoryContract
{

    protected function getModelClass(): string
    {
        return User::class;
    }
}
