<?php

namespace App\Http\Requests;

use App\Enums\ExerciseType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkoutExerciseRequest extends FormRequest
{
    /**
     * Sets are not submitted here. They are added one at a time afterwards.
     * `type` only applies when the name is new to the catalog, because an
     * existing exercise keeps the type it was created with.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(ExerciseType::class)],

            'duration_seconds' => ['nullable', 'required_if:type,cardio', 'integer', 'min:1'],
            'distance_miles' => ['nullable', 'numeric', 'between:0,999.99', 'decimal:0,2'],
        ];
    }
}
