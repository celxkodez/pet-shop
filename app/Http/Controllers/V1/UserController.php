<?php

namespace App\Http\Controllers\V1;

use App\Facades\JWTServiceFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\V1\UserResource;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="User",
 *     description="User API Endpoints"
 * )
 */
class UserController extends Controller
{

    /**
     *
     * @OA\Post(
     *  path="/api/v1/user/login",
     *  summary="Login A User Account",
     *  tags={"User"},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email", example="user@email.com"),
     *       @OA\Property(property="password", type="string", format="password", example="password"),
     *    ),
     *  ),
     *  @OA\Response(
     *    response=200,
     *    description="Ok"
     *  ),
     *  @OA\Response(
     *    response=401,
     *    description="Unauthorized"
     *  ),
     *  @OA\Response(
     *    response=404,
     *    description="Page not found"
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="Unprocessable Entity"
     *  ),
     *  @OA\Response(
     *    response=500,
     *    description="Internal server error"
     *  )
     * )
     */
    public function login(UserRequest $request, Guard $guard): \Illuminate\Http\JsonResponse
    {
        $auth = auth('api');
        if (! $auth->validate($request->validated())) {

            return $this->errorResponse("Failed to authenticate user", 422);
        }

        $token = JWTServiceFacade::requestToken($auth->user());

        $data = [
            'token' => $token->unique_id,
            'expires_at' => $token->expires_at,
            'type' => $token->token_title,
        ];

        return $this->jsonResponse(200, true, $data);
    }

    /**
     * @OA\Get(
     *  path="/api/v1/user",
     *  summary="View A User Account",
     *  tags={"User"},
     *  security={ {"bearerAuth": {} }},
     *  @OA\Response(
     *    response=200,
     *    description="Ok"
     *  ),
     *  @OA\Response(
     *    response=401,
     *    description="Unauthorized"
     *  ),
     *  @OA\Response(
     *    response=404,
     *    description="Page not found"
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="Unprocessable Entity"
     *  ),
     *  @OA\Response(
     *    response=500,
     *    description="Internal server error"
     *  )
     * )
     */
    public function user(): \Illuminate\Http\JsonResponse
    {

        return $this->jsonResponse(200, true, new UserResource(auth('api')->user()));
    }

    /**
     *
     * @OA\Post(
     *  path="/api/v1/user/create",
     *  summary="Create A New User Account",
     *  tags={"User"},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Supply User Data",
     *    @OA\JsonContent(
     *     ref="#/components/schemas/RegisterUserProperties",
     *    ),
     *  ),
     *  @OA\Response(
     *    response=200,
     *    description="Ok"
     *  ),
     *  @OA\Response(
     *    response=401,
     *    description="Unauthorized"
     *  ),
     *  @OA\Response(
     *    response=404,
     *    description="Page not found"
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="Unprocessable Entity"
     *  ),
     *  @OA\Response(
     *    response=500,
     *    description="Internal server error"
     *  )
     * )
     */
    public function store(UserRequest $request): \Illuminate\Http\JsonResponse
    {
        $input = $request->validated();

        try {
            $password = \Hash::make($input['password']);
            $user = User::create(array_merge([
                'password' => $password,
                'is_marketing' => isset($input['is_marketing']),
            ], \Arr::except($input, ['password', 'is_marketing'])));

            $responseData = array_merge((new UserResource($user))->toArray($request), [
                    'token' => JWTServiceFacade::requestToken($user)->unique_id
            ]);

            return $this->jsonResponse(200, true, $responseData);
        } catch (\Throwable $exception) {
            \Log::error($exception);

            return $this->errorResponse('Server Error!',500);
        }
    }

