<?php

use App\Enums\ExerciseType;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\User;
use App\Models\WorkoutSession;
use App\Models\WorkoutSessionExercise;
use Inertia\Testing\AssertableInertia;

function entryOwnedBy(User $user, ExerciseType $type = ExerciseType::Strength): WorkoutSessionExercise
{
    return WorkoutSessionExercise::factory()
        ->for(WorkoutSession::factory()->for($user))
        ->for(Exercise::factory()->for($user)->state(['type' => $type]))
        ->create();
}

it('loads the workout index', function () {
    $user = User::factory()->hasWorkoutSessions(2)->create();

    $this->actingAs($user)
        ->get(route('workouts.index'))
        ->assertOk();
});

it('shows only the latest ten workouts on the first page', function () {
    $user = User::factory()->hasWorkoutSessions(12)->create();

    $this->actingAs($user)
        ->get(route('workouts.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('workouts.data', 10)
            ->where('workouts.total', 12)
        );
});

it('does not let a user open someone elses workout', function () {
    $workoutSession = WorkoutSession::factory()->for(User::factory())->create();

    $this->actingAs(User::factory()->create())
        ->get(route('workouts.show', $workoutSession))
        ->assertForbidden();
});

it('adds an exercise to a workout without any sets', function () {
    $user = User::factory()->create();
    $workoutSession = WorkoutSession::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('workout-session-exercises.store', $workoutSession), [
            'name' => 'Bench Press',
            'type' => 'strength',
        ])
        ->assertRedirect();

    expect($workoutSession->exercises()->sole())
        ->exercise->name->toBe('Bench Press')
        ->sets->toHaveCount(0);
});

it('reuses the catalog entry when the same name is added again', function () {
    $user = User::factory()->create();
    $actor = $this->actingAs($user);

    foreach (WorkoutSession::factory()->for($user)->count(2)->create() as $workoutSession) {
        $actor->post(route('workout-session-exercises.store', $workoutSession), [
            'name' => 'Bench Press',
            'type' => 'strength',
        ])->assertRedirect();
    }

    expect($user->exercises()->sole())
        ->name->toBe('Bench Press')
        ->workoutSessionExercises->toHaveCount(2);
});

it('rejects cardio fields when the catalog says the name is a strength move', function () {
    $user = User::factory()->create();
    $workoutSession = WorkoutSession::factory()->for($user)->create();
    Exercise::factory()->for($user)->create(['name' => 'Row', 'type' => ExerciseType::Strength]);

    $this->actingAs($user)
        ->post(route('workout-session-exercises.store', $workoutSession), [
            'name' => 'Row',
            'type' => 'cardio',
            'duration_seconds' => 600,
            'distance_miles' => '2.00',
        ])
        ->assertInvalid(['duration_seconds', 'distance_miles']);

    expect($user->exercises()->sole()->type)->toBe(ExerciseType::Strength)
        ->and($workoutSession->exercises()->count())->toBe(0);
});

it('requires a duration when the catalog says the name is cardio', function () {
    $user = User::factory()->create();
    $workoutSession = WorkoutSession::factory()->for($user)->create();
    Exercise::factory()->for($user)->cardio()->create(['name' => 'Run']);

    $this->actingAs($user)
        ->post(route('workout-session-exercises.store', $workoutSession), [
            'name' => 'Run',
            'type' => 'strength',
        ])
        ->assertInvalid('duration_seconds');

    expect($workoutSession->exercises()->count())->toBe(0);
});

it('never stores cardio fields on a strength entry', function () {
    $user = User::factory()->create();
    $workoutSession = WorkoutSession::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('workout-session-exercises.store', $workoutSession), [
            'name' => 'Bench Press',
            'type' => 'strength',
            'duration_seconds' => 600,
        ])
        ->assertInvalid('duration_seconds');

    expect($workoutSession->exercises()->count())->toBe(0);
});

it('reuses a catalog entry whose name differs only by case', function () {
    $user = User::factory()->create();
    $workoutSession = WorkoutSession::factory()->for($user)->create();
    Exercise::factory()->for($user)->create(['name' => 'Bench Press']);

    $this->actingAs($user)
        ->post(route('workout-session-exercises.store', $workoutSession), [
            'name' => 'bench press',
            'type' => 'strength',
        ])
        ->assertRedirect();

    expect($user->exercises()->sole()->name)->toBe('Bench Press');
});

it('does not borrow another users catalog entry', function () {
    $user = User::factory()->create();
    $workoutSession = WorkoutSession::factory()->for($user)->create();
    $theirs = Exercise::factory()->for(User::factory())->create(['name' => 'Squat']);

    $this->actingAs($user)
        ->post(route('workout-session-exercises.store', $workoutSession), [
            'name' => 'Squat',
            'type' => 'strength',
        ])
        ->assertRedirect();

    expect($user->exercises()->sole()->id)->not->toBe($theirs->id);
});

