<?php

namespace App\Http\Controllers\V1;

use App\Contracts\RepositoryInterfaces\OrderRepositoryContract;
use App\Facades\JWTServiceFacade;
use App\Http\Requests\AdminRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Builder;


/**
 * @OA\Tag(
 *     name="Admin",
 *     description="Admin API Endpoints"
 * )
 */
class AdminController extends UserController
{
    protected OrderRepositoryContract $orderRepository;

    public function __construct(OrderRepositoryContract $repositoryContract)
    {
        $this->orderRepository = $repositoryContract;
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
    public function login(UserRequest $request, Guard $guard): \Illuminate\Http\JsonResponse
    {
        $user = User::whereEmail($request->email)->whereIsAdmin(true)->first();

        if (! $user) {
            return $this->errorResponse('User Not and Admin', 422);
        }
        return parent::login($request, $guard);
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
    public function createUser(AdminRequest $request): \Illuminate\Http\JsonResponse
    {
        $input = $request->validated();

        try {
            $password = \Hash::make($input['password']);
            $user = User::create(array_merge([
                'password' => $password,
                'is_admin' => true,
                'is_marketing' => !is_null($input['is_marketing']),
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
     *  summary="View All User Listings",
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
    public function userListing(AdminRequest $request)
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
                'phone' => $input['phone'] ?? '',
                'address' => $input['address'] ?? '',
                'created_at' => $input['created_at'] ?? '',
                'is_marketing' => $input['is_marketing'] ?? '',
            ];

            $orderQuery = $this->orderRepository->builder();

            $orderQuery->whereHas('user', function (Builder $builder) use ($searchFields) {
                foreach ($searchFields as $key => $field) {
                    $builder->where($key, 'like', "%$field%");
                }
            });

            $data = $orderQuery->orderBy($orderByField, $orderByClause)
                ->paginate($input['limit'] ?? 10);


            return new OrderResource($data);
        } catch (\Throwable $exception) {
            \Log::error($exception);

            return $this->errorResponse('Server Error!',500);
        }
    }
}
