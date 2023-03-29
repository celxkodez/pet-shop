<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->first()->id,
            'title' => fake()->title,
            'uuid' => Str::uuid()->toString(),
            'price' => fake()->randomDigitNot(0) * fake()->randomDigitNot(0),
            'description' => fake()->text,
            'metadata' => json_encode([]),
        ];
    }
}
