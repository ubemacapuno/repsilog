<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\WorkoutSession;
use App\Models\WorkoutSessionExercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkoutSessionExercise>
 */
class WorkoutSessionExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workout_session_id' => WorkoutSession::factory(),
            'exercise_id' => Exercise::factory(),
            'duration_seconds' => null,
            'distance_miles' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function cardio(): static
    {
        return $this->state(fn (array $attributes) => [
            'exercise_id' => Exercise::factory()->cardio(),
            'duration_seconds' => fake()->numberBetween(600, 36000),
            'distance_miles' => fake()->randomFloat(2, 1, 6),
        ]);
    }
}