    /**
     * @OA\Get(
     *  path="/api/v1/user/orders",
     *  summary="List All User Orders",
     *  tags={"User"},
     *  security={ {"bearerAuth": {} }},
     *  @OA\Parameter(
     *     name="page",
     *     description="page Number",
     *     in="query",
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="limit",
     *     description="Item Per page",
     *     in="query",
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="sortBy",
     *     description="Sort By",
     *     in="query",
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="desc",
     *     description="Direction of sort",
     *     in="query",
     *     @OA\Schema(
     *       type="boolean"
     *     )
     *   ),
     *  @OA\Response(
     *    response=200,
     *    description="Ok"
     *  ),
     *  @OA\Response(
     *    response=401,
     *    description="Unauthorized"
     *  ),
     *  @OA\Response(
     *    response=404,
     *    description="Page not found"
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="Unprocessable Entity"
     *  ),
     *  @OA\Response(
     *    response=500,
     *    description="Internal server error"
     *  )
     * )
     */
    public function userOrders(UserRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $input = $request->validated();

        try {
            $authUser = auth('api')->user();
            $fields = [
                'created_at' => 'created_at',
                'shipped_at' => 'shipped_at',
                'amount' => 'amount',
            ];

            $orderByClause = isset($input['desc']) && $input['desc'] == 'true' ? 'desc' : 'asc';

            $orderByField = isset($input['sortBy']) && isset($fields[$input['sortBy']]) ?
                $fields[$input['sortBy']] : $fields['created_at'];

            $data = $authUser->orders()
                ->orderBy($orderByField, $orderByClause)
                ->paginate($input['limit'] ?? 10);

            return OrderResource::collection($data);
        } catch (\Throwable $exception) {
            \Log::error($exception);

            return $this->errorResponse('Server Error!',500);
        }
    }

    /**
     * @OA\Put(
     *  path="/api/v1/user/edit",
     *  summary="Update A User Account",
     *  tags={"User"},
     *  security={ {"bearerAuth": {} }},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Update User Record",
     *    @OA\JsonContent(
     *       ref="#/components/schemas/RegisterUserProperties",
     *    ),
     *  ),
     *  @OA\Response(
     *    response=200,
     *    description="Ok"
     *  ),
     *  @OA\Response(
     *    response=401,
     *    description="Unauthorized"
     *  ),
     *  @OA\Response(
     *    response=404,
     *    description="Page not found"
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="Unprocessable Entity"
     *  ),
     *  @OA\Response(
     *    response=500,
     *    description="Internal server error"
     *  )
     * )
     */
    public function update(UserRequest $request): \Illuminate\Http\JsonResponse
    {
        $input = $request->validated();

        try {
            $user = auth('api')->user();

            $password = \Hash::make($input['password']);

            $user->update(array_merge([
                'password' => $password,
                'is_marketing' => isset($input['is_marketing']),
            ], \Arr::except($input, ['password', 'is_marketing'])));

            $responseData = new UserResource($user);

            return $this->jsonResponse(200, true, $responseData);
        } catch (\Throwable $exception) {
            \Log::error($exception);

            return $this->errorResponse('Server Error!',500);
        }
    }

