<?php

namespace Database\Factories;

use App\Enums\ExerciseType;
use App\Models\Exercise;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    /**
     * Names are generated rather than picked from a list, because a user cannot
     * hold the same exercise name twice.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => Str::headline(fake()->unique()->slug(2)),
            'type' => ExerciseType::Strength,
        ];
    }

    public function cardio(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ExerciseType::Cardio,
        ]);
    }
}
