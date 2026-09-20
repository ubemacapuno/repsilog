<?php

namespace Database\Factories;

use App\Enums\ExerciseType;
use App\Models\Exercise;
use App\Models\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
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
            'name' => fake()->randomElement(['Bench Press', 'Squat', 'Deadlift', 'Overhead Press', 'Row']),
            'type' => ExerciseType::Strength,
            'duration_seconds' => null,
            'distance_miles' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function cardio(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement(['Run', 'Walk', 'Row', 'Cycle']),
            'type' => ExerciseType::Cardio,
            'duration_seconds' => fake()->numberBetween(600, 36000),
            'distance_miles' => fake()->randomFloat(2, 1, 6),
        ]);
    }
}
