<?php

namespace Tests\Unit;

use App\Facades\JWTServiceFacade;
use App\Models\JwtToken;
use App\Models\User;
use App\Services\JWTService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
class JWTServiceTest extends TestCase
{
    use DatabaseMigrations;

    public function test_jwt_token_can_be_created()
    {
        $user = User::factory()->create();

        $token = JWTServiceFacade::requestToken($user);

        $this->assertIsString($token->unique_id);

        $this->assertInstanceOf(JwtToken::class, $token);
    }

    public function test_jwt_token_can_be_validated()
    {
        $user = User::factory()->create();

        $token = JWTServiceFacade::requestToken($user);

        $token = $token->unique_id;

        $this->assertIsString($token);
        $this->assertInstanceOf(JwtToken::class, JWTServiceFacade::validateToken($token));
    }

    public function test_jwt_token_can_be_revoked()
    {
        $user = User::factory()->create();

        $token = JWTServiceFacade::requestToken($user);

        $this->assertInstanceOf(JwtToken::class, $token);
        $token = $token->unique_id;

        $this->assertIsString($token);
        $this->assertInstanceOf(JwtToken::class, JWTServiceFacade::validateToken($token));
        $this->assertTrue(JWTServiceFacade::revokeToken($token));
        $this->assertFalse(JWTServiceFacade::validateToken($token));
    }
}
