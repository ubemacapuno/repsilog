<?php

namespace App\Models;

use Database\Factories\WorkoutSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutSession extends Model
{
    /** @use HasFactory<WorkoutSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'performed_at',
        'title',
        'notes',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The exercises as they were performed in this workout, each holding its
     * own sets. The reusable names live on Exercise.
     *
     * @return HasMany<WorkoutSessionExercise, $this>
     */
    public function exercises(): HasMany
    {
        return $this->hasMany(WorkoutSessionExercise::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
        ];
    }
}
