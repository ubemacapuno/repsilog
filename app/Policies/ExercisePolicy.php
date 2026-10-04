<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;

class ExercisePolicy
{
    public function view(User $user, Exercise $exercise): bool
    {
        return $user->id === $exercise->user_id;
    }

    public function update(User $user, Exercise $exercise): bool
    {
        return $user->id === $exercise->user_id;
    }

    /**
     * Deleting an exercise that a workout still references would take those
     * logged sets with it, so the catalog only lets go of unused entries.
     */
    public function delete(User $user, Exercise $exercise): bool
    {
        return $user->id === $exercise->user_id
            && $exercise->workoutSessionExercises()->doesntExist();
    }
}
