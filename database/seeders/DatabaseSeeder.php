<?php

namespace Database\Seeders;

use App\Enums\ExerciseType;
use App\Models\Exercise;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->seedPushDay($user);
        $this->seedLegDay($user);
        $this->seedMorningRun($user);
        $this->seedRecentHistory($user);
    }

    private function seedPushDay(User $user): void
    {
        $workout = $user->workouts()->create([
            'title' => 'Push Day',
            'performed_at' => now()->subDays(2)->setTime(18, 30),
            'notes' => 'Felt strong. Bench moved well.',
        ]);

        $this->addStrengthExercises($workout, [
            'Bench Press' => [
                ['reps' => 12, 'weight' => 135],
                ['reps' => 10, 'weight' => 155],
                ['reps' => 8, 'weight' => 175],
                ['reps' => 6, 'weight' => 185],
            ],
            'Overhead Press' => [
                ['reps' => 10, 'weight' => 75],
                ['reps' => 8, 'weight' => 85],
                ['reps' => 8, 'weight' => 85],
            ],
            'Push Up' => [
                ['reps' => 20, 'weight' => null],
                ['reps' => 18, 'weight' => null],
            ],
        ]);
    }

    private function seedLegDay(User $user): void
    {
        $workout = $user->workouts()->create([
            'title' => 'Leg Day',
            'performed_at' => now()->subDays(5)->setTime(17, 15),
        ]);

        $this->addStrengthExercises($workout, [
            'Squat' => [
                ['reps' => 10, 'weight' => 185],
                ['reps' => 8, 'weight' => 205],
                ['reps' => 5, 'weight' => 225],
            ],
            'Romanian Deadlift' => [
                ['reps' => 10, 'weight' => 155],
                ['reps' => 10, 'weight' => 155],
            ],
        ]);
    }

    private function seedMorningRun(User $user): void
    {
        $workout = $user->workouts()->create([
            'title' => 'Morning Run',
            'performed_at' => now()->subDay()->setTime(7, 0),
        ]);

        $workout->exercises()->create([
            'name' => 'Run',
            'type' => ExerciseType::Cardio,
            'duration_seconds' => 1930, // 32:10
            'distance_miles' => 3.10,
        ]);
    }

    private function seedRecentHistory(User $user): void
    {
        Workout::factory()
            ->for($user)
            ->count(8)
            ->has(Exercise::factory()->count(3)->hasSets(3))
            ->create();
    }

    /**
     * @param  array<string, list<array{reps: int, weight: int|null}>>  $exercises
     */
    private function addStrengthExercises(Workout $workout, array $exercises): void
    {
        foreach ($exercises as $name => $sets) {
            $workout->exercises()
                ->create(['name' => $name])
                ->sets()
                ->createMany($sets);
        }
    }
}
