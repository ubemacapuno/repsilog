<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateExerciseSetRequest;
use App\Models\ExerciseSet;
use App\Models\WorkoutSessionExercise;
use Illuminate\Http\RedirectResponse;

class ExerciseSetController extends Controller
{
    public function store(WorkoutSessionExercise $workoutSessionExercise): RedirectResponse
    {
        $workoutSessionExercise->load('workoutSession');

        $this->authorize('update', $workoutSessionExercise);

        $previous = $workoutSessionExercise->sets()->reorder('id', 'desc')->first();

        $workoutSessionExercise->sets()->create([
            'reps' => $previous->reps ?? 0,
            'weight' => $previous?->weight,
            'completed_at' => null,
        ]);

        return back();
    }

    public function update(UpdateExerciseSetRequest $request, ExerciseSet $set): RedirectResponse
    {
        $set->load('workoutSessionExercise.workoutSession');

        $this->authorize('update', $set);

        $set->update($request->validated());

        return back();
    }

    public function destroy(ExerciseSet $set): RedirectResponse
    {
        $set->load('workoutSessionExercise.workoutSession');

        $this->authorize('delete', $set);

        $set->delete();

        return back();
    }
}
