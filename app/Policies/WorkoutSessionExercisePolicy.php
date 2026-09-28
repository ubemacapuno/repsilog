<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutSessionExercise;

class WorkoutSessionExercisePolicy
{
    public function update(User $user, WorkoutSessionExercise $workoutSessionExercise): bool
    {
        return $user->id === $workoutSessionExercise->workoutSession->user_id;
    }

    public function delete(User $user, WorkoutSessionExercise $workoutSessionExercise): bool
    {
        return $user->id === $workoutSessionExercise->workoutSession->user_id;
    }
}
