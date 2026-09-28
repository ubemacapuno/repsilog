<?php

use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\User;
use App\Models\WorkoutSession;
use App\Models\WorkoutSessionExercise;

it('belongs to the user who logged it', function () {
    $user = User::factory()->create();
    $workoutSession = WorkoutSession::factory()->for($user)->create();

    expect($workoutSession->user->id)->toBe($user->id);
});

it('has many exercises', function () {
    $workoutSession = WorkoutSession::factory()->hasExercises(3)->create();

    expect($workoutSession->exercises)->toHaveCount(3)
        ->and($workoutSession->exercises->first())->toBeInstanceOf(WorkoutSessionExercise::class);
});

it('keeps each users workouts separate', function () {
    $me = User::factory()->hasWorkoutSessions(2)->create();
    User::factory()->hasWorkoutSessions(5)->create();

    expect($me->workoutSessions)->toHaveCount(2)
        ->and(WorkoutSession::count())->toBe(7);
});

it('cascades deletes all the way down to sets, leaving the catalog alone', function () {
    $workoutSession = WorkoutSession::factory()
        ->has(WorkoutSessionExercise::factory()->hasSets(3), 'exercises')
        ->create();

    $workoutSession->delete();

    expect(WorkoutSessionExercise::count())->toBe(0)
        ->and(ExerciseSet::count())->toBe(0)
        ->and(Exercise::count())->toBe(1);
});

it('does not allow the owner to be mass assigned', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();

    $workoutSession = $owner->workoutSessions()->create([
        'performed_at' => now(),
        'user_id' => $attacker->id,
    ]);

    expect($workoutSession->user_id)->toBe($owner->id);
});
