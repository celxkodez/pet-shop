<?php

namespace Database\Factories;

use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'order_status_id' => OrderStatus::inRandomOrder()->first()->id,
            'payment_id' => Payment::inRandomOrder()->first()->id,
            'uuid' => Str::uuid()->toString(),
            'amount' => fake()->randomDigitNot(0) * 100,
            'address' => json_encode([
                'billing' => fake()->address,
                'shipping' => fake()->address
            ]),
            'products' => json_encode([
                [
                    'uuid' => Product::inRandomOrder()->first()->uuid,
                    'quantity' => fake()->randomDigit() * fake()->randomDigit()
                ],
            ]),
            'shipped_at' => now()
        ];
    }
}
