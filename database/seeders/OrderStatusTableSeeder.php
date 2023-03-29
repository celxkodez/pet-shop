<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OrderStatus::truncate();

        $statuses = ['open', 'pending payment', 'paid', 'shipped', 'cancelled'];

        foreach ($statuses as $status) {
            OrderStatus::factory()
                ->state([
                    'title' => $status
                ])
                ->create();
        }
    }
}
