<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutExerciseRequest;
use App\Http\Requests\UpdateWorkoutExerciseRequest;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class WorkoutExerciseController extends Controller
{
    /**
     * The name is matched against the user's catalog rather than sent as an id,
     * so typing a movement they have never logged still adds it in one step.
     */
    public function store(StoreWorkoutExerciseRequest $request, Workout $workout): RedirectResponse
    {
        $this->authorize('update', $workout);

        $validated = $request->validated();

        $exercise = $request->user()->exercises()->firstOrCreate(
            ['name' => $validated['name']],
            ['type' => $validated['type']],
        );

        $workout->exercises()->create([
            'exercise_id' => $exercise->id,
            'duration_seconds' => $validated['duration_seconds'] ?? null,
            'distance_miles' => $validated['distance_miles'] ?? null,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise added.')]);

        return back();
    }

    public function update(UpdateWorkoutExerciseRequest $request, WorkoutExercise $workoutExercise): RedirectResponse
    {
        $workoutExercise->load('workout');

        $this->authorize('update', $workoutExercise);

        $workoutExercise->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise updated.')]);

        return back();
    }

    public function destroy(WorkoutExercise $workoutExercise): RedirectResponse
    {
        $workoutExercise->load('workout');

        $this->authorize('delete', $workoutExercise);

        $workoutExercise->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise removed.')]);

        return back();
    }
}
