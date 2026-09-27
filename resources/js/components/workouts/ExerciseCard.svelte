<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import Plus from '@lucide/svelte/icons/plus';
    import X from '@lucide/svelte/icons/x';
    import { untrack } from 'svelte';
    import ExerciseController from '@/actions/App/Http/Controllers/ExerciseController';
    import ExerciseSetController from '@/actions/App/Http/Controllers/ExerciseSetController';
    import ConfirmDialog from '@/components/ConfirmDialog.svelte';
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

    const volumeLabel = $derived(
        totalVolume === 0 ? 'BW' : totalVolume.toLocaleString(),
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

    let confirmingDelete = $state(false);

    function removeExercise() {
        router
            .optimistic((props: { workout: Workout }) => ({
                workout: {
                    ...props.workout,
                    exercises: (props.workout.exercises ?? []).filter(
                        (candidate) => candidate.id !== exercise.id,
                    ),
                },
            }))
            .delete(ExerciseController.destroy.url(exercise.id), visit);
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

        const duration = (Number(minutes) || 0) * 60 + (Number(seconds) || 0);
        const miles = distance === '' ? null : distance;

        if (
            duration === exercise.duration_seconds &&
            miles === exercise.distance_miles
        ) {
            return;
        }

        router.patch(
            ExerciseController.update.url(exercise.id),
            { duration_seconds: duration, distance_miles: miles },
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

<article class="border-t border-border py-6">
    <header class="flex items-start justify-between gap-6 pb-2">
        <h2 class="text-base font-semibold">{exercise.name}</h2>

        <div class="flex items-start gap-4">
            {#if exercise.type === 'strength'}
                <dl class="flex gap-6 text-right">
                    <div>
                        <dt
                            class="font-mono text-xs tracking-widest text-muted-foreground uppercase"
                        >
                            Volume
                        </dt>
                        <dd
                            class="font-mono text-base font-semibold tabular-nums"
                        >
                            {volumeLabel}
                        </dd>
                    </div>
                    <div>
                        <dt
                            class="font-mono text-xs tracking-widest text-muted-foreground uppercase"
                        >
                            Reps
                        </dt>
                        <dd
                            class="font-mono text-base font-semibold tabular-nums"
                        >
                            {totalReps}
                        </dd>
                    </div>
                </dl>
            {:else}
                <span
                    class="font-mono text-xs tracking-widest text-muted-foreground uppercase"
                >
                    Cardio
                </span>
            {/if}

            <button
                type="button"
                onclick={() => (confirmingDelete = true)}
                aria-label="Remove exercise"
                class="text-muted-foreground transition-colors hover:text-destructive"
            >
                <X class="size-4" />
            </button>
        </div>
    </header>

    <ConfirmDialog
        bind:open={confirmingDelete}
        title="Delete {exercise.name}?"
        description="This removes the exercise and every set logged under it. This cannot be undone."
        onconfirm={removeExercise}
    />

    {#if exercise.type === 'strength'}
        <div>
            {#each sets as set, index (set.id)}
                <SetRow
                    {set}
                    {index}
                    onchange={(patch) => Object.assign(sets[index], patch)}
                    onremove={() => removeSet(set.id)}
                />
            {/each}

            <button
                type="button"
                onclick={addSet}
                class="flex items-center gap-2 pt-3 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <Plus class="size-4" /> Add a set
            </button>
        </div>
    {:else if editing}
        <div
            class="flex items-baseline gap-2 pt-2"
            use:focusFirstInput
            onfocusout={(event) => {
                if (!event.currentTarget.contains(event.relatedTarget as Node)) {
                    save();
                }
            }}
        >
            <input
                type="text"
                inputmode="numeric"
                aria-label="Minutes"
                bind:value={minutes}
                onkeydown={handleKeydown}
                class="w-20 border-b border-border bg-transparent pb-1 text-right font-mono text-2xl font-semibold tabular-nums focus:border-primary focus:outline-none"
            />
            <span class="font-mono text-2xl font-semibold text-muted-foreground"
                >:</span
            >
            <input
                type="text"
                inputmode="numeric"
                aria-label="Seconds"
                bind:value={seconds}
                onkeydown={handleKeydown}
                class="w-20 border-b border-border bg-transparent pb-1 text-left font-mono text-2xl font-semibold tabular-nums focus:border-primary focus:outline-none"
            />
            <input
                type="text"
                inputmode="decimal"
                placeholder="&mdash;"
                aria-label="Distance in miles"
                bind:value={distance}
                onkeydown={handleKeydown}
                class="ml-6 w-28 border-b border-border bg-transparent pb-1 text-right font-mono text-2xl font-semibold tabular-nums focus:border-primary focus:outline-none"
            />
            <span class="font-mono text-xs text-muted-foreground">mi</span>
        </div>
    {:else}
        <button
            type="button"
            onclick={startEditing}
            class="flex items-baseline gap-6 pt-2 text-left"
        >
            <span class="font-mono text-2xl font-semibold tabular-nums">
                {formatDuration(exercise.duration_seconds)}
            </span>
            {#if exercise.distance_miles}
                <span class="font-mono text-2xl font-semibold tabular-nums">
                    {exercise.distance_miles}
                    <span class="font-mono text-xs text-muted-foreground"
                        >mi</span
                    >
                </span>
            {/if}
        </button>
    {/if}
</article>
