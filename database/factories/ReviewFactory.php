<?php

namespace Database\Factories;

use App\Models\Protocol;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'protocol_id' => Protocol::factory(),
            'user_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'feedback' => fake()->boolean(70) ? fake()->sentences(2, true) : null,
        ];
    }
}
