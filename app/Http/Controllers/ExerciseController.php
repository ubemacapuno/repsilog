<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExerciseRequest;
use App\Models\Exercise;
use App\Models\Workout;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ExerciseController extends Controller
{
    public function store(StoreExerciseRequest $request, Workout $workout): RedirectResponse
    {
        $this->authorize('update', $workout);

        $workout->exercises()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise added.')]);

        return back();
    }

    public function destroy(Exercise $exercise): RedirectResponse
    {
        $exercise->load('workout');

        $this->authorize('delete', $exercise);

        $exercise->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise removed.')]);

        return back();
    }
}
