<?php

use App\Enums\ExerciseType;
use App\Models\Exercise;
use App\Models\ExerciseSet;

it('casts type to the ExerciseType enum', function () {
    $exercise = Exercise::factory()->create();

    expect($exercise->type)->toBe(ExerciseType::Strength);
});

it('records a cardio exercise with duration and distance', function () {
    $run = Exercise::factory()->cardio()->create();

    expect($run->type)->toBe(ExerciseType::Cardio)
        ->and($run->duration_seconds)->toBeGreaterThan(0)
        ->and($run->sets)->toHaveCount(0);
});

it('has many sets each with its own reps', function () {
    $exercise = Exercise::factory()->hasSets(3)->create();

    expect($exercise->sets)->toHaveCount(3)
        ->and($exercise->sets->first())->toBeInstanceOf(ExerciseSet::class);
});

it('allows a bodyweight set with no weight', function () {
    $set = ExerciseSet::factory()->bodyweight()->create();

    expect($set->weight)->toBeNull();
});

it('sums reps across sets', function () {
    $exercise = Exercise::factory()->create();
    $exercise->sets()->createMany([
        ['reps' => 10], ['reps' => 8], ['reps' => 6],
    ]);

    expect((int) $exercise->sets()->sum('reps'))->toBe(24);
});
