<?php

use App\Models\Exercise;
use App\Models\ExerciseSet;

it('belongs to the exercise it was logged under', function () {
    $exercise = Exercise::factory()->create();
    $set = ExerciseSet::factory()->for($exercise)->create();

    expect($set->exercise->id)->toBe($exercise->id);
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

it('does not allow the parent exercise to be mass assigned', function () {
    $mine = Exercise::factory()->create();
    $theirs = Exercise::factory()->create();

    $set = $mine->sets()->create([
        'reps' => 8,
        'exercise_id' => $theirs->id,
    ]);

    expect($set->exercise_id)->toBe($mine->id);
});
