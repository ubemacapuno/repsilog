<script lang="ts">
    import {router} from '@inertiajs/svelte';
    import {untrack} from 'svelte';
    import WorkoutSessionExerciseController from '@/actions/App/Http/Controllers/WorkoutSessionExerciseController';
    import ExerciseSetController from '@/actions/App/Http/Controllers/ExerciseSetController';
    import ConfirmDialog from '@/components/ConfirmDialog.svelte';
    import SetRow from '@/components/workouts/SetRow.svelte';
    import {formatDuration} from '@/lib/datetime';
    import {formatDecimal} from '@/lib/number';
    import type {ExerciseSet, SetDraft, WorkoutSession, WorkoutSessionExercise} from '@/types';
    import { Plus, X } from '@lucide/svelte';

    let {exercise}: { exercise: WorkoutSessionExercise } = $props();

    const movement = $derived(exercise.exercise);

    const toDraft = (set: ExerciseSet): SetDraft => ({
        id: set.id,
        reps: String(set.reps),
        weight: formatDecimal(set.weight),
        completed: set.completed_at !== null,
        completedAt: set.completed_at,
    });

    let sets = $state<SetDraft[]>([]);
    let focusedSetId = $state<number | null>(null);

    function trackFocus(event: FocusEvent) {
        const target = event.target;
        const row =
            target instanceof HTMLElement
                ? target.closest<HTMLElement>('[data-set-id]')
                : null;

        focusedSetId = row === null ? null : Number(row.dataset.setId);
    }

    $effect(() => {
        const live = exercise.sets;

        untrack(() => {
            const liveIds = new Set(live.map((set) => set.id));

            for (let index = sets.length - 1; index >= 0; index--) {
                if (!liveIds.has(sets[index].id)) {
                    sets.splice(index, 1);
                }
            }

            for (const set of live) {
                const draft = sets.find(
                    (candidate) => candidate.id === set.id,
                );

                if (draft === undefined) {
                    sets.push(toDraft(set));
                } else if (draft.id !== focusedSetId) {
                    Object.assign(draft, toDraft(set));
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

    function withSets(props: { workout: WorkoutSession }, sets: ExerciseSet[]) {
        return {
            workout: {
                ...props.workout,
                exercises: (props.workout.exercises ?? []).map((candidate) =>
                    candidate.id === exercise.id
                        ? {...candidate, sets}
                        : candidate,
                ),
            },
        };
    }

    function setsIn(props: { workout: WorkoutSession }): ExerciseSet[] {
        return (
            (props.workout.exercises ?? []).find(
                (candidate) => candidate.id === exercise.id,
            )?.sets ?? []
        );
    }

    const visit = {preserveScroll: true, preserveState: true};

    function addSet() {
        const previous = sets[sets.length - 1];
        const pending: ExerciseSet = {
            id: -Date.now(),
            workout_session_exercise_id: exercise.id,
            reps: Number(previous?.reps) || 0,
            weight: previous?.weight || null,
            completed_at: null,
        };

        router
            .optimistic((props: { workout: WorkoutSession }) =>
                withSets(props, [...setsIn(props), pending]),
            )
            .post(ExerciseSetController.store.url(exercise.id), {}, visit);
    }

    let confirmingSetRemoval = $state(false);
    let pendingSetId = $state<number | null>(null);

    function confirmSetRemoval(id: number) {
        pendingSetId = id;
        confirmingSetRemoval = true;
    }

    function removeSet() {
        const id = pendingSetId;

        if (id === null || id < 0) {
            return;
        }

        router
            .optimistic((props: { workout: WorkoutSession }) =>
                withSets(
                    props,
                    setsIn(props).filter((set) => set.id !== id),
                ),
            )
            .delete(ExerciseSetController.destroy.url(id), visit);

        pendingSetId = null;
    }

    let confirmingDelete = $state(false);

    function removeExercise() {
        router
            .optimistic((props: { workout: WorkoutSession }) => ({
                workout: {
                    ...props.workout,
                    exercises: (props.workout.exercises ?? []).filter(
                        (candidate) => candidate.id !== exercise.id,
                    ),
                },
            }))
            .delete(WorkoutSessionExerciseController.destroy.url(exercise.id), visit);
    }

    let editing = $state(false);
    let minutes = $state('');
    let seconds = $state('');
    let distance = $state('');

    function startEditing() {
        const total = exercise.duration_seconds ?? 0;

        minutes = String(Math.floor(total / 60));
        seconds = String(total % 60);
        distance = formatDecimal(exercise.distance_miles);
        editing = true;
    }

    function focusFirstInput(node: HTMLElement) {
        const input = node.querySelector('input');

        input?.focus();
        input?.select();
    }

    function save() {
        if (!editing) {
            return;
        }

        editing = false;

        const duration = (Number(minutes) || 0) * 60 + (Number(seconds) || 0);
        const miles = distance === '' ? null : distance;

        if (
            duration === exercise.duration_seconds &&
            (miles ?? '') === formatDecimal(exercise.distance_miles)
        ) {
            return;
        }

        router.patch(
            WorkoutSessionExerciseController.update.url(exercise.id),
            {duration_seconds: duration, distance_miles: miles},
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

<article id="exercise-{exercise.id}" class="scroll-mt-4 border-t border-border py-6">
    <header class="flex items-start justify-between gap-6 pb-2">
        <h2 class="text-base font-bold text-primary">{movement.name}</h2>

        <div class="flex items-start gap-4">
            {#if movement.type === 'strength'}
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
                <X class="size-4"/>
            </button>
        </div>
    </header>

    <ConfirmDialog
        bind:open={confirmingDelete}
        title="Delete {movement.name}?"
        description="This removes the exercise and every set logged under it. This cannot be undone."
        onconfirm={removeExercise}
    />

    {#if movement.type === 'strength'}
        <div onfocusin={trackFocus} onfocusout={() => (focusedSetId = null)}>
            {#each sets as set, index (set.id)}
                <SetRow
                    {set}
                    {index}
                    onchange={(patch) => Object.assign(sets[index], patch)}
                    onremove={() => confirmSetRemoval(set.id)}
                />
            {/each}

            <ConfirmDialog
                bind:open={confirmingSetRemoval}
                title="Delete this set?"
                description="This removes the logged set from {movement.name}. This cannot be undone."
                confirmLabel="Delete set"
                onconfirm={removeSet}
            />

            <button
                type="button"
                onclick={addSet}
                class="flex items-center gap-2 pt-3 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <Plus class="size-4"/>
                Add a set
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
                    {formatDecimal(exercise.distance_miles)}
                    <span class="font-mono text-xs text-muted-foreground"
                    >mi</span
                    >
                </span>
            {/if}
        </button>
    {/if}
</article>
