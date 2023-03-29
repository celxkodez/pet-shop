<?php

namespace Tests\Feature;



use App\Models\User;
use Database\Seeders\UserTableSeeder;

class AdminEndpointRequestTest extends BaseTestClass
{

    public function test_user_can_login()
    {
        $this->artisan('db:seed', ['class' => UserTableSeeder::class]);


        $user = User::where('email', 'admin@buckhill.co.uk')->first();
        $response = $this->post('/api/v1/admin/login', [
            'email' => 'admin@buckhill.co.uk',
            'password' => 'admin'
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
     * @dataProvider adminData
     */
    public function test_user_be_created($input)
    {
        $response = $this->post('/api/v1/admin/create', $input, $this->requestHeaders);

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
     * @dataProvider adminData
     */
    public function test_user_can_be_updated($input)
    {
        $user = $this->loginUser('Admin');

        $userTobeUpdated = User::factory()->create();

        $response = $this->put('/api/v1/admin/user-edit/' . $userTobeUpdated->uuid, $input, $this->requestHeaders);

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
        $user = $this->loginUser('Admin');

        $userToDeDeleted = User::factory()->create();

        $response = $this->delete('/api/v1/admin/user-delete/' . $userToDeDeleted->uuid, [], $this->requestHeaders);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', [
            'id' => $userToDeDeleted->id,
            'uuid' => $userToDeDeleted->uuid,
            'first_name' => $userToDeDeleted->first_name,
            'last_name' => $userToDeDeleted->last_name,
        ]);
    }

    public function test_admin_logout_and_fetch_user_data()
    {
        $user = $this->loginUser('Admin');

        // fetch user to test that login works
        $fetchUserRequest = $this->get('api/v1/admin/user-listing', $this->requestHeaders);

        $fetchUserRequest->assertStatus(200);

        $logoutUserRequest = $this->get('api/v1/admin/logout', $this->requestHeaders);
        $logoutUserRequest->assertStatus(200);
        $logoutUserRequest->assertJson($this->successPayload([]));

        $this->assertDatabaseMissing('jwt_tokens', [
            'unique_id' => $this->requestHeaders['Authorization'],
            'user_id' => $user->id
        ]);
    }


    public function adminData(): array
    {
        return [
            [
                [
                    'email' => 'testadmin1@email',
                    'first_name' => 'Test',
                    'last_name' => 'Admin1',
                    'address' => 'No1, Alter Street.',
                    'phone_number' => '+178884545',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                    'avatar' => "test_png.jpg",
                ]
            ],

            [
                [
                    'email' => 'testadmin2@email',
                    'first_name' => 'Test',
                    'last_name' => 'Admin2',
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
