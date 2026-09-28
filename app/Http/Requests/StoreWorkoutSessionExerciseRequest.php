<?php

namespace App\Http\Requests;

use App\Enums\ExerciseType;
use App\Models\Exercise;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkoutSessionExerciseRequest extends FormRequest
{
    /**
     * Sets are not submitted here. They are added one at a time afterwards.
     * Duration and distance are validated against the type the exercise will
     * actually have, which is the catalog's whenever the name already exists.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isCardio = $this->resolvedType() === ExerciseType::Cardio;

        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(ExerciseType::class)],

            'duration_seconds' => $isCardio
                ? ['required', 'integer', 'min:1']
                : ['prohibited'],
            'distance_miles' => $isCardio
                ? ['nullable', 'numeric', 'between:0,999.99', 'decimal:0,2']
                : ['prohibited'],
        ];
    }

    /**
     * A name already in the catalog keeps the type it was created with, so the
     * submitted type decides anything only when the name is new.
     */
    public function resolvedType(): ?ExerciseType
    {
        $existing = $this->existingExercise();

        if ($existing instanceof Exercise) {
            return $existing->type;
        }

        $submitted = $this->input('type');

        return is_string($submitted) ? ExerciseType::tryFrom($submitted) : null;
    }

    public function existingExercise(): ?Exercise
    {
        $name = $this->input('name');

        if (is_string($name) === false || $name === '') {
            return null;
        }

        return $this->user()->exercises()->firstWhere('name', $name);
    }
}
