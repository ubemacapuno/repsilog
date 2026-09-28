<?php

namespace App\Models;

use App\Enums\ExerciseType;
use Database\Factories\ExerciseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property ExerciseType $type
 */
class Exercise extends Model
{
    /** @use HasFactory<ExerciseFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'duration_seconds',
        'distance_miles',
        'notes',
    ];

    protected $attributes = [
        'type' => 'strength',
    ];

    /**
     * @return BelongsTo<Workout, $this>
     */
    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
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
            'type' => ExerciseType::class,
            'distance_miles' => 'decimal:2',
        ];
    }
}
