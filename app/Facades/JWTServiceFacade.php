<?php

namespace App\Facades;

use App\Models\JwtToken;
use App\Models\User;
use App\Services\JWTService;
use Illuminate\Support\Facades\Facade;

/**
 *
 * @method static JwtToken requestToken(User $user)
 * @method static JwtToken validateToken(string $tokenStr, string $title = null)
 *
 * @see \App\Services\JWTService
 */
class JWTServiceFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return JWTService::class;
    }
}
