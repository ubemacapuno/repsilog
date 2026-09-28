<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutExercise;

class WorkoutExercisePolicy
{
    public function update(User $user, WorkoutExercise $workoutExercise): bool
    {
        return $user->id === $workoutExercise->workout->user_id;
    }

    public function delete(User $user, WorkoutExercise $workoutExercise): bool
    {
        return $user->id === $workoutExercise->workout->user_id;
    }
}
