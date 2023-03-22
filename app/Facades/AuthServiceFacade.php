<?php

namespace App\Facades;

use App\Services\AuthService;
use Illuminate\Support\Facades\Facade;

class AuthServiceFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AuthService::class;
    }
}
