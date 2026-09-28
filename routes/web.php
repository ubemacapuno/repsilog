<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ExerciseSetController;
use App\Http\Controllers\WorkoutSessionController;
use App\Http\Controllers\WorkoutSessionExerciseController;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // There are no create or edit pages. New workouts come from a dialog on the
    // index, and workouts.show edits its own fields in place.
    Route::resource('workouts', WorkoutSessionController::class)
        ->except(['create', 'edit'])
        ->parameters(['workouts' => 'workoutSession']);

    // The catalog of movements the user reuses across workouts. Renaming here
    // renames everywhere, and deleting is only allowed while unused.
    Route::get('exercises', [ExerciseController::class, 'index'])->name('exercises.index');
    Route::patch('exercises/{exercise}', [ExerciseController::class, 'update'])
        ->name('exercises.update');
    Route::delete('exercises/{exercise}', [ExerciseController::class, 'destroy'])
        ->name('exercises.destroy');

    Route::post('workouts/{workoutSession}/exercises', [WorkoutSessionExerciseController::class, 'store'])
        ->name('workout-session-exercises.store');
    Route::patch('workout-session-exercises/{workoutSessionExercise}', [WorkoutSessionExerciseController::class, 'update'])
        ->name('workout-session-exercises.update');
    Route::delete('workout-session-exercises/{workoutSessionExercise}', [WorkoutSessionExerciseController::class, 'destroy'])
        ->name('workout-session-exercises.destroy');

    Route::post('workout-session-exercises/{workoutSessionExercise}/sets', [ExerciseSetController::class, 'store'])
        ->name('sets.store');
    Route::patch('sets/{set}', [ExerciseSetController::class, 'update'])
        ->name('sets.update');
    Route::delete('sets/{set}', [ExerciseSetController::class, 'destroy'])
        ->name('sets.destroy');
});

require __DIR__.'/settings.php';
