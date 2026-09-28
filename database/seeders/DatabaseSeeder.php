<?php

namespace Database\Seeders;

use App\Enums\ExerciseType;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\User;
use App\Models\WorkoutSession;
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
        $workoutSession = $user->workoutSessions()->create([
            'title' => 'Push Day',
            'performed_at' => now()->subDays(2)->setTime(18, 30),
            'notes' => 'Felt strong. Bench moved well.',
        ]);

        $this->addStrengthExercises($workoutSession, [
            'Bench Press' => [
                ['reps' => 8, 'weight' => 135],
                ['reps' => 5, 'weight' => 155],
                ['reps' => 3, 'weight' => 175],
                ['reps' => 2, 'weight' => 185],
            ],
            'Overhead Press' => [
                ['reps' => 10, 'weight' => 75],
                ['reps' => 8, 'weight' => 85],
                ['reps' => 8, 'weight' => 85],
            ],
            'Push Up' => [
                ['reps' => 20, 'weight' => null],
                ['reps' => 18, 'weight' => null, 'completed' => false],
            ],
        ]);
    }

    private function seedLegDay(User $user): void
    {
        $workoutSession = $user->workoutSessions()->create([
            'title' => 'Leg Day',
            'performed_at' => now()->subDays(5)->setTime(17, 15),
        ]);

        $this->addStrengthExercises($workoutSession, [
            'Squat' => [
                ['reps' => 8, 'weight' => 185],
                ['reps' => 8, 'weight' => 205],
                ['reps' => 5, 'weight' => 225],
            ],
            'Romanian Deadlift' => [
                ['reps' => 8, 'weight' => 155],
                ['reps' => 10, 'weight' => 155],
            ],
        ]);
    }

    private function seedCardioSessions(User $user): void
    {
        $workoutSession = $user->workoutSessions()->create([
            'title' => 'Morning Run',
            'performed_at' => now()->subDay()->setTime(7, 0),
        ]);

        $this->addCardioExercises($workoutSession, [
            ['name' => 'Run', 'duration_seconds' => 1930, 'distance_miles' => 3.10],
        ]);

        $workoutSession = $user->workoutSessions()->create([
            'title' => 'Interval Session',
            'performed_at' => now()->subDays(3)->setTime(6, 45),
            'notes' => 'Treadmill sprints, then walked it off.',
        ]);

        $this->addCardioExercises($workoutSession, [
            ['name' => 'Treadmill Run', 'duration_seconds' => 1200, 'distance_miles' => 2.20],
            ['name' => 'Incline Walk', 'duration_seconds' => 600, 'distance_miles' => 0.55],
        ]);

        $workoutSession = $user->workoutSessions()->create([
            'title' => 'Long Ride',
            'performed_at' => now()->subDays(7)->setTime(9, 15),
        ]);

        $this->addCardioExercises($workoutSession, [
            ['name' => 'Cycling', 'duration_seconds' => 4320, 'distance_miles' => 18.40],
        ]);

        $workoutSession = $user->workoutSessions()->create([
            'title' => 'Conditioning',
            'performed_at' => now()->subDays(9)->setTime(12, 30),
        ]);

        $this->addCardioExercises($workoutSession, [
            ['name' => 'Rowing', 'duration_seconds' => 900, 'distance_miles' => 1.55],
        ]);
    }

    /**
     * Reuses the catalog the sessions above already built, so the history looks
     * like the same lifter repeating the same movements.
     */
    private function seedRecentHistory(User $user): void
    {
        $catalog = $user->exercises()
            ->where('type', ExerciseType::Strength)
            ->pluck('id');

        WorkoutSession::factory()
            ->for($user)
            ->count(8)
            ->create()
            ->each(function (WorkoutSession $workoutSession) use ($catalog): void {
                foreach ($catalog->random(3) as $exerciseId) {
                    ExerciseSet::factory()
                        ->count(3)
                        ->for($workoutSession->exercises()->create(['exercise_id' => $exerciseId]))
                        ->create(['completed_at' => $workoutSession->performed_at]);
                }
            });
    }

    /**
     * @param  array<string, list<array{reps: int, weight: int|null, completed?: bool}>>  $exercises
     */
    private function addStrengthExercises(WorkoutSession $workoutSession, array $exercises): void
    {
        foreach ($exercises as $name => $sets) {
            $workoutSession->exercises()
                ->create(['exercise_id' => $this->catalog($workoutSession, $name)->id])
                ->sets()
                ->createMany(array_map(
                    fn (array $set): array => [
                        'reps' => $set['reps'],
                        'weight' => $set['weight'],
                        'completed_at' => ($set['completed'] ?? true)
                            ? $workoutSession->performed_at
                            : null,
                    ],
                    $sets,
                ));
        }
    }

    /**
     * @param  list<array{name: string, duration_seconds: int, distance_miles: float|null}>  $exercises
     */
    private function addCardioExercises(WorkoutSession $workoutSession, array $exercises): void
    {
        foreach ($exercises as $exercise) {
            $workoutSession->exercises()->create([
                'exercise_id' => $this->catalog($workoutSession, $exercise['name'], ExerciseType::Cardio)->id,
                'duration_seconds' => $exercise['duration_seconds'],
                'distance_miles' => $exercise['distance_miles'],
            ]);
        }
    }

    private function catalog(WorkoutSession $workoutSession, string $name, ExerciseType $type = ExerciseType::Strength): Exercise
    {
        return $workoutSession->user->exercises()->firstOrCreate(
            ['name' => $name],
            ['type' => $type],
        );
    }
}
