<?php

use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutExercise;

it('belongs to the user who logged it', function () {
    $user = User::factory()->create();
    $workout = Workout::factory()->for($user)->create();

    expect($workout->user->id)->toBe($user->id);
});

it('has many exercises', function () {
    $workout = Workout::factory()->hasExercises(3)->create();

    expect($workout->exercises)->toHaveCount(3)
        ->and($workout->exercises->first())->toBeInstanceOf(WorkoutExercise::class);
});

it('keeps each users workouts separate', function () {
    $me = User::factory()->hasWorkouts(2)->create();
    User::factory()->hasWorkouts(5)->create();

    expect($me->workouts)->toHaveCount(2)
        ->and(Workout::count())->toBe(7);
});

it('cascades deletes all the way down to sets, leaving the catalog alone', function () {
    $workout = Workout::factory()
        ->has(WorkoutExercise::factory()->hasSets(3), 'exercises')
        ->create();

    $workout->delete();

    expect(WorkoutExercise::count())->toBe(0)
        ->and(ExerciseSet::count())->toBe(0)
        ->and(Exercise::count())->toBe(1);
});

it('does not allow the owner to be mass assigned', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();

    $workout = $owner->workouts()->create([
        'performed_at' => now(),
        'user_id' => $attacker->id,
    ]);

    expect($workout->user_id)->toBe($owner->id);
});
