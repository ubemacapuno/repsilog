<?php

use App\Enums\ExerciseType;
use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutSession;
use App\Models\WorkoutSessionExercise;
use Inertia\Testing\AssertableInertia;

function catalogEntryFor(User $user, string $name = 'Bench Press'): Exercise
{
    return Exercise::factory()->for($user)->create(['name' => $name]);
}

it('lists only the signed in users exercises with their usage counts', function () {
    $user = User::factory()->create();
    $bench = catalogEntryFor($user);
    catalogEntryFor($user, 'Squat');
    catalogEntryFor(User::factory()->create(), 'Deadlift');

    WorkoutSessionExercise::factory()
        ->for(WorkoutSession::factory()->for($user))
        ->for($bench)
        ->create();

    $this->actingAs($user)
        ->get(route('exercises.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('exercises/Index')
            ->has('exercises.data', 2)
            ->where('exercises.data.0.name', 'Bench Press')
            ->where('exercises.data.0.workout_session_exercises_count', 1)
            ->where('exercises.data.1.workout_session_exercises_count', 0)
        );
});

it('paginates the catalog at fifteen per page', function () {
    $user = User::factory()->create();

    foreach (range(1, 18) as $number) {
        catalogEntryFor($user, "Movement {$number}");
    }

    $this->actingAs($user)
        ->get(route('exercises.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('exercises.data', 15)
            ->where('exercises.total', 18)
        );
});

it('renames an exercise everywhere it is used', function () {
    $user = User::factory()->create();
    $exercise = catalogEntryFor($user, 'Bench');
    $entry = WorkoutSessionExercise::factory()
        ->for(WorkoutSession::factory()->for($user))
        ->for($exercise)
        ->create();

    $this->actingAs($user)
        ->patch(route('exercises.update', $exercise), ['name' => 'Bench Press'])
        ->assertRedirect();

    expect($entry->refresh()->exercise->name)->toBe('Bench Press');
});

it('rejects a rename that collides with another of the users exercises', function () {
    $user = User::factory()->create();
    catalogEntryFor($user, 'Squat');
    $exercise = catalogEntryFor($user, 'Front Squat');

    $this->actingAs($user)
        ->patch(route('exercises.update', $exercise), ['name' => 'Squat'])
        ->assertInvalid('name');
});

it('allows a rename that collides only with another users exercise', function () {
    $user = User::factory()->create();
    catalogEntryFor(User::factory()->create(), 'Squat');
    $exercise = catalogEntryFor($user, 'Front Squat');

    $this->actingAs($user)
        ->patch(route('exercises.update', $exercise), ['name' => 'Squat'])
        ->assertRedirect();

    expect($exercise->refresh()->name)->toBe('Squat');
});

it('does not let a user rename someone elses exercise', function () {
    $exercise = catalogEntryFor(User::factory()->create());

    $this->actingAs(User::factory()->create())
        ->patch(route('exercises.update', $exercise), ['name' => 'Mine now'])
        ->assertForbidden();
});

it('deletes an exercise no workout uses', function () {
    $user = User::factory()->create();
    $exercise = catalogEntryFor($user);

    $this->actingAs($user)
        ->delete(route('exercises.destroy', $exercise))
        ->assertRedirect();

    expect(Exercise::count())->toBe(0);
});

it('refuses to delete an exercise a workout still uses', function () {
    $user = User::factory()->create();
    $exercise = catalogEntryFor($user);

    WorkoutSessionExercise::factory()
        ->for(WorkoutSession::factory()->for($user))
        ->for($exercise)
        ->create();

    $this->actingAs($user)
        ->delete(route('exercises.destroy', $exercise))
        ->assertForbidden();

    expect(Exercise::count())->toBe(1);
});

it('rejects a rename that collides only by case', function () {
    $user = User::factory()->create();
    catalogEntryFor($user, 'Squat');
    $exercise = catalogEntryFor($user, 'Front Squat');

    $this->actingAs($user)
        ->patch(route('exercises.update', $exercise), ['name' => 'squat'])
        ->assertInvalid('name');
});

it('offers the whole catalog when adding an exercise to a workout', function () {
    $user = User::factory()->create();
    $workoutSession = WorkoutSession::factory()->for($user)->create();
    catalogEntryFor($user, 'Squat');
    Exercise::factory()->for($user)->cardio()->create(['name' => 'Run']);

    $this->actingAs($user)
        ->get(route('workouts.show', $workoutSession))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('exercises', 2)
            ->where('exercises.0.name', 'Run')
            ->where('exercises.0.type', ExerciseType::Cardio->value)
        );
});

it('sends each workout entry with its catalog name and sets attached', function () {
    $user = User::factory()->create();
    $workoutSession = WorkoutSession::factory()->for($user)->create();

    WorkoutSessionExercise::factory()
        ->for($workoutSession)
        ->for(catalogEntryFor($user, 'Bench Press'))
        ->hasSets(2)
        ->create();

    $this->actingAs($user)
        ->get(route('workouts.show', $workoutSession))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('workout.exercises.0.exercise.name', 'Bench Press')
            ->where('workout.exercises.0.exercise.type', ExerciseType::Strength->value)
            ->has('workout.exercises.0.sets', 2)
        );
});
