<?php

namespace App\Models;

use Database\Factories\WorkoutSessionExerciseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One exercise as it was performed in one session. It joins a workout session
 * to a catalog exercise, and owns the sets logged against it.
 */
class WorkoutSessionExercise extends Model
{
    /** @use HasFactory<WorkoutSessionExerciseFactory> */
    use HasFactory;

    protected $fillable = [
        'exercise_id',
        'duration_seconds',
        'distance_miles',
        'notes',
    ];

    /**
     * @return BelongsTo<WorkoutSession, $this>
     */
    public function workoutSession(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class);
    }

    /**
     * @return BelongsTo<Exercise, $this>
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    /**
     * @return HasMany<ExerciseSet, $this>
     */
    public function sets(): HasMany
    {
        return $this->hasMany(ExerciseSet::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'distance_miles' => 'decimal:2',
        ];
    }
}
