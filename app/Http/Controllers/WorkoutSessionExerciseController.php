<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutSessionExerciseRequest;
use App\Http\Requests\UpdateWorkoutSessionExerciseRequest;
use App\Models\WorkoutSession;
use App\Models\WorkoutSessionExercise;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class WorkoutSessionExerciseController extends Controller
{
    /**
     * The name is matched against the user's catalog rather than sent as an id,
     * so typing a movement they have never logged still adds it in one step.
     */
    public function store(StoreWorkoutSessionExerciseRequest $request, WorkoutSession $workoutSession): RedirectResponse
    {
        $this->authorize('update', $workoutSession);

        $validated = $request->validated();

        $exercise = $request->user()->exercises()->firstOrCreate(
            ['name' => $validated['name']],
            ['type' => $validated['type']],
        );

        $workoutSession->exercises()->create([
            'exercise_id' => $exercise->id,
            'duration_seconds' => $validated['duration_seconds'] ?? null,
            'distance_miles' => $validated['distance_miles'] ?? null,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise added.')]);

        return back();
    }

    public function update(UpdateWorkoutSessionExerciseRequest $request, WorkoutSessionExercise $workoutSessionExercise): RedirectResponse
    {
        $workoutSessionExercise->load('workoutSession');

        $this->authorize('update', $workoutSessionExercise);

        $workoutSessionExercise->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise updated.')]);

        return back();
    }

    public function destroy(WorkoutSessionExercise $workoutSessionExercise): RedirectResponse
    {
        $workoutSessionExercise->load('workoutSession');

        $this->authorize('delete', $workoutSessionExercise);

        $workoutSessionExercise->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise removed.')]);

        return back();
    }
}
