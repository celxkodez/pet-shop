<?php

namespace App\Http\Controllers\V1;

use App\Facades\JWTServiceFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\JWTService;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="User",
 *     description="User API Endpoints"
 * )
 */
class UserController extends Controller
{

    /**
     * Login User With Provider Credentials.
     */
    public function login(UserRequest $request, Guard $guard)
    {
        $auth = auth('api');
        if (! $auth->validate($request->validated())) {

            return response()->json([
                'status' => false,
                'data' => [],
                'error' => "Failed to authenticate user",
                'errors' => [],
                'extra' => [],
            ], 422);

        }

        $token = JWTServiceFacade::requestToken($auth->user());

        return response()->json([
            'status' => true,
            'data' => [
                'token' => $token->unique_id,
                'expires_at' => $token->expires_at,
                'type' => $token->token_title,
            ],
            'error' => null,
            'errors' => [],
            'extra' => [],
        ], 200);
    }

    /**
     * Return Authenticated user Instance.
     */
    public function user()
    {
//        dd(\request()->header('authorization'));
//        dd(auth('api')->check());
        return response()->json([
            'status' => true,
            'data' => auth('api')->user(),
            'error' => null,
            'errors' => [],
            'extra' => [],
        ], 200);
    }

    /**
     *
     * @OA\Put(
     *     path="api/v1/user/create",
     *     summary="Create a User account",
     *     tags={"User"},
     *     @OA\RequestBody (
     *          required=true,
     *          name="first_name",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
     * )
     *
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $input = $request->validated();

        try {
            $password = \Hash::make($input['password']);
            $user = User::create(array_merge([
                'password' => $password,
                'is_marketing' => !is_null($input['is_marketing']),
            ], \Arr::except($input, ['password', 'is_marketing'])));

            $responseData = array_merge($user->toArray(), [
                    'token' => (JWTServiceFacade::requestToken($user))->unique_id
            ]);

            return response()->json([
                'status' => true,
                'data' => $responseData,
                'error' => null,
                'errors' => [],
                'extra' => [],
            ], 200);

        } catch (\Throwable $exception) {
            \Log::error($exception);

            return response()->json([], 500);
        }
    }

    /**
     * Return Auth User Orders.
     */
    public function orders(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
