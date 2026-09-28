export type ExerciseType = 'strength' | 'cardio';

export type ExerciseSet = {
    id: number;
    exercise_id: number;
    reps: number;
    weight: string | null;
    completed_at: string | null;
};

export type Exercise = {
    id: number;
    workout_id: number;
    name: string;
    type: ExerciseType;
    duration_seconds: number | null;
    distance_miles: string | null;
    notes: string | null;
    sets: ExerciseSet[];
};

export type Workout = {
    id: number;
    title: string | null;
    performed_at: string;
    notes: string | null;
    exercises?: Exercise[];
    exercises_count?: number;
};

export type SetDraft = {
    id: number;
    reps: string;
    weight: string;
    completed: boolean;
    completedAt: string | null;
};

export type Paginated<T> = {
    data: T[];
    prev_page_url: string | null;
    next_page_url: string | null;
    from: number | null;
    to: number | null;
    total: number;
};
