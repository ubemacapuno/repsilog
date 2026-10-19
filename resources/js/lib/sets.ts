import { router } from '@inertiajs/svelte';
import ExerciseSetController from '@/actions/App/Http/Controllers/ExerciseSetController';
import type { SetDraft } from '@/types';

/**
 * A draft still holding its optimistic negative id has no row on the server to
 * patch, so the write is dropped here. Those edits are not lost: ExerciseCard
 * carries them onto the saved set once the server hands back a real id.
 */
export function persistSet(draft: SetDraft): void {
    if (draft.id < 0) {
        return;
    }

    router.patch(
        ExerciseSetController.update.url(draft.id),
        {
            reps: Number(draft.reps) || 0,
            weight: draft.weight === '' ? null : draft.weight,
            completed_at: draft.completedAt,
        },
        { preserveScroll: true, preserveState: true },
    );
}
