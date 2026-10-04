<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExerciseController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('exercises/Index', [
            'exercises' => $request->user()->exercises()
                ->withCount('workoutSessionExercises')
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request, Exercise $exercise): Response
    {
        $this->authorize('view', $exercise);

        return Inertia::render('exercises/Show', [
            'exercise' => $exercise->only(['id', 'name', 'type']),
            'workouts' => $request->user()->workoutSessions()
                ->whereHas('exercises', fn (Builder $query) => $query->whereBelongsTo($exercise))
                ->with(['exercises' => fn (HasMany $query) => $query->whereBelongsTo($exercise)->with('sets')])
                ->latest('performed_at')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function update(UpdateExerciseRequest $request, Exercise $exercise): RedirectResponse
    {
        $this->authorize('update', $exercise);

        $exercise->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise renamed.')]);

        return back();
    }

    public function destroy(Exercise $exercise): RedirectResponse
    {
        $this->authorize('delete', $exercise);

        $exercise->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise deleted.')]);

        return back();
    }
}
