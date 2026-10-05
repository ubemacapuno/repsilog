<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkoutSessionRequest;
use App\Models\WorkoutSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkoutSessionController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('workouts/Index', [
            'workouts' => $request->user()->workoutSessions()
                ->withCount('exercises')
                ->latest('performed_at')
                ->latest('id')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function store(WorkoutSessionRequest $request): RedirectResponse
    {
        $workoutSession = $request->user()->workoutSessions()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Workout started.')]);

        return to_route('workouts.show', $workoutSession);
    }

    public function show(Request $request, WorkoutSession $workoutSession): Response
    {
        $this->authorize('view', $workoutSession);

        $workoutSession->load(['exercises.exercise', 'exercises.sets']);

        return Inertia::render('workouts/Show', [
            'workout' => $workoutSession,
            'exercises' => $request->user()->exercises()
                ->orderBy('name')
                ->get(['id', 'name', 'type']),
        ]);
    }

    public function update(WorkoutSessionRequest $request, WorkoutSession $workoutSession): RedirectResponse
    {
        $this->authorize('update', $workoutSession);

        $workoutSession->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Workout updated.')]);

        return back();
    }

    public function destroy(WorkoutSession $workoutSession): RedirectResponse
    {
        $this->authorize('delete', $workoutSession);

        $workoutSession->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Workout deleted.')]);

        return to_route('workouts.index');
    }
}
