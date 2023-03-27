<?php

namespace App\Services;

use App\Facades\JWTServiceFacade;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class JWTGuard implements Guard
{

    protected ?Authenticatable $user;

    protected UserProvider $userProvider;
    public function __construct(UserProvider $userProvider, public Request $request)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        return (bool)$this->user();
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest(): bool
    {
        return ! $this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return Authenticatable|null
     */
    public function user(): ?Authenticatable
    {

        if (is_null($this->user ?? null) && $token = $this->request->header('authorization')) {

            [$tokenTitle, $token] = explode(' ', $token);

            if (! ($tokenTitle && $token)) {
                return null;
            }

            $token = JWTServiceFacade::validateToken($token, $tokenTitle);

            if (!$token) {
                return null;
            }

            return $token->user;
        }

        return $this->user ?? null;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id(): int|string|null
    {
        $user = $this->user();

        return $user?->getAuthIdentifier();
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        //todo modify this check to include other other attributes rather email and password
        if (! isset($credentials['email']) || ! isset($credentials['password'])) {
            return false;
        }

        $user  = $this->userProvider->retrieveByCredentials($credentials);

        if ($user && $this->userProvider->validateCredentials($user,$credentials)) {

            $this->setUser($user);

            return true;
        }

        return false;
    }

    /**
     * Determine if the guard has a user instance.
     *
     * @return bool
     */
    public function hasUser(): bool
    {
        return $this->check();
    }

    /**
     * Set the current user.
     *
     * @param  Authenticatable  $user
     * @return void
     */
    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }
}
