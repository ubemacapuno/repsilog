<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
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

    public function update(UpdateExerciseRequest $request, Exercise $exercise): RedirectResponse
    {
        $exercise->load('workout');

        $this->authorize('update', $exercise);

        $exercise->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise updated.')]);

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
