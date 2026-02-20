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
        $faker = $this->faker ?? \Faker\Factory::create();

        return [
            'title' => $faker->sentence(6),
            'content' => $faker->paragraphs(4, true),
            'tags' => $faker->randomElements(
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
                $faker->numberBetween(2, 4),
            ),
            'author_id' => User::factory(),
            'rating' => $faker->randomFloat(1, 2, 5),
        ];
    }
}
