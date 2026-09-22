<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExerciseSetRequest extends FormRequest
{
    /**
     * `min:0` on reps is deliberate. A row sitting at 0 while the user decides what
     * to do is a legitimate save.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reps' => ['required', 'integer', 'min:0', 'max:999'],
            'weight' => ['nullable', 'numeric', 'between:0,9999.99', 'decimal:0,2'],
            'completed_at' => ['nullable', 'date'],
        ];
    }
}
