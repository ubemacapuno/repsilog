<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExerciseSetRequest extends FormRequest
{
    /**
     * `min:0` on reps is deliberate. A row sitting at 0 while the user decides what
     * to do is a legitimate save. Completing that row is not, so `completed_at` is
     * prohibited until the set carries at least one rep.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $hasReps = (int) $this->input('reps') >= 1;

        return [
            'reps' => ['required', 'integer', 'min:0', 'max:999'],
            'weight' => ['nullable', 'numeric', 'between:0,9999.99', 'decimal:0,2'],
            'completed_at' => $hasReps
                ? ['nullable', 'date']
                : ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'completed_at.prohibited' => 'A set needs at least one rep before it can be completed.',
        ];
    }
}
