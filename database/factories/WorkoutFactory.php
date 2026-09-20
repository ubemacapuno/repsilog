<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workout>
 */
class WorkoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'performed_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'title' => fake()->randomElement(['Push Day', 'Pull Day', 'Leg Day', 'Morning Run', null]),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
