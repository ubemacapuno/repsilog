<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\ExerciseSet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExerciseSet>
 */
class ExerciseSetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exercise_id' => Exercise::factory(),
            'reps' => fake()->numberBetween(5, 12),
            'weight' => fake()->randomFloat(2, 45, 315),
        ];
    }

    public function bodyweight(): static
    {
        return $this->state(fn (array $attributes) => ['weight' => null]);
    }
}
