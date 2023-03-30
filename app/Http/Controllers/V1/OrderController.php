<?php

namespace App\Http\Controllers\V1;

use App\Contracts\RepositoryInterfaces\OrderRepositoryContract;
use App\Exports\OrderExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use Barryvdh\DomPDF\PDF;
use Maatwebsite\Excel\Facades\Excel;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="Order API Endpoints"
 * )
 */
class OrderController extends Controller
{
    protected OrderRepositoryContract $repository;

    public function __construct(OrderRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    /**
     *
     * @OA\Post(
     *  path="/api/v1/order/create",
     *  summary="Create A New Admin Account",
     *  tags={"Orders"},
     *  security={ {"bearerAuth": {} }},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Supply Order Data",
     *    @OA\JsonContent(
     *      ref="#/components/schemas/CreateOrderProperties",
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
    public function store(OrderRequest $request): \Illuminate\Http\JsonResponse
    {
        $input = $request->validated();
        try {
            $message = '';

            $orderStatus = OrderStatus::where('uuid', $input['order_status_uuid'])->first();
            $payment = Payment::where('uuid', $input['payment_uuid'])->first();

            if (! $orderStatus) {
                $message = "Invalid Order Status";
            }

            if (! $payment) {
                $message = "Invalid Payment";
            }

            if ($message !== '') {
               $this->errorResponse($message, 422) ;
            }
            $data = Order::create([
                'order_status_id' => $orderStatus->id,
                'payment_id' => $payment->id,
                'products' => json_encode($input['products']),
                'address' => json_encode($input['address']),
                'user_id' => auth('api')->user()->id
            ]);

            return $this->jsonResponse(200, true, new OrderResource($data));

        } catch (\Throwable $exception) {
            \Log::error($exception);

            return $this->errorResponse('Server Error!',500);
        }
    }

    /**
     *
     * @OA\Get(
     *  path="/api/v1/order/{uuid}",
     *  summary="Show Order",
     *  tags={"Orders"},
     *  security={ {"bearerAuth": {} }},
     *  @OA\Parameter(
     *    description="UUID of Order",
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
    public function show($uuid): \Illuminate\Http\JsonResponse
    {
        return $this->withErrorHandling(function () use ($uuid) {
            $order = Order::where('uuid', $uuid)->first();

            if ($order) {
                return $this->jsonResponse(200, true, new OrderResource($order));
            }

            return $this->errorResponse('Order Not Found!', 404);
        });
    }

    /**
     * @OA\Get(
     *  path="/api/v1/order",
     *  summary="List All Orders",
     *  tags={"Orders"},
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
    public function index(OrderRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $input = $request->validated();

        return $this->withErrorHandling(function () use ($input) {
            $orderQuery = $this->repository->builder();

            $fields = [
                'created_at' => 'created_at',
                'shipped_at' => 'shipped_at',
                'amount' => 'amount',
            ];

            $orderByClause = isset($input['desc']) && $input['desc'] == 'true' ? 'desc' : 'asc';

            $orderByField = isset($input['sortBy']) && isset($fields[$input['sortBy']]) ?
                $fields[$input['sortBy']] : $fields['created_at'];

            $data = $orderQuery->orderBy($orderByField, $orderByClause)
                ->paginate($input['limit'] ?? 10);

            return OrderResource::collection($data);
        });
    }

    /**
     *
     * @OA\Put(
     *  path="/api/v1/order/{uuid}",
     *  summary="Update Order",
     *  tags={"Orders"},
     *  security={ {"bearerAuth": {} }},
     *  @OA\Parameter(
     *    description="UUID of Order",
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
     *    description="Supply Order Data",
     *    @OA\JsonContent(
     *      ref="#/components/schemas/CreateOrderProperties",
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
    public function update(OrderRequest $request, $uuid): \Illuminate\Http\JsonResponse
    {
        $input = $request->validated();
        return $this->withErrorHandling(function () use ($input, $uuid) {
            $order = Order::where('uuid', $uuid)->first();

            if (!$order) {
                return $this->errorResponse('Order Not Found!', 404);
            }

            $input['products'] = json_encode($input['products']);
            $input['address'] = json_encode($input['address']);
            $order->update($input);

            return $this->jsonResponse(200, true, new OrderResource($order));

        });
    }

    /**
     *
     * @OA\Delete(
     *  path="/api/v1/order/{uuid}",
     *  summary="Delete an Order",
     *  tags={"Orders"},
     *  security={ {"bearerAuth": {} }},
     *  @OA\Parameter(
     *    description="UUID of Order",
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
    public function destroy($uuid)
    {
        return $this->withErrorHandling(function () use ($uuid) {
            $order = Order::where('uuid', $uuid)->delete();

            if ($order) {
                return $this->jsonResponse(200, true, []);
            }

            return $this->errorResponse('Order Not Found!', 404);
        });
    }

    /**
     *
     * @OA\Get(
     *  path="/api/v1/order/{uuid}/download",
     *  summary="Downlad an Order",
     *  tags={"Orders"},
     *  security={ {"bearerAuth": {} }},
     *  @OA\Parameter(
     *    description="UUID of Order",
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
    public function download($uuid)
    {
        return $this->withErrorHandling(function () use ($uuid) {
            $order = Order::where('uuid', $uuid)
                ->with('user')
                ->first();

            if ($order) {

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.order', ['order' => $order]);
                return $pdf->download($order->uuid . '.pdf');
            }

            return $this->errorResponse('Order Not Found!', 404);
        });
    }
}
