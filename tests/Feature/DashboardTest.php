<?php

use App\Models\ExerciseSet;
use App\Models\User;
use App\Models\WorkoutSession;
use App\Models\WorkoutSessionExercise;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('the dashboard counts only the users own completed sets', function () {
    $user = User::factory()->create();
    $exercise = WorkoutSessionExercise::factory()
        ->for(WorkoutSession::factory()->for($user)->create(['performed_at' => now()]))
        ->create();

    ExerciseSet::factory()->for($exercise)->create([
        'reps' => 10,
        'weight' => '100.00',
        'completed_at' => now(),
    ]);
    ExerciseSet::factory()->for($exercise)->create([
        'reps' => 5,
        'weight' => '50.00',
        'completed_at' => null,
    ]);
    ExerciseSet::factory()->create(['completed_at' => now()]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('stats.workouts', 1)
            ->where('stats.workoutsThisWeek', 1)
            ->where('stats.setsCompleted', 1)
            ->where('stats.volumeLast7Days', 1000)
            ->has('recentWorkouts', 1, fn (AssertableInertia $workout) => $workout
                ->where('exercises_count', 1)
                ->etc()
            )
        );
});

test('volume covers only the last seven days, while set counts stay lifetime', function () {
    $user = User::factory()->create();

    $recent = WorkoutSessionExercise::factory()
        ->for(WorkoutSession::factory()->for($user)->create(['performed_at' => now()->subDays(2)]))
        ->create();
    $old = WorkoutSessionExercise::factory()
        ->for(WorkoutSession::factory()->for($user)->create(['performed_at' => now()->subDays(8)]))
        ->create();

    ExerciseSet::factory()->for($recent)->create([
        'reps' => 10,
        'weight' => '100.00',
        'completed_at' => now(),
    ]);
    ExerciseSet::factory()->for($old)->create([
        'reps' => 10,
        'weight' => '200.00',
        'completed_at' => now()->subDays(8),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('stats.setsCompleted', 2)
            ->where('stats.volumeLast7Days', 1000)
        );
});

test('a future dated workout is not counted in this week', function () {
    $user = User::factory()->create();
    WorkoutSession::factory()->for($user)->create(['performed_at' => now()]);
    WorkoutSession::factory()->for($user)->create(['performed_at' => now()->addWeeks(2)]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('stats.workouts', 2)
            ->where('stats.workoutsThisWeek', 1)
        );
});

test('fractional plate weight rounds rather than truncates', function () {
    $user = User::factory()->create();
    $exercise = WorkoutSessionExercise::factory()
        ->for(WorkoutSession::factory()->for($user)->create(['performed_at' => now()]))
        ->create();

    ExerciseSet::factory()->for($exercise)->create([
        'reps' => 3,
        'weight' => '2.50',
        'completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('stats.volumeLast7Days', 8)
        );
});
