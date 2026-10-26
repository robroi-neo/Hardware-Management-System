<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),

            'phone' => fake()->unique()->numerify('09#########'),

            'address' => fake()->address(),

            'pin' => Hash::make('1234'),

            'status' => fake()->randomElement([
                'active',
                'inactive'
            ]),

            'branch_id' => Branch::query()->inRandomOrder()->value('id'),

            'created_by' => 1,
        ];
    }
}
