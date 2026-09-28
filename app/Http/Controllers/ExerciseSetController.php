<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateExerciseSetRequest;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use Illuminate\Http\RedirectResponse;

class ExerciseSetController extends Controller
{
    public function store(Exercise $exercise): RedirectResponse
    {
        $exercise->load('workout');

        $this->authorize('update', $exercise);

        $previous = $exercise->sets()->latest('id')->first();

        $exercise->sets()->create([
            'reps' => $previous->reps ?? 0,
            'weight' => $previous->weight ?? null,
            'completed_at' => null,
        ]);

        return back();
    }

    public function update(UpdateExerciseSetRequest $request, ExerciseSet $set): RedirectResponse
    {
        $set->load('exercise.workout');

        $this->authorize('update', $set);

        $set->update($request->validated());

        return back();
    }

    public function destroy(ExerciseSet $set): RedirectResponse
    {
        $set->load('exercise.workout');

        $this->authorize('delete', $set);

        $set->delete();

        return back();
    }
}
