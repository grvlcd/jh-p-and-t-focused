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
            'title' => $this->faker->sentence(6),
            'content' => $this->faker->paragraphs(4, true),
            'tags' => $this->faker->randomElements(
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
                $this->faker->numberBetween(2, 4),
            ),
            'author_id' => User::factory(),
            'rating' => $this->faker->randomFloat(1, 2, 5),
        ];
    }
}
