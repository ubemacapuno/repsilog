<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateExerciseSetRequest;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ExerciseSetController extends Controller
{
    public function store(Exercise $exercise): JsonResponse
    {
        $exercise->load('workout');

        $this->authorize('update', $exercise);

        $previous = $exercise->sets()->latest('id')->first();

        $set = $exercise->sets()->create([
            'reps' => $previous->reps ?? 0,
            'weight' => $previous->weight ?? null,
            'completed_at' => null,
        ]);

        return response()->json($set, 201);
    }

    public function update(UpdateExerciseSetRequest $request, ExerciseSet $set): JsonResponse
    {
        $set->load('exercise.workout');

        $this->authorize('update', $set);

        $set->update($request->validated());

        return response()->json($set);
    }

    public function destroy(ExerciseSet $set): Response
    {
        $set->load('exercise.workout');

        $this->authorize('delete', $set);

        $set->delete();

        return response()->noContent();
    }
}
