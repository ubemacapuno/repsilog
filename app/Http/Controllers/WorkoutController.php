<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkoutRequest;
use App\Models\Workout;
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
                ->latest('id')
                ->paginate(10)
                ->withQueryString(),
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

        $workout->load(['exercises.exercise', 'exercises.sets']);

        return Inertia::render('workouts/Show', [
            'workout' => $workout,
            'exercises' => $request->user()->exercises()
                ->orderBy('name')
                ->get(['id', 'name', 'type']),
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
}
