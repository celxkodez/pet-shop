<?php

namespace Tests\Feature;

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
