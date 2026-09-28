<?php

namespace App\Http\Controllers;

use App\Models\ExerciseSet;
use Carbon\CarbonInterface;
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
                'workouts' => $user->workoutSessions()->count(),
                'workoutsThisWeek' => $user->workoutSessions()
                    ->whereBetween('performed_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                // TODO: two queries run here. completedSets() is rebuilt for the
                // lifetime count and again for the 7-day sum. Look into collapsing them.
                'setsCompleted' => $this->completedSets($user->id)->count(),
                'volumeLast7Days' => (int) round((float) $this->completedSets(
                    $user->id,
                    now()->subDays(7),
                )->sum(DB::raw('reps * COALESCE(weight, 0)'))),
            ],
            'recentWorkouts' => $user->workoutSessions()
                ->withCount('exercises')
                ->latest('performed_at')
                ->latest('id')
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * @return Builder<ExerciseSet>
     */
    private function completedSets(int $userId, ?CarbonInterface $since = null): Builder
    {
        return ExerciseSet::query()
            ->whereNotNull('completed_at')
            ->whereHas('workoutSessionExercise.workoutSession', function (Builder $query) use ($userId, $since): void {
                $query->where('user_id', $userId)
                    ->when($since, fn (Builder $query) => $query->where('performed_at', '>=', $since));
            });
    }
}
