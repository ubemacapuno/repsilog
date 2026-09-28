<?php

namespace App\Http\Requests;

use App\Enums\ExerciseType;
use App\Models\Exercise;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExerciseRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $exercise = $this->route('exercise');

        $isCardio = $exercise instanceof Exercise
            && $exercise->type === ExerciseType::Cardio;

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
