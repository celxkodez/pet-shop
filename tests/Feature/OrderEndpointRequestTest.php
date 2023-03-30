<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Product;

class OrderEndpointRequestTest extends BaseTestClass
{
    /**
     * @test
     *
     * @dataProvider dataProvider
     */
    public function test_order_can_be_created($input)
    {
        $this->artisan('db:seed');

        $authUser = $this->loginUser();
        $payment = Payment::factory()->create();
        $input = array_merge($input, [
            'payment_uuid' => $payment->uuid,
            'order_status_uuid' => (OrderStatus::inRandomOrder()->first())->uuid,
            'products' => [
                [
                    'uuid' => (Product::inRandomOrder()->first())->uuid,
                    'quantity' => 40
                ],
                [
                    'uuid' => (Product::inRandomOrder()->first())->uuid,
                    'quantity' => 30
                ]
            ]
        ]);
        $response = $this->post('api/v1/order/create', $input, $this->requestHeaders);

        $response->assertStatus(200);

        $response->assertJsonStructure($this->successPayload([
            'data' => []
        ], true));
    }

    public function test_order_show()
    {
        $authUser = $this->loginUser('Admin');

        $this->artisan('db:seed');
        $order = Order::first();
        $response = $this->get('api/v1/order/' . $order->uuid, $this->requestHeaders);

        $response->assertStatus(200);

        $response->assertJsonStructure($this->successPayload([
            'data' => [
                'uuid',
                'product',
                'payment',
                'user',
                'address',
                'delivery_fee',
                'created_at',
                'updated_at',
                'shipped_at',
                'order_status',
            ]
        ], true));

        $response->assertJson($this->successPayload([
            'data' => [
                'uuid' => $order->uuid,
            ]
        ]));
    }

    public function test_order_update()
    {
        $authUser = $this->loginUser('Admin');

        $this->artisan('db:seed');
        $order = Order::first();
        $payment = Payment::factory()->create();
        $input = [
            'order_status_uuid' => (OrderStatus::inRandomOrder()->first())->uuid,
            'payment_uuid' => $payment->uuid,
            'products' => [
                [
                    'uuid' => (Product::inRandomOrder()->first())->uuid,
                    'quantity' => 40
                ],
                [
                    'uuid' => (Product::inRandomOrder()->first())->uuid,
                    'quantity' => 30
                ]
            ],
            'address' => [
                'billing' => "some random address changed",
                'shipping' => "some random address changed"
            ],
        ];
        $response = $this->put('api/v1/order/' . $order->uuid, $input, $this->requestHeaders);

        $response->assertStatus(200);

        $response->assertJsonStructure($this->successPayload([
            'data' => [
                'uuid',
                'product',
                'payment',
                'user',
                'address',
                'delivery_fee',
                'created_at',
                'updated_at',
                'shipped_at',
                'order_status',
            ]
        ], true));

        $response->assertJson($this->successPayload([
            'data' => [
                'uuid' => $order->uuid
            ]
        ]));
    }

    public function test_order_can_be_deleted()
    {
        $authUser = $this->loginUser('Admin');

        $this->artisan('db:seed');
        $order = Order::first();

        $response = $this->delete('api/v1/order/' . $order->uuid, [], $this->requestHeaders);

        $response->assertStatus(200);

        $response->assertJson($this->successPayload([]));

        $this->assertDatabaseMissing('orders', [
            'uuid' => $order->uuid
        ]);
    }

    public function test_order_can_be_uploaded()
    {
        $authUser = $this->loginUser('Admin');

        $this->artisan('db:seed');
        $order = Order::first();

        $response = $this->get('api/v1/order/' . $order->uuid,  $this->requestHeaders);

        $response->assertDownload($order->uuid . ".pdf");
    }

    public function dataProvider(): array
    {
        return [
            [
                [
                    'address' => [
                        'billing' => "some random address",
                        'shipping' => "some random address"
                    ],
                ]
            ],

            [
                [
                    'address' => [
                        'billing' => "some random address3",
                        'shipping' => "some random address3"
                    ],
                ]
            ],
        ];
    }
}
