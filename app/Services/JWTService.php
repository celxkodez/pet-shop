<?php

namespace App\Services;

use App\Models\JwtToken;
use App\Models\User;
use App\Utils\Clock;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\HasClaimWithValue;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\Validator;
use Psr\Clock\ClockInterface;

class JWTService
{
    protected UnencryptedToken $token;

    private InMemory $signingKey;
    private Signer $algorithm;

    public function __construct()
    {
        $this->signingKey = InMemory::base64Encoded(
            config('jwt.secret')
        );

        $this->algorithm = new Sha256();
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
            'expires_at' => $token->claims()->get('exp'),
            'last_used_at' => now(),
            'refreshed_at' => null
        ]);
    }

    public function validateToken(string $tokenStr, string $title = null): JwtToken | false
    {

        $parser = new Parser(new JoseEncoder());

        try {
            $token = $parser->parse($tokenStr);

            $tokenModel = JwtToken::query();
            $tokenModel->where('unique_id', $token->toString());

            if ($title) {
                $tokenModel->where('token_title', $title);
            }

            $tokenModel = $tokenModel->first();

            $validator = new Validator();

            $validationConditions = $tokenModel && $tokenModel->user !== null;

            if (!$validationConditions) {
                return false;
            }

            $validationConditions &= ! $token->isExpired(now());

            $validationConditions &= $validator
                ->validate($token, new SignedWith($this->algorithm, $this->signingKey));

            $validationConditions &= $validator
                ->validate($token, new IdentifiedBy($tokenModel->user->uuid));

            $validationConditions &= $validator
                ->validate($token, new HasClaimWithValue('uid',$tokenModel->user->id));

            if (!$validationConditions) {

                $tokenModel->delete();

                return  false;
            }

            return $tokenModel;
        } catch (\Throwable $exception) {

            //Log::error($exception);
            return false;
        }
    }

    protected function createNewToken(User $user): UnencryptedToken
    {
        $tokenBuilder = (new Builder(new JoseEncoder(), ChainedFormatter::default()));

        $time = new \DateTimeImmutable();

        $tokenTTL = config('jwt.ttl');

        $this->token = $tokenBuilder
            ->issuedBy(config('app.url'))
            ->identifiedBy($user->uuid)
            ->issuedAt($time)
            ->expiresAt($time->modify("+{$tokenTTL} minutes"))
            ->withClaim('uid', $user->id)
            ->getToken($this->algorithm, $this->signingKey);

        return $this->token;
    }

    /**
     * Revokes Token
     *
     * @param string $token
     * @return bool
     */
    public function revokeToken(string $token): bool
    {
        if ($token = $this->validateToken($token)) {

            return (bool) $token->delete();
        }

        return true;
    }
}
