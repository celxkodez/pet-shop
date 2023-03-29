<?php

namespace Tests\Feature;

use App\Models\User;

class UseEndpointsRequestTest extends BaseTestClass
{
    public function test_user_can_login()
    {
        $user = User::factory()->create();

        $response = $this->post('/api/v1/user/login', [
            'email' => $user->email,
            'password' => 'password'
        ], $this->requestHeaders);

        $response->assertJsonStructure([
            'status',
            'data' => [
                'token',
                'expires_at',
                'type'
            ],
            'error',
            'errors',
            'extra',
        ]);

        $data = $response->json();
        $this->assertDatabaseHas('jwt_tokens', [
            'unique_id' => $data['data']['token'],
            'user_id' => $user->id,
        ]);

        $response->assertJson([
            'status' => true,
            'error' => null,
            'errors' => [],
            'extra' => [],
        ]);
    }

    /**
     * @test
     *
     * @dataProvider userData
     */
    public function test_user_be_created($input)
    {
        $response = $this->post('/api/v1/user/create', $input, $this->requestHeaders);

        $response->assertStatus(200);

        $response->assertJsonStructure($this->successPayload(['data' => [
            'uuid',
            'email',
            'token',
            'first_name',
            'last_name',
            'address',
            'phone_number',
            'avatar',
            'is_marketing',
        ]], true));

        $responseData = $response->json()['data'];

        $this->assertDatabaseHas('users', [
            'uuid' => $responseData['uuid'],
            'first_name' => $responseData['first_name'],
            'last_name' =>  $responseData['last_name'],
            'address' =>  $responseData['address'],
            'phone_number' =>  $responseData['phone_number'],
            'avatar' =>  $responseData['avatar'],
            'is_marketing' =>  $responseData['is_marketing'] ? 1 : 0
        ]);

    }

    /**
     * @test
     *
     * @dataProvider userData
     */
    public function test_user_can_be_updated($input)
    {
        $user = $this->loginUser();


        $response = $this->put('/api/v1/user/edit', $input, $this->requestHeaders);

        $response->assertStatus(200);

        $response->assertJsonStructure($this->successPayload(['data' => [
            'uuid',
            'email',
            'first_name',
            'last_name',
            'address',
            'phone_number',
            'avatar',
            'is_marketing',
        ]], true));

        $responseData = $response->json()['data'];

        $this->assertDatabaseHas('users', [
            'uuid' => $responseData['uuid'],
            'first_name' => $responseData['first_name'],
            'last_name' =>  $responseData['last_name'],
            'address' =>  $responseData['address'],
            'phone_number' =>  $responseData['phone_number'],
            'avatar' =>  $responseData['avatar'],
            'is_marketing' =>  $responseData['is_marketing'] ? 1 : 0
        ]);
    }

    public function test_user_can_be_deleted()
    {
        $user = $this->loginUser();

        $response = $this->delete('/api/v1/user', [], $this->requestHeaders);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
        ]);
    }

    public function test_user_password_recovery_flow()
    {
        //password recovery flow includes forgot password token request, password change and login action

        $user = User::factory()->create();

        $tokenRequest = $this->post('api/v1/user/forgot-password', [
                'email' => $user->email
            ], $this->requestHeaders);

        $tokenRequest->assertStatus(200);

        $tokenRequest->assertJsonStructure($this->successPayload(['data' => [
            'reset_token',
        ]], true));

        $tokenRequest = $tokenRequest->json();

        $changePasswordRequest = $this->post('api/v1/user/reset-password-token', [
            'token' => $tokenRequest['data']['reset_token'],
            'email' => $user->email,
            'password' => 'password-change',
            'password_confirmation' => 'password-change'
        ], $this->requestHeaders);

        $changePasswordRequest->assertStatus(200);

        $changePasswordRequest->assertJson($this->successPayload([
            'data' => [
                'message' => "Password has been successfully updated"
            ]
        ]));

        $loginRequest = $this->post('api/v1/user/login', [
            'email' => $user->email,
            'password' => 'password-change',
        ], $this->requestHeaders);

        $loginRequest->assertStatus(200);
        $loginRequest->assertJsonStructure($this->successPayload([
            'data' => [
                'token',
                'expires_at',
                'type',
            ]
        ], true));
    }

    public function test_user_logout_and_fetch_user_data()
    {
        $user = $this->loginUser();

        // fetch user to test that login works
        $fetchUserRequest = $this->get('api/v1/user/', $this->requestHeaders);

        $fetchUserRequest->assertStatus(200);
        $fetchUserRequest->assertJson($this->successPayload([
            'data' => [
                'uuid' => $user->uuid,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'avatar' => $user->avatar,
                'is_marketing' => $user->is_marketing,
                'phone_number' => $user->phone_number,
                'address' => $user->address,
            ]
        ]));

        $logoutUserRequest = $this->get('api/v1/user/logout', $this->requestHeaders);
        $logoutUserRequest->assertStatus(200);
        $logoutUserRequest->assertJson($this->successPayload([]));

        $this->assertDatabaseMissing('jwt_tokens', [
            'unique_id' => $this->requestHeaders['Authorization'],
            'user_id' => $user->id
        ]);
    }


    public function userData(): array
    {
        return [
            [
                [
                    'email' => 'testUser1@email',
                    'first_name' => 'Test',
                    'last_name' => 'User1',
                    'address' => 'No1, Alter Street.',
                    'phone_number' => '+178884545',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ]
            ],

            [
                [
                    'email' => 'testUser2@email',
                    'first_name' => 'Test',
                    'last_name' => 'User2',
                    'address' => 'No1, Alter Street2.',
                    'phone_number' => '+17888454524',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                    'avatar' => "test_png.jpg",
                    'is_marketing' => "true",
                ]
            ]
        ];
    }
}
