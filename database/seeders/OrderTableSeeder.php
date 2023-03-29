<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::truncate();

        Order::factory(10)
            ->for(User::factory()->create())
            ->for(OrderStatus::inRandomOrder()->first() ?? OrderStatus::factory()->create())
            ->for(Payment::inRandomOrder()->first() ?? Payment::factory()->create())
            ->state([
                'products' => json_encode([
                    [
                        'uuid' => Product::inRandomOrder()->first()->uuid ?? (Product::factory()
                                ->for(Category::inRandomOrder()->first() ?? Category::factory()->create())
                                ->create())->uuid,
                        'quantity' => fake()->randomDigit() * fake()->randomDigit()
                    ],
                    [
                        'uuid' => (Product::factory()
                                ->for(Category::inRandomOrder()->first() ?? Category::factory()->create())
                                ->create())->uuid,
                        'quantity' => fake()->randomDigit() * fake()->randomDigit()
                    ]
                ])
            ])
            ->create();
    }
}
