<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class BaseTestClass extends TestCase
{
    use DatabaseMigrations;

    protected array $requestHeaders = [
        'Accept' => 'application/json',
    ];

    protected function loginUser(string $type = 'user')
    {
        if ($type === 'user') {
            $user = User::factory()->create();

            $route = "user/login";
        } else {
            $user = User::factory()
                ->state([
                    'is_admin' => true
                ])
                ->create();

            $route = "admin/login";
        }

        $response = $this->post("/api/v1/$route", [
            'email' => $user->email,
            'password' => 'password'
        ], $this->requestHeaders);

        $response = $response->json()['data'];

        $this->requestHeaders['Authorization'] = "Bearer " . $response['token'];

        return $user;
    }

    protected function successPayload(array $data = [], bool $structure = false): array
    {
        $expected = [
            'data' => [],
            'status' => true,
            'error' => null,
            'errors' => [],
            'extra' => [],
        ];

        if ($structure) {
            return array_merge( array_keys($expected), $data);
        }

        return array_merge( $expected, $data);
    }
}
