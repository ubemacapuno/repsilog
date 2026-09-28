<?php

use App\Models\ExerciseSet;
use App\Models\WorkoutExercise;

it('belongs to the workout entry it was logged under', function () {
    $entry = WorkoutExercise::factory()->create();
    $set = ExerciseSet::factory()->for($entry)->create();

    expect($set->workoutExercise->id)->toBe($entry->id);
});

it('casts reps sent as a string to an integer', function () {
    $set = ExerciseSet::factory()->create(['reps' => '10']);

    expect($set->reps)->toBe(10);
});

it('casts weight to two decimal places', function () {
    $set = ExerciseSet::factory()->create(['weight' => 135]);

    expect($set->weight)->toBe('135.00');
});

it('rounds a weight carrying more precision than two decimal places', function () {
    $set = ExerciseSet::factory()->create(['weight' => 42.567]);

    expect($set->weight)->toBe('42.57');
});

it('does not allow the parent workout entry to be mass assigned', function () {
    $mine = WorkoutExercise::factory()->create();
    $theirs = WorkoutExercise::factory()->create();

    $set = $mine->sets()->create([
        'reps' => 8,
        'workout_exercise_id' => $theirs->id,
    ]);

    expect($set->workout_exercise_id)->toBe($mine->id);
});
