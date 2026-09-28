<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ExerciseSetController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\WorkoutExerciseController;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // There are no create or edit pages. New workouts come from a dialog on the
    // index, and workouts.show edits its own fields in place.
    Route::resource('workouts', WorkoutController::class)->except(['create', 'edit']);

    // The catalog of movements the user reuses across workouts. Renaming here
    // renames everywhere, and deleting is only allowed while unused.
    Route::get('exercises', [ExerciseController::class, 'index'])->name('exercises.index');
    Route::patch('exercises/{exercise}', [ExerciseController::class, 'update'])
        ->name('exercises.update');
    Route::delete('exercises/{exercise}', [ExerciseController::class, 'destroy'])
        ->name('exercises.destroy');

    Route::post('workouts/{workout}/exercises', [WorkoutExerciseController::class, 'store'])
        ->name('workout-exercises.store');
    Route::patch('workout-exercises/{workoutExercise}', [WorkoutExerciseController::class, 'update'])
        ->name('workout-exercises.update');
    Route::delete('workout-exercises/{workoutExercise}', [WorkoutExerciseController::class, 'destroy'])
        ->name('workout-exercises.destroy');

    Route::post('workout-exercises/{workoutExercise}/sets', [ExerciseSetController::class, 'store'])
        ->name('sets.store');
    Route::patch('sets/{set}', [ExerciseSetController::class, 'update'])
        ->name('sets.update');
    Route::delete('sets/{set}', [ExerciseSetController::class, 'destroy'])
        ->name('sets.destroy');
});

require __DIR__.'/settings.php';
