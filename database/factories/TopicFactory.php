<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Topic>
 */
class TopicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'topic_name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(10),
            'due_date' => $this->faker->date('Y-m-d', '+1 year'),
        ];
    }
}
