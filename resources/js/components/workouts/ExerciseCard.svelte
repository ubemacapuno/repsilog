<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import Dumbbell from '@lucide/svelte/icons/dumbbell';
    import Plus from '@lucide/svelte/icons/plus';
    import Timer from '@lucide/svelte/icons/timer';
    import { untrack } from 'svelte';
    import ExerciseController from '@/actions/App/Http/Controllers/ExerciseController';
    import ExerciseSetController from '@/actions/App/Http/Controllers/ExerciseSetController';
    import { Input } from '@/components/ui/input';
    import SetRow from '@/components/workouts/SetRow.svelte';
    import { formatDuration } from '@/lib/datetime';
    import type { Exercise, ExerciseSet, SetDraft, Workout } from '@/types';

    let { exercise }: { exercise: Exercise } = $props();

    const toDraft = (set: ExerciseSet): SetDraft => ({
        id: set.id,
        reps: String(set.reps),
        weight: set.weight ?? '',
        completed: set.completed_at !== null,
    });

    let sets = $state<SetDraft[]>([]);

    $effect(() => {
        const live = exercise.sets;

        untrack(() => {
            const liveIds = new Set(live.map((set) => set.id));

            for (let index = sets.length - 1; index >= 0; index--) {
                if (!liveIds.has(sets[index].id)) {
                    sets.splice(index, 1);
                }
            }

            const knownIds = new Set(sets.map((set) => set.id));

            for (const set of live) {
                if (!knownIds.has(set.id)) {
                    sets.push(toDraft(set));
                }
            }
        });
    });

    const completed = $derived(sets.filter((set) => set.completed));

    const totalReps = $derived(
        completed.reduce((sum, set) => sum + (Number(set.reps) || 0), 0),
    );

    const totalVolume = $derived(
        completed.reduce(
            (sum, set) => sum + (Number(set.reps) || 0) * (Number(set.weight) || 0),
            0,
        ),
    );

    function withSets(props: { workout: Workout }, sets: ExerciseSet[]) {
        return {
            workout: {
                ...props.workout,
                exercises: (props.workout.exercises ?? []).map((candidate) =>
                    candidate.id === exercise.id
                        ? { ...candidate, sets }
                        : candidate,
                ),
            },
        };
    }

    const visit = { preserveScroll: true, preserveState: true };

    function addSet() {
        const previous = sets[sets.length - 1];

        router
            .optimistic((props: { workout: Workout }) =>
                withSets(props, [
                    ...exercise.sets,
                    {
                        id: -Date.now(),
                        exercise_id: exercise.id,
                        reps: Number(previous?.reps) || 0,
                        weight: previous?.weight || null,
                        completed_at: null,
                    },
                ]),
            )
            .post(ExerciseSetController.store.url(exercise.id), {}, visit);
    }

    function removeSet(id: number) {
        router
            .optimistic((props: { workout: Workout }) =>
                withSets(
                    props,
                    exercise.sets.filter((set) => set.id !== id),
                ),
            )
            .delete(ExerciseSetController.destroy.url(id), visit);
    }

    let editing = $state(false);
    let minutes = $state('');
    let seconds = $state('');
    let distance = $state('');

    function startEditing() {
        const total = exercise.duration_seconds ?? 0;

        minutes = String(Math.floor(total / 60));
        seconds = String(total % 60);
        distance = exercise.distance_miles ?? '';
        editing = true;
    }

    function focusFirstInput(node: HTMLElement) {
        const input = node.querySelector('input');

        input?.focus();
        input?.select();
    }

    function save() {
        editing = false;

        router.patch(
            ExerciseController.update.url(exercise.id),
            {
                duration_seconds:
                    (Number(minutes) || 0) * 60 + (Number(seconds) || 0),
                distance_miles: distance === '' ? null : distance,
            },
            visit,
        );
    }

    function handleKeydown(event: KeyboardEvent) {
        if (event.key === 'Enter') {
            save();
        }

        if (event.key === 'Escape') {
            editing = false;
        }
    }
</script>

<article class="rounded-3xl bg-card p-4 shadow-sm">
    <header class="flex items-start justify-between gap-4 px-2 pb-4">
        <div class="flex items-center gap-3">
            <span
                class="flex size-12 items-center justify-center rounded-2xl bg-foreground text-background"
            >
                <Dumbbell class="size-6" />
            </span>
            <h2 class="text-lg font-semibold leading-tight">{exercise.name}</h2>
        </div>

        {#if exercise.type === 'strength'}
            <dl class="flex gap-6 text-right">
                <div>
                    <dt class="text-sm text-muted-foreground">Volume</dt>
                    <dd class="text-xl font-bold tabular-nums">
                        {totalVolume.toLocaleString()}
                        <span class="text-sm font-normal text-muted-foreground">lbs</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Reps</dt>
                    <dd class="text-xl font-bold tabular-nums">{totalReps}</dd>
                </div>
            </dl>
        {/if}
    </header>

    {#if exercise.type === 'strength'}
        <div class="space-y-2 rounded-2xl bg-muted/50 p-2">
            {#each sets as set, index (set.id)}
                <SetRow {set} {index} onremove={() => removeSet(set.id)} />
            {/each}

            <button
                type="button"
                onclick={addSet}
                class="flex w-full items-center justify-center gap-1 rounded-2xl border-2 border-dashed border-muted-foreground/30 py-3 text-sm font-medium text-muted-foreground transition-colors hover:border-muted-foreground/50"
            >
                <Plus class="size-4" /> Add a set
            </button>
        </div>
    {:else if editing}
        <div
            class="flex items-center gap-2 px-2 pb-2 text-sm"
            use:focusFirstInput
            onfocusout={(event) => {
                if (!event.currentTarget.contains(event.relatedTarget as Node)) {
                    save();
                }
            }}
        >
            <Timer class="size-4 text-muted-foreground" />
            <Input
                type="number"
                min="0"
                aria-label="Minutes"
                bind:value={minutes}
                onkeydown={handleKeydown}
                class="h-8 w-16 text-center tabular-nums"
            />
            <span class="text-muted-foreground">m</span>
            <Input
                type="number"
                min="0"
                max="59"
                aria-label="Seconds"
                bind:value={seconds}
                onkeydown={handleKeydown}
                class="h-8 w-16 text-center tabular-nums"
            />
            <span class="text-muted-foreground">s</span>
            <Input
                type="number"
                step="0.01"
                min="0"
                placeholder="0"
                aria-label="Distance in miles"
                bind:value={distance}
                onkeydown={handleKeydown}
                class="h-8 w-20 text-center tabular-nums"
            />
            <span class="text-muted-foreground">mi</span>
        </div>
    {:else}
        <button
            type="button"
            onclick={startEditing}
            class="flex w-full items-center gap-2 rounded-2xl px-2 pb-2 text-left text-sm transition-colors hover:text-muted-foreground"
        >
            <Timer class="size-4 text-muted-foreground" />
            <span>{formatDuration(exercise.duration_seconds)}</span>
            {#if exercise.distance_miles}
                <span class="text-muted-foreground">·</span>
                <span>{exercise.distance_miles} mi</span>
            {/if}
        </button>
    {/if}
</article>
