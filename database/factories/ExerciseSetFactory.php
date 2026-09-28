<?php

namespace Database\Factories;

use App\Models\ExerciseSet;
use App\Models\WorkoutExercise;
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
            'workout_exercise_id' => WorkoutExercise::factory(),
            'reps' => fake()->numberBetween(5, 12),
            'weight' => fake()->randomFloat(2, 45, 315),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => ['completed_at' => now()]);
    }

    public function bodyweight(): static
    {
        return $this->state(fn (array $attributes) => ['weight' => null]);
    }
}
