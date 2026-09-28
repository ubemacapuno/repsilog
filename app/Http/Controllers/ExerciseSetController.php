<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateExerciseSetRequest;
use App\Models\ExerciseSet;
use App\Models\WorkoutExercise;
use Illuminate\Http\RedirectResponse;

class ExerciseSetController extends Controller
{
    public function store(WorkoutExercise $workoutExercise): RedirectResponse
    {
        $workoutExercise->load('workout');

        $this->authorize('update', $workoutExercise);

        $previous = $workoutExercise->sets()->latest('id')->first();

        $workoutExercise->sets()->create([
            'reps' => $previous->reps ?? 0,
            'weight' => $previous->weight ?? null,
            'completed_at' => null,
        ]);

        return back();
    }

    public function update(UpdateExerciseSetRequest $request, ExerciseSet $set): RedirectResponse
    {
        $set->load('workoutExercise.workout');

        $this->authorize('update', $set);

        $set->update($request->validated());

        return back();
    }

    public function destroy(ExerciseSet $set): RedirectResponse
    {
        $set->load('workoutExercise.workout');

        $this->authorize('delete', $set);

        $set->delete();

        return back();
    }
}
