<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import X from '@lucide/svelte/icons/x';
    import ExerciseSetController from '@/actions/App/Http/Controllers/ExerciseSetController';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import type { SetDraft } from '@/types';

    let {
        set = $bindable(),
        index,
        onremove,
    }: {
        set: SetDraft;
        index: number;
        onremove: () => void;
    } = $props();

    function save() {
        if (set.id < 0) {
            return;
        }

        router.patch(
            ExerciseSetController.update.url(set.id),
            {
                reps: Number(set.reps) || 0,
                weight: set.weight === '' ? null : set.weight,
                completed_at: set.completed ? new Date().toISOString() : null,
            },
            { preserveScroll: true, preserveState: true },
        );
    }

    function toggle() {
        set.completed = !set.completed;
        save();
    }
</script>

<div class="flex items-center gap-2 rounded-xl bg-background p-2">
    <span class="w-6 text-center text-sm font-medium text-muted-foreground">
        {index + 1}
    </span>

    <Input
        type="number"
        min="0"
        aria-label="Reps"
        bind:value={set.reps}
        onblur={save}
        class="h-8 flex-1 text-center tabular-nums"
    />

    <Input
        type="number"
        step="0.01"
        min="0"
        placeholder="lbs"
        aria-label="Weight"
        bind:value={set.weight}
        onblur={save}
        class="h-8 flex-1 text-center tabular-nums"
    />

    <Checkbox checked={set.completed} onclick={toggle} aria-label="Completed" />

    <button
        type="button"
        onclick={onremove}
        aria-label="Remove set"
        class="text-muted-foreground transition-colors hover:text-destructive"
    >
        <X class="size-4" />
    </button>
</div>
