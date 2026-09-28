<?php

namespace App\Http\Requests;

use App\Models\Exercise;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExerciseRequest extends FormRequest
{
    /**
     * Only the name is editable. Changing the type would rewrite history for
     * every workout already referencing this exercise.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $exercise = $this->route('exercise');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('exercises')
                    ->where('user_id', $this->user()->id)
                    ->ignore($exercise instanceof Exercise ? $exercise->id : null),
            ],
        ];
    }
}
