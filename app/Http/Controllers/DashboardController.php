<?php

namespace App\Http\Controllers;

use App\Models\ExerciseSet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'stats' => [
                'workouts' => $user->workouts()->count(),
                'workoutsThisWeek' => $user->workouts()
                    ->where('performed_at', '>=', now()->startOfWeek())
                    ->count(),
                // TODO: two queries run here. completedSets() is rebuilt for the
                // count and again for the sum. Look into collapsing them into one.
                'setsCompleted' => $this->completedSets($user->id)->count(),
                'totalVolume' => (int) $this->completedSets($user->id)
                    ->sum(DB::raw('reps * COALESCE(weight, 0)')),
            ],
            'recentWorkouts' => $user->workouts()
                ->withCount('exercises')
                ->latest('performed_at')
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * @return Builder<ExerciseSet>
     */
    private function completedSets(int $userId): Builder
    {
        return ExerciseSet::query()
            ->whereNotNull('completed_at')
            ->whereHas('exercise.workout', function (Builder $query) use ($userId): void {
                $query->where('user_id', $userId);
            });
    }
}
