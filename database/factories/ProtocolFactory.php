<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Protocol>
 */
class ProtocolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'content' => fake()->paragraphs(4, true),
            'tags' => fake()->randomElements(
                [
                    'clinical',
                    'survey',
                    'a-b-test',
                    'neuroscience',
                    'chemistry',
                    'biology',
                    'psychology',
                    'machine-learning',
                ],
                fake()->numberBetween(2, 4),
            ),
            'author_id' => User::factory(),
            'rating' => fake()->randomFloat(1, 2, 5),
        ];
    }
}
