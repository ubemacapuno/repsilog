<?php

use App\Enums\ExerciseType;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\User;
use App\Models\Workout;
use Inertia\Testing\AssertableInertia;

function exerciseOwnedBy(User $user): Exercise
{
    return Exercise::factory()
        ->for(Workout::factory()->for($user))
        ->create(['type' => ExerciseType::Strength]);
}

it('loads the workout index', function () {
    $user = User::factory()->hasWorkouts(2)->create();

    $this->actingAs($user)
        ->get(route('workouts.index'))
        ->assertOk();
});

it('shows only the latest ten workouts on the first page', function () {
    $user = User::factory()->hasWorkouts(12)->create();

    $this->actingAs($user)
        ->get(route('workouts.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('workouts.data', 10)
            ->where('workouts.total', 12)
        );
});

it('does not let a user open someone elses workout', function () {
    $workout = Workout::factory()->for(User::factory())->create();

    $this->actingAs(User::factory()->create())
        ->get(route('workouts.show', $workout))
        ->assertForbidden();
});

it('adds an exercise to a workout without any sets', function () {
    $user = User::factory()->create();
    $workout = Workout::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('exercises.store', $workout), [
            'name' => 'Bench Press',
            'type' => 'strength',
        ])
        ->assertRedirect();

    expect($workout->exercises()->sole())
        ->name->toBe('Bench Press')
        ->sets->toHaveCount(0);
});

it('requires a duration for a cardio exercise', function () {
    $user = User::factory()->create();
    $workout = Workout::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('exercises.store', $workout), [
            'name' => 'Run',
            'type' => 'cardio',
        ])
        ->assertInvalid('duration_seconds');
});

it('edits the duration and distance of a cardio exercise', function () {
    $user = User::factory()->create();
    $run = Exercise::factory()
        ->cardio()
        ->for(Workout::factory()->for($user))
        ->create(['duration_seconds' => 600, 'distance_miles' => '1.00']);

    $this->actingAs($user)
        ->patch(route('exercises.update', $run), [
            'duration_seconds' => 1530,
            'distance_miles' => '3.25',
        ])
        ->assertRedirect();

    expect($run->refresh())
        ->duration_seconds->toBe(1530)
        ->distance_miles->toBe('3.25');
});

it('rejects a cardio duration of zero', function () {
    $user = User::factory()->create();
    $run = Exercise::factory()
        ->cardio()
        ->for(Workout::factory()->for($user))
        ->create();

    $this->actingAs($user)
        ->patch(route('exercises.update', $run), ['duration_seconds' => 0])
        ->assertInvalid('duration_seconds');
});

it('does not let a user edit someone elses exercise', function () {
    $run = Exercise::factory()
        ->cardio()
        ->for(Workout::factory()->for(User::factory()))
        ->create();

    $this->actingAs(User::factory()->create())
        ->patch(route('exercises.update', $run), ['duration_seconds' => 60])
        ->assertForbidden();
});

it('starts the first set of an exercise empty', function () {
    $user = User::factory()->create();

    $exercise = exerciseOwnedBy($user);

    $this->actingAs($user)
        ->post(route('sets.store', $exercise))
        ->assertRedirect();

    expect($exercise->sets()->sole())
        ->reps->toBe(0)
        ->weight->toBeNull()
        ->completed_at->toBeNull();
});

it('copies the previous set values onto a new set', function () {
    $user = User::factory()->create();
    $exercise = exerciseOwnedBy($user);

    ExerciseSet::factory()->for($exercise)->create(['reps' => 12, 'weight' => '95.00']);

    $this->actingAs($user)
        ->post(route('sets.store', $exercise))
        ->assertRedirect();

    expect($exercise->sets()->latest('id')->first())
        ->reps->toBe(12)
        ->weight->toBe('95.00');
});

it('saves a set and marks it complete', function () {
    $user = User::factory()->create();
    $set = ExerciseSet::factory()
        ->for(exerciseOwnedBy($user))
        ->create(['reps' => 0, 'completed_at' => null]);

    $this->actingAs($user)
        ->patch(route('sets.update', $set), [
            'reps' => 12,
            'weight' => '95.00',
            'completed_at' => now()->toIso8601String(),
        ])
        ->assertRedirect();

    expect($set->refresh())
        ->reps->toBe(12)
        ->weight->toBe('95.00')
        ->completed_at->not->toBeNull();
});

it('does not let a user save a set belonging to someone else', function () {
    $set = ExerciseSet::factory()
        ->for(exerciseOwnedBy(User::factory()->create()))
        ->create();

    $this->actingAs(User::factory()->create())
        ->patch(route('sets.update', $set), ['reps' => 7])
        ->assertForbidden();
});

it('does not let a user add a set to someone elses exercise', function () {
    $exercise = exerciseOwnedBy(User::factory()->create());

    $this->actingAs(User::factory()->create())
        ->post(route('sets.store', $exercise))
        ->assertForbidden();
});

it('deleting a workout takes its exercises and sets with it', function () {
    $user = User::factory()->create();
    $workout = Workout::factory()->for($user)
        ->has(Exercise::factory()->hasSets(3))
        ->create();

    $this->actingAs($user)
        ->delete(route('workouts.destroy', $workout))
        ->assertRedirect(route('workouts.index'));

    expect(Workout::count())->toBe(0)
        ->and(Exercise::count())->toBe(0)
        ->and(ExerciseSet::count())->toBe(0);
});