    /**
     *
     * @OA\Delete(
     *  path="/api/v1/user",
     *  summary="Delete A User Account",
     *  tags={"User"},
     *  security={ {"bearerAuth": {} }},
     *  @OA\Response(
     *    response=200,
     *    description="Ok"
     *  ),
     *  @OA\Response(
     *    response=401,
     *    description="Unauthorized"
     *  ),
     *  @OA\Response(
     *    response=404,
     *    description="Page not found"
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="Unprocessable Entity"
     *  ),
     *  @OA\Response(
     *    response=500,
     *    description="Internal server error"
     *  )
     * )
     */
    public function destroy(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth('api')->user();

            $user->delete();

            return $this->jsonResponse(200, true, []);
        } catch (\Throwable $exception) {
            \Log::error($exception);

            return $this->errorResponse('Server Error!',500);
        }
    }

    /**
     *
     * @OA\Post(
     *  path="/api/v1/user/forgot-password",
     *  summary="Request Forgot Password Token",
     *  tags={"User"},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Pass user email",
     *    @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string", format="email", example="user@email.com"),
     *    ),
     *  ),
     *  @OA\Response(
     *    response=200,
     *    description="Ok"
     *  ),
     *  @OA\Response(
     *    response=401,
     *    description="Unauthorized"
     *  ),
     *  @OA\Response(
     *    response=404,
     *    description="Page not found"
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="Unprocessable Entity"
     *  ),
     *  @OA\Response(
     *    response=500,
     *    description="Internal server error"
     *  )
     * )
     */
    public function forgotPassword(UserRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $input = $request->validated();
            if ($user = User::where('email', $input['email'])->first()) {
                $token = Str::random(128);

                $tokenModel = PasswordResetToken::firstOrCreate(['email' => $user->email], [
                    'token' => $token,
                ]);

                return $this->jsonResponse(200, true, ['reset_token' => $tokenModel->token]);
            }

            return $this->errorResponse('Invalid email', 404);
        } catch (\Throwable $exception) {
            \Log::error($exception);
            return $this->errorResponse('Server Error!', 500);
        }
    }

    /**
     *
     * @OA\Post(
     *  path="/api/v1/user/reset-password-token",
     *  summary="Recover User Account",
     *  tags={"User"},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Supply Credentials",
     *    @OA\JsonContent(
     *       required={"email","password", "password_confirmation", "token"},
     *       @OA\Property(property="email", type="string", format="email", example="user@email.com"),
     *       @OA\Property(property="token", type="string", format="token", example="gfgfg885478hgf"),
     *       @OA\Property(property="password", type="string", format="password", example="password"),
     *       @OA\Property(property="password_confirmation", type="string", format="password", example="password"),
     *    ),
     *  ),
     *  @OA\Response(
     *    response=200,
     *    description="Ok"
     *  ),
     *  @OA\Response(
     *    response=401,
     *    description="Unauthorized"
     *  ),
     *  @OA\Response(
     *    response=404,
     *    description="Page not found"
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="Unprocessable Entity"
     *  ),
     *  @OA\Response(
     *    response=500,
     *    description="Internal server error"
     *  )
     * )
     */
    public function resetPasswordToken(UserRequest $request): \Illuminate\Http\JsonResponse
    {
        $input = $request->validated();

        try {
            $token = PasswordResetToken::where('email', $input['email'])
                ->where('token', $input['token'])
                ->first();

            if (!$token) {
                return $this->errorResponse("Invalid or expired token",422);
            }

            User::whereEmail($input['email'])->update(['password' => \Hash::make($input['password'])]);

            $token->delete();

            return $this->jsonResponse(200, true, ['message' => "Password has been successfully updated"]);
        } catch (\Throwable $exception) {

            \Log::error($exception);
            return $this->errorResponse('Server Error!', 500);
        }
    }

    /**
     *
     * @OA\Get(
     *  path="/api/v1/user/logout",
     *  summary="Logout A User Account",
     *  tags={"User"},
     *  security={ {"bearerAuth": {} }},
     *  @OA\Response(
     *    response=200,
     *    description="Ok"
     *  ),
     *  @OA\Response(
     *    response=401,
     *    description="Unauthorized"
     *  ),
     *  @OA\Response(
     *    response=404,
     *    description="Page not found"
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="Unprocessable Entity"
     *  ),
     *  @OA\Response(
     *    response=500,
     *    description="Internal server error"
     *  )
     * )
     */
    public function logout(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $token = $request->header('Authorization');

            if (is_string($token)) {
                [$tokenTitle, $token] = explode(' ', $token);

                if ($token) {
                    JWTServiceFacade::revokeToken($token);
                }
            }

            return $this->jsonResponse(200, true, []);
        } catch (\Throwable $exception) {
            \Log::error($exception);

            return $this->errorResponse('Server Error!',500);
        }
    }
}
