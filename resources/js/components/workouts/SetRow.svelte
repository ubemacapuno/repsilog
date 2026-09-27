<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import Check from '@lucide/svelte/icons/check';
    import X from '@lucide/svelte/icons/x';
    import ExerciseSetController from '@/actions/App/Http/Controllers/ExerciseSetController';
    import type { SetDraft } from '@/types';

    let {
        set,
        index,
        onchange,
        onremove,
    }: {
        set: SetDraft;
        index: number;
        onchange: (patch: Partial<SetDraft>) => void;
        onremove: () => void;
    } = $props();

    let before = $state('');

    const signature = (draft: SetDraft) =>
        `${draft.reps}|${draft.weight}|${draft.completed}`;

    function saveIfChanged() {
        if (signature(set) !== before) {
            persist(set);
        }
    }

    function persist(draft: SetDraft) {
        if (draft.id < 0) {
            return;
        }

        router.patch(
            ExerciseSetController.update.url(draft.id),
            {
                reps: Number(draft.reps) || 0,
                weight: draft.weight === '' ? null : draft.weight,
                completed_at: draft.completed ? new Date().toISOString() : null,
            },
            { preserveScroll: true, preserveState: true },
        );
    }

    function toggle() {
        const draft = { ...set, completed: !set.completed };

        onchange({ completed: draft.completed });
        persist(draft);
    }
</script>

<div class="flex items-center gap-3 py-2">
    <span class="w-4 font-mono text-xs text-muted-foreground">{index + 1}</span>

    <input
        type="text"
        inputmode="numeric"
        aria-label="Reps"
        value={set.reps}
        oninput={(event) => onchange({ reps: event.currentTarget.value })}
        onfocus={() => (before = signature(set))}
        onblur={saveIfChanged}
        class="w-16 border-b border-border bg-transparent pb-1 text-right font-mono text-2xl font-semibold tabular-nums focus:border-primary focus:outline-none"
    />

    <span class="font-mono text-sm text-muted-foreground">&times;</span>

    <input
        type="text"
        inputmode="decimal"
        placeholder="&mdash;"
        aria-label="Weight"
        value={set.weight}
        oninput={(event) => onchange({ weight: event.currentTarget.value })}
        onfocus={() => (before = signature(set))}
        onblur={saveIfChanged}
        class="w-24 border-b border-border bg-transparent pb-1 text-right font-mono text-2xl font-semibold tabular-nums focus:border-primary focus:outline-none"
    />

    <span class="font-mono text-xs text-muted-foreground">lbs</span>

    <button
        type="button"
        onclick={onremove}
        aria-label="Remove set"
        class="ml-auto text-muted-foreground transition-colors hover:text-destructive"
    >
        <X class="size-4" />
    </button>

    <button
        type="button"
        onclick={toggle}
        aria-pressed={set.completed}
        aria-label="Completed"
        class="flex size-9 shrink-0 items-center justify-center rounded-full border transition-colors {set.completed
            ? 'border-primary bg-primary text-white'
            : 'border-muted-foreground/40 text-transparent hover:border-muted-foreground'}"
    >
        <Check class="size-5" />
    </button>
</div>
