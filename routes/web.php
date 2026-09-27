<?php

use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ExerciseSetController;
use App\Http\Controllers\WorkoutController;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // There are no create or edit pages. New workouts come from a dialog on the
    // index, and workouts.show edits its own fields in place.
    Route::resource('workouts', WorkoutController::class)->except(['create', 'edit']);

    Route::post('workouts/{workout}/exercises', [ExerciseController::class, 'store'])
        ->name('exercises.store');
    Route::patch('exercises/{exercise}', [ExerciseController::class, 'update'])
        ->name('exercises.update');
    Route::delete('exercises/{exercise}', [ExerciseController::class, 'destroy'])
        ->name('exercises.destroy');

    Route::post('exercises/{exercise}/sets', [ExerciseSetController::class, 'store'])
        ->name('sets.store');
    Route::patch('sets/{set}', [ExerciseSetController::class, 'update'])
        ->name('sets.update');
    Route::delete('sets/{set}', [ExerciseSetController::class, 'destroy'])
        ->name('sets.destroy');
});

require __DIR__.'/settings.php';
