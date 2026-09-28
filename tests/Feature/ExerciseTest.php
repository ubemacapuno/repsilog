<?php

use App\Enums\ExerciseType;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\User;
use App\Models\WorkoutSession;
use App\Models\WorkoutSessionExercise;
use Illuminate\Database\UniqueConstraintViolationException;

it('casts type to the ExerciseType enum', function () {
    $exercise = Exercise::factory()->create();

    expect($exercise->type)->toBe(ExerciseType::Strength);
});

it('is reused by every workout that includes it', function () {
    $user = User::factory()->create();
    $bench = Exercise::factory()->for($user)->create(['name' => 'Bench Press']);

    WorkoutSession::factory()->for($user)->count(3)->create()
        ->each(fn (WorkoutSession $workoutSession) => $workoutSession->exercises()->create(['exercise_id' => $bench->id]));

    expect($bench->workoutSessionExercises)->toHaveCount(3)
        ->and(Exercise::where('name', 'Bench Press')->count())->toBe(1);
});

it('cannot hold the same name twice for one user', function () {
    $user = User::factory()->create();
    Exercise::factory()->for($user)->create(['name' => 'Squat']);

    expect(fn () => Exercise::factory()->for($user)->create(['name' => 'Squat']))
        ->toThrow(UniqueConstraintViolationException::class);
});

it('lets two users each keep their own copy of a name', function () {
    Exercise::factory()->for(User::factory())->create(['name' => 'Squat']);
    Exercise::factory()->for(User::factory())->create(['name' => 'Squat']);

    expect(Exercise::where('name', 'Squat')->count())->toBe(2);
});

it('records a cardio exercise with duration and distance on the workout entry', function () {
    $run = WorkoutSessionExercise::factory()->cardio()->create();

    expect($run->exercise->type)->toBe(ExerciseType::Cardio)
        ->and($run->duration_seconds)->toBeGreaterThan(0)
        ->and($run->sets)->toHaveCount(0);
});

it('has many sets each with its own reps', function () {
    $entry = WorkoutSessionExercise::factory()->hasSets(3)->create();

    expect($entry->sets)->toHaveCount(3)
        ->and($entry->sets->first())->toBeInstanceOf(ExerciseSet::class);
});

it('allows a bodyweight set with no weight', function () {
    $set = ExerciseSet::factory()->bodyweight()->create();

    expect($set->weight)->toBeNull();
});

it('sums reps across sets', function () {
    $entry = WorkoutSessionExercise::factory()->create();
    $entry->sets()->createMany([
        ['reps' => 10], ['reps' => 8], ['reps' => 6],
    ]);

    expect((int) $entry->sets()->sum('reps'))->toBe(24);
});