it('requires a duration for a cardio exercise', function () {
    $user = User::factory()->create();
    $workoutSession = WorkoutSession::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('workout-session-exercises.store', $workoutSession), [
            'name' => 'Run',
            'type' => 'cardio',
        ])
        ->assertInvalid('duration_seconds');
});

it('edits the duration and distance of a cardio exercise', function () {
    $user = User::factory()->create();
    $run = entryOwnedBy($user, ExerciseType::Cardio);
    $run->update(['duration_seconds' => 600, 'distance_miles' => '1.00']);

    $this->actingAs($user)
        ->patch(route('workout-session-exercises.update', $run), [
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
    $run = entryOwnedBy($user, ExerciseType::Cardio);

    $this->actingAs($user)
        ->patch(route('workout-session-exercises.update', $run), ['duration_seconds' => 0])
        ->assertInvalid('duration_seconds');
});

it('rejects a duration sent for a strength exercise', function () {
    $user = User::factory()->create();
    $entry = entryOwnedBy($user);

    $this->actingAs($user)
        ->patch(route('workout-session-exercises.update', $entry), ['duration_seconds' => 600])
        ->assertSessionHasErrors('duration_seconds');

    expect($entry->refresh()->duration_seconds)->toBeNull();
});

it('does not let a user edit someone elses exercise', function () {
    $run = entryOwnedBy(User::factory()->create(), ExerciseType::Cardio);

    $this->actingAs(User::factory()->create())
        ->patch(route('workout-session-exercises.update', $run), ['duration_seconds' => 60])
        ->assertForbidden();
});

it('starts the first set of an exercise empty', function () {
    $user = User::factory()->create();
    $entry = entryOwnedBy($user);

    $this->actingAs($user)
        ->post(route('sets.store', $entry))
        ->assertRedirect();

    expect($entry->sets()->sole())
        ->reps->toBe(0)
        ->weight->toBeNull()
        ->completed_at->toBeNull();
});

it('copies the previous set values onto a new set', function () {
    $user = User::factory()->create();
    $entry = entryOwnedBy($user);

    ExerciseSet::factory()->for($entry)->create(['reps' => 12, 'weight' => '95.00']);
    ExerciseSet::factory()->for($entry)->create(['reps' => 8, 'weight' => '115.00']);

    $this->actingAs($user)
        ->post(route('sets.store', $entry))
        ->assertRedirect();

    expect($entry->sets()->get()->last())
        ->reps->toBe(8)
        ->weight->toBe('115.00');
});

it('saves a set and marks it complete', function () {
    $user = User::factory()->create();
    $set = ExerciseSet::factory()
        ->for(entryOwnedBy($user))
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
        ->for(entryOwnedBy(User::factory()->create()))
        ->create();

    $this->actingAs(User::factory()->create())
        ->patch(route('sets.update', $set), ['reps' => 7])
        ->assertForbidden();
});

it('does not let a user add a set to someone elses exercise', function () {
    $entry = entryOwnedBy(User::factory()->create());

    $this->actingAs(User::factory()->create())
        ->post(route('sets.store', $entry))
        ->assertForbidden();
});

it('deleting a workout takes its exercises and sets with it', function () {
    $user = User::factory()->create();
    $workoutSession = WorkoutSession::factory()->for($user)
        ->has(WorkoutSessionExercise::factory()->hasSets(3), 'exercises')
        ->create();

    $this->actingAs($user)
        ->delete(route('workouts.destroy', $workoutSession))
        ->assertRedirect(route('workouts.index'));

    expect(WorkoutSession::count())->toBe(0)
        ->and(WorkoutSessionExercise::count())->toBe(0)
        ->and(ExerciseSet::count())->toBe(0);
});

it('paginates workouts that share a performed_at without repeating any', function () {
    $user = User::factory()->create();
    WorkoutSession::factory()->for($user)->count(12)->create([
        'performed_at' => now()->setTime(10, 0),
    ]);

    $actor = $this->actingAs($user);

    $first = $actor->get(route('workouts.index'))
        ->viewData('page')['props']['workouts']['data'];
    $second = $actor->get(route('workouts.index', ['page' => 2]))
        ->viewData('page')['props']['workouts']['data'];

    $ids = [...collect($first)->pluck('id'), ...collect($second)->pluck('id')];

    expect($ids)->toHaveCount(12)
        ->and(array_unique($ids))->toHaveCount(12);
});
