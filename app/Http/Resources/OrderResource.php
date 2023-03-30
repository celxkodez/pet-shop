<?php

namespace App\Http\Resources;

use App\Http\Resources\V1\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'product' => json_decode($this->products, true),
            'payment' => new PaymentResource($this->payment),
            'user' => new UserResource($this->user),
            'address' => json_decode($this->address, true),
            'delivery_fee' => $this->delivery_fee,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'shipped_at' => $this->shipped_at,
            'order_status' => new OrderStatusResource($this->orderStatus),
        ];
    }
}
