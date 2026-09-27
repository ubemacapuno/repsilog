<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExerciseRequest extends FormRequest
{
    /**
     * Only the cardio measurements are editable. Name and type are fixed once added.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'duration_seconds' => ['required', 'integer', 'min:1'],
            'distance_miles' => ['nullable', 'numeric', 'between:0,999.99', 'decimal:0,2'],
        ];
    }
}
