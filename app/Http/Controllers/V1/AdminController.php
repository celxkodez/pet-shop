<?php

namespace App\Http\Controllers\V1;

use App\Contracts\RepositoryInterfaces\OrderRepositoryContract;
use App\Contracts\RepositoryInterfaces\UserRepositoryContract;
use App\Facades\JWTServiceFacade;
use App\Http\Requests\AdminRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Builder;
use phpDocumentor\Reflection\Types\Parent_;


/**
 * @OA\Tag(
 *     name="Admin",
 *     description="Admin API Endpoints"
 * )
 */
class AdminController extends UserController
{
    protected UserRepositoryContract $userRepository;

    public function __construct(UserRepositoryContract $repositoryContract)
    {
        $this->userRepository = $repositoryContract;
    }

    /**
     *
     * @OA\Post(
     *  path="/api/v1/admin/login",
     *  summary="Login Into Admin Account",
     *  tags={"Admin"},
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
    public function loginAdmin(AdminRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = User::whereEmail($request->email)->whereIsAdmin(true)->first();

        if (! $user) {
            return $this->errorResponse('User Not and Admin', 422);
        }

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
     *
     * @OA\Post(
     *  path="/api/v1/admin/create",
     *  summary="Create A New Admin Account",
     *  tags={"Admin"},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Supply Admin Data",
     *    @OA\JsonContent(
     *      required={"email","password","last_name","first_name","address","phone_number","avatar"},
     *      ref="#/components/schemas/CreateUserProperties",
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
    public function createAdmin(AdminRequest $request): \Illuminate\Http\JsonResponse
    {
        $input = $request->validated();

        try {
            $password = \Hash::make($input['password']);
            $user = User::create(array_merge([
                'password' => $password,
                'is_admin' => true,
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
     *
     * @OA\Get(
     *  path="/api/v1/admin/user-listing",
     *  summary="View All Users",
     *  tags={"Admin"},
     *  security={ {"bearerAuth": {} }},
     *  @OA\RequestBody(
     *    required=false,
     *    description="Filter Data",
     *    @OA\JsonContent(
     *     ref="#/components/schemas/AdminListingProperties"
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
    public function userListing(AdminRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $input = $request->validated();
        try {
            $fields = [
                'created_at' => 'created_at',
                'shipped_at' => 'shipped_at',
                'amount' => 'amount',
            ];

            $orderByClause = isset($input['desc']) && $input['desc'] == 'true' ? 'desc' : 'asc';

            $orderByField = isset($input['sortBy']) && isset($fields[$input['sortBy']]) ?
                $fields[$input['sortBy']] : $fields['created_at'];

            $searchFields = [
                'first_name' => $input['first_name'] ?? '',
                'email' => $input['email'] ?? '',
                'phone_number' => $input['phone'] ?? '',
                'address' => $input['address'] ?? '',
                'created_at' => $input['created_at'] ?? '',
                'is_marketing' => $input['is_marketing'] ?? '',
                'is_admin' => 0
            ];

            $userQuery = $this->userRepository->builder();

            foreach ($searchFields as $key => $field) {
                $userQuery->where($key, 'like', "%$field%");
            }

            $data = $userQuery->orderBy($orderByField, $orderByClause)
                ->paginate($input['limit'] ?? 10);


            return UserResource::collection($data);
        } catch (\Throwable $exception) {
            \Log::error($exception);

            return $this->errorResponse('Server Error!',500);
        }
    }

    /**
     *
     * @OA\Put(
     *  path="/api/v1/admin/user-edit/{uuid}",
     *  summary="Update User Account",
     *  tags={"Admin"},
     *  security={ {"bearerAuth": {} }},
     *  @OA\Parameter(
     *    description="UUID of User",
     *    in="path",
     *    name="uuid",
     *    required=true,
     *    example="uuiuytyytytj5656jk-jnnknnkbkjnk-nghn6n565",
     *    @OA\Schema(
     *       type="string",
     *       format="string"
     *    )
     *  ),
     *  @OA\RequestBody(
     *    required=true,
     *    description="Supply Admin Data",
     *    @OA\JsonContent(
     *      required={"email","password","last_name","first_name","address","phone_number","avatar"},
     *      ref="#/components/schemas/CreateUserProperties",
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
    public function userEdit(AdminRequest $request, $uuid): \Illuminate\Http\JsonResponse
    {
        $input = $request->validated();
        try {
            $user = User::where('uuid', $uuid)->first();
            if ($user?->is_admin) {
                return $this->errorResponse('Admin User Cannot Be editted',422);
            }

            if ($user) {
                $password = \Hash::make($input['password']);

                $user->update(array_merge([
                    'password' => $password,
                    'is_marketing' => isset($input['is_marketing']),
                ], \Arr::except($input, ['password', 'is_marketing'])));

                $responseData = new UserResource($user);

                return $this->jsonResponse(200, true, $responseData);
            }

            return $this->errorResponse('User not found',404);

        } catch (\Throwable $exception) {
            \Log::error($exception);

            return $this->errorResponse('Server Error!',500);
        }
    }

    /**
     *
     * @OA\Delete(
     *  path="/api/v1/admin/user-delete/{uuid}",
     *  summary="Delete A User Account",
     *  tags={"User"},
     *  security={ {"bearerAuth": {} }},
     *  @OA\Parameter(
     *    description="UUID of User",
     *    in="path",
     *    name="uuid",
     *    required=true,
     *    example="uuiuytyytytj5656jk-jnnknnkbkjnk-nghn6n565",
     *    @OA\Schema(
     *       type="string",
     *       format="string"
     *    )
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
    public function deleteUser($uuid): \Illuminate\Http\JsonResponse
    {
        try {
            $user = User::where('uuid', $uuid)->first();

            if ($user?->is_admin) {
                return $this->errorResponse('Admin User Cannot Be Deleted!',422);
            }

            if ($user) {
                $user->delete();

                return $this->jsonResponse(200, true, []);
            }

            return $this->errorResponse('User not found',404);
        } catch (\Throwable $exception) {
            \Log::error($exception);

            return $this->errorResponse('Server Error!',500);
        }
    }
}
