<?php

namespace App\Models;

use Database\Factories\ExerciseSetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseSet extends Model
{
    /** @use HasFactory<ExerciseSetFactory> */
    use HasFactory;

    protected $fillable = [
        'reps',
        'weight',
        'completed_at',
    ];

    /**
     * @return BelongsTo<WorkoutSessionExercise, $this>
     */
    public function workoutSessionExercise(): BelongsTo
    {
        return $this->belongsTo(WorkoutSessionExercise::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reps' => 'integer',
            'weight' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }
}
