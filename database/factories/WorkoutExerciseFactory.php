<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkoutExercise>
 */
class WorkoutExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workout_id' => Workout::factory(),
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
