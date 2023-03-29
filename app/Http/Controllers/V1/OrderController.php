<?php

namespace App\Http\Controllers\V1;

use App\Contracts\RepositoryInterfaces\OrderRepositoryContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Order",
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
     *  tags={"Order"},
     *  security={ {"bearerAuth": {} }},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Supply Admin Data",
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
    public function store(OrderRequest $request)
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
}
