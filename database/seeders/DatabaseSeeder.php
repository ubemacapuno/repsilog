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
        $this->seedCardioSessions($user);
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

    private function seedCardioSessions(User $user): void
    {
        $workout = $user->workouts()->create([
            'title' => 'Morning Run',
            'performed_at' => now()->subDay()->setTime(7, 0),
        ]);

        $this->addCardioExercises($workout, [
            ['name' => 'Run', 'duration_seconds' => 1930, 'distance_miles' => 3.10],
        ]);

        $workout = $user->workouts()->create([
            'title' => 'Interval Session',
            'performed_at' => now()->subDays(3)->setTime(6, 45),
            'notes' => 'Treadmill sprints, then walked it off.',
        ]);

        $this->addCardioExercises($workout, [
            ['name' => 'Treadmill Run', 'duration_seconds' => 1200, 'distance_miles' => 2.20],
            ['name' => 'Incline Walk', 'duration_seconds' => 600, 'distance_miles' => 0.55],
        ]);

        $workout = $user->workouts()->create([
            'title' => 'Long Ride',
            'performed_at' => now()->subDays(7)->setTime(9, 15),
        ]);

        $this->addCardioExercises($workout, [
            ['name' => 'Cycling', 'duration_seconds' => 4320, 'distance_miles' => 18.40],
        ]);

        $workout = $user->workouts()->create([
            'title' => 'Conditioning',
            'performed_at' => now()->subDays(9)->setTime(12, 30),
        ]);

        $this->addCardioExercises($workout, [
            ['name' => 'Rowing', 'duration_seconds' => 900, 'distance_miles' => 1.55],
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

    /**
     * @param  list<array{name: string, duration_seconds: int, distance_miles: float|null}>  $exercises
     */
    private function addCardioExercises(Workout $workout, array $exercises): void
    {
        foreach ($exercises as $exercise) {
            $workout->exercises()->create([
                ...$exercise,
                'type' => ExerciseType::Cardio,
            ]);
        }
    }
}
