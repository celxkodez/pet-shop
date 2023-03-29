<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends BaseFormRequest
{
    protected array $routeRequest = [
        'api/v1/order/create|post' => 'createMethodRule',
    ];

    /**
     * @OA\Schema(
     *  schema="CreateOrderProperties",
     *  @OA\Xml(name="CreateOrderProperties"),
     *  required={"order_status_uuid","products","payment_uuid","address"},
     *  @OA\Property(
     *     property="order_status_uuid",
     *     type="string",
     *     format="Order Status UUID", example=""
     * ),
     *  @OA\Property(property="payment_uuid", type="string", format="Payment UUID", example=""),
     *  @OA\Property(
     *     property="products",
     *     type="array",
     *     format="Array of objects with product uuid and quantity", example="[]",
     *     @OA\Items(
     *      type="object",
     *     ),
     *     @OA\Items(
     *      type="object",
     *     ),
     *  ),
     *  @OA\Property(
     *     property="address",
     *     type="object",
     *     format="Billing and Shipping address",
     *     example="{}",
     *  ),
     * )
     */
    public function createMethodRule(): void
    {
        $this->rules = [
            'order_status_uuid' => ['required', 'string'],
            'payment_uuid' => ['required', 'string'],
            'products' => ['required', 'array'],
            'products.*.uuid' => ['required', 'string', 'exists:products'],
            'products.*.quantity' => ['required', 'integer'],
            'address' => ['required',],
            'address.billing' => ['required', 'string'],
            'address.shipping' => ['required', 'string'],
        ];
    }

    public function getPath(): string
    {
        return $this->route()->uri();
    }
}
