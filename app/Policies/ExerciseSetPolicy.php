<?php

namespace App\Policies;

use App\Models\ExerciseSet;
use App\Models\User;

class ExerciseSetPolicy
{
    /**
     * Eager-load `workoutExercise.workout` before authorizing, since this runs
     * on every debounced keystroke.
     */
    public function update(User $user, ExerciseSet $set): bool
    {
        return $user->id === $set->workoutExercise->workout->user_id;
    }

    public function delete(User $user, ExerciseSet $set): bool
    {
        return $user->id === $set->workoutExercise->workout->user_id;
    }
}
