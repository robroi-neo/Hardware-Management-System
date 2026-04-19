<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
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
        $units = ['piece', 'set', 'box', 'pack', 'dozen', 'meter', 'liter', 'kg'];

        return [
            'name' => fake()->word() . ' ' . fake()->randomElement(['Tool', 'Hardware', 'Equipment', 'Item', 'Part']),
            'capital' => fake()->numberBetween(50, 2000) + fake()->numberBetween(0, 99) / 100,
            'unit' => fake()->randomElement($units),
            'status' => 'active',
        ];
    }
}

