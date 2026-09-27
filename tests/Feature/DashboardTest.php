<?php

use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\User;
use App\Models\Workout;
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
    $exercise = Exercise::factory()
        ->for(Workout::factory()->for($user)->create(['performed_at' => now()]))
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
            ->where('stats.totalVolume', 1000)
            ->has('recentWorkouts', 1)
        );
});
