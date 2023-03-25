<?php

namespace App\Services;

use App\Models\JwtToken;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;

class AuthService implements Guard
{
    protected UnencryptedToken $token;

    protected Authenticatable $user;

    protected UserProvider $userProvider;
    public function __construct(UserProvider $userProvider, public Request $request)
    {
        $this->userProvider = $userProvider;
    }

    public function authenticate(array $credentials = []): array | false
    {
        try {
            if ($this->validate($credentials)) {
                $user = \Auth::user();

                $this->createNewToken($user);

                return [
                    'token' => $this->token->claims()->toString(),
                    'expires_at' => $this->token->claims()->get('iat'),
                    'user' => $user
                ];
            }

            return false;
        } catch (\Throwable $exception) {
            \Log::error($exception);

            return false;
        }
    }

    public function requestToken(User $user): JwtToken
    {
        $token = $this->createNewToken($user);

        //todo search and replace token with a new value
        // from db instead of creation of token on each request
        return JwtToken::create([
            'user_id' => $user->id,
            'unique_id' => $token->toString(),
            'token_title' => 'Bearer',
            'expires_at' => $token->claims()->get('iat'),
            'last_used_at' => now(),
            'refreshed_at' => null
        ]);
    }

    public function validateToken(string $tokenStr): bool
    {
        $parser = new Parser(new JoseEncoder());

        try {
            $token = $parser->parse($tokenStr);

//            if (! ($token instanceof UnencryptedToken)) {
//                throw new \Exception("Invalid Token!");
//            }
//
//            $this->token =  $token;

            return true;
        } catch (\Throwable $exception) {

            return false;
//            throw New \Exception("Invalid JWT Token");
        }
    }

    protected function createNewToken(User $user): UnencryptedToken
    {
        $tokenBuilder = (new Builder(new JoseEncoder(), ChainedFormatter::default()));
        $algorithm    = new Sha256();
        $signingKey = InMemory::base64Encoded(
            config('jwt.secret')
        );

        $time = new DateTimeImmutable();

        $tokenTTL = config('jwt.ttl');

        $this->token = $tokenBuilder
            ->issuedBy(config('app.url'))
            ->identifiedBy($user->uuid)
            ->issuedAt($time)
            ->expiresAt($time->modify("+{$tokenTTL} minutes"))
            ->withClaim('uid', $user->id)
            ->getToken($algorithm, $signingKey);

        return $this->token;
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
    public function user(): Authenticatable | null
    {
        return $this->user;
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
        if (! isset($credentials['email']) || ! isset($credentials['password'])) {
            $token = $this->request->header('authorization');
            [$tokenTitle, $token] = explode(' ', $token);
            $this->validateToken($token);

            $tokenCheckFromDB = JwtToken::where('unique_id', $tokenTitle)
                ->where('title', $tokenTitle)
                ->first();

            if ($tokenCheckFromDB) {
                $this->setUser($tokenCheckFromDB->user);

                return true;
            }
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
