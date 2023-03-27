<?php

namespace App\Http\Controllers\V1;

use App\Facades\JWTServiceFacade;
use App\Http\Controllers\Controller;
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
     * Login User With Provider Credentials.
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
     * Return Authenticated user Instance.
     */
    public function user(): \Illuminate\Http\JsonResponse
    {

        return $this->jsonResponse(200, true, new UserResource(auth('api')->user()));
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
    public function store(UserRequest $request): \Illuminate\Http\JsonResponse
    {
        $input = $request->validated();

        try {
            $password = \Hash::make($input['password']);
            $user = User::create(array_merge([
                'password' => $password,
                'is_marketing' => !is_null($input['is_marketing']),
            ], \Arr::except($input, ['password', 'is_marketing'])));

            $responseData = array_merge((new UserResource($user))->toArray($request), [
                    'token' => (JWTServiceFacade::requestToken($user))->unique_id
            ]);

            return $this->jsonResponse(200, true, $responseData);
        } catch (\Throwable $exception) {
            \Log::error($exception);

            return $this->errorResponse('Server Error!',500);
        }
    }

    /**
     * Return Auth User Orders.
     */
    public function userOrders(UserRequest $request): \Illuminate\Http\JsonResponse|OrderResource
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

            return new OrderResource($data);
        } catch (\Throwable $exception) {
            \Log::error($exception);

            return $this->errorResponse('Server Error!',500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request): \Illuminate\Http\JsonResponse
    {
        $input = $request->validated();

        try {
            $user = auth('api')->user();

            $password = \Hash::make($input['password']);

            $user->update(array_merge([
                'password' => $password,
                'is_marketing' => !is_null($input['is_marketing']),
            ], \Arr::except($input, ['password', 'is_marketing'])));

            $responseData = new UserResource($user);

            return $this->jsonResponse(200, true, $responseData);
        } catch (\Throwable $exception) {
            \Log::error($exception);

            return $this->errorResponse('Server Error!',500);
        }
    }

    /**
     * Remove the specified resource from storage.
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
