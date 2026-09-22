<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkoutRequest;
use App\Models\Exercise;
use App\Models\Workout;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkoutController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('workouts/Index', [
            'workouts' => $request->user()->workouts()
                ->withCount('exercises')
                ->latest('performed_at')
                ->get(),
        ]);
    }

    public function store(WorkoutRequest $request): RedirectResponse
    {
        $workout = $request->user()->workouts()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Workout started.')]);

        return to_route('workouts.show', $workout);
    }

    public function show(Request $request, Workout $workout): Response
    {
        $this->authorize('view', $workout);

        $workout->load('exercises.sets');

        return Inertia::render('workouts/Show', [
            'workout' => $workout,
            'recentExercises' => $this->recentExercises($request),
        ]);
    }

    public function update(WorkoutRequest $request, Workout $workout): RedirectResponse
    {
        $this->authorize('update', $workout);

        $workout->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Workout updated.')]);

        return back();
    }

    public function destroy(Workout $workout): RedirectResponse
    {
        $this->authorize('delete', $workout);

        $workout->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Workout deleted.')]);

        return to_route('workouts.index');
    }

    /**
     * Turns "pick a type, then type a name" into one tap for anything the user repeats.
     *
     * @return Collection<int, Exercise>
     */
    private function recentExercises(Request $request): Collection
    {
        return Exercise::query()
            ->whereRelation('workout', 'user_id', $request->user()->id)
            ->select('name', 'type')
            ->groupBy('name', 'type')
            ->orderByRaw('MAX(id) DESC')
            ->limit(30)
            ->get();
    }
}
