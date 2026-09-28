<?php

namespace App\Http\Requests;

use App\Enums\ExerciseType;
use App\Models\WorkoutExercise;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkoutExerciseRequest extends FormRequest
{
    /**
     * Duration and distance belong to cardio only. Sending either for a
     * strength exercise is a bug in the client, not a value to store.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $workoutExercise = $this->route('workoutExercise');

        $isCardio = $workoutExercise instanceof WorkoutExercise
            && $workoutExercise->exercise->type === ExerciseType::Cardio;

        return [
            'duration_seconds' => $isCardio
                ? ['required', 'integer', 'min:1']
                : ['prohibited'],
            'distance_miles' => $isCardio
                ? ['nullable', 'numeric', 'between:0,999.99', 'decimal:0,2']
                : ['prohibited'],
        ];
    }
}
