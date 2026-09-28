<script lang="ts">
    import {router, setLayoutProps} from '@inertiajs/svelte';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import WorkoutSessionController from '@/actions/App/Http/Controllers/WorkoutSessionController';
    import AppHead from '@/components/AppHead.svelte';
    import ConfirmDialog from '@/components/ConfirmDialog.svelte';
    import {Button} from '@/components/ui/button';
    import AddExerciseForm from '@/components/workouts/AddExerciseForm.svelte';
    import ExerciseCard from '@/components/workouts/ExerciseCard.svelte';
    import {formatWorkoutDate, fromDateTimeLocal, toDateTimeLocal,} from '@/lib/datetime';
    import {index, show} from '@/routes/workouts';
    import type {Exercise, WorkoutSession} from '@/types';

    let {
        workout,
        exercises,
    }: {
        workout: WorkoutSession;
        exercises: Exercise[];
    } = $props();

    let confirmingDelete = $state(false);
    let editingTitle = $state(false);
    let editingDate = $state(false);
    let title = $state('');
    let performedAt = $state('');

    function focusInput(node: HTMLInputElement) {
        node.focus();
        node.select();
    }

    function startTitleEdit() {
        title = workout.title ?? '';
        editingTitle = true;
    }

    function startDateEdit() {
        performedAt = toDateTimeLocal(workout.performed_at);
        editingDate = true;
    }

    function save() {
        const trimmed = title.trim();
        const nextTitle = editingTitle
            ? (trimmed === '' ? null : trimmed)
            : (workout.title ?? null);
        const nextDate = editingDate
            ? performedAt
            : toDateTimeLocal(workout.performed_at);

        editingTitle = false;
        editingDate = false;

        if (
            nextTitle === (workout.title ?? null) &&
            nextDate === toDateTimeLocal(workout.performed_at)
        ) {
            return;
        }

        router.patch(
            WorkoutSessionController.update.url(workout.id),
            {title: nextTitle, performed_at: fromDateTimeLocal(nextDate)},
            {preserveScroll: true, preserveState: true},
        );
    }

    function cancel() {
        editingTitle = false;
        editingDate = false;
    }

    function handleKeydown(event: KeyboardEvent) {
        if (event.key === 'Enter') {
            save();
        }

        if (event.key === 'Escape') {
            cancel();
        }
    }

    const breadcrumbs = $derived([
        {title: 'Workouts', href: index()},
        {title: workout.title ?? 'Workout', href: show(workout.id)},
    ]);

    $effect(() => {
        setLayoutProps({breadcrumbs});
    });
</script>

<AppHead title={workout.title ?? 'Workout'}/>

<div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
    <header class="flex items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            {#if editingTitle}
                <input
                    bind:value={title}
                    use:focusInput
                    onblur={save}
                    onkeydown={handleKeydown}
                    aria-label="Workout title"
                    placeholder="Untitled workout"
                    class="w-full border-b border-border bg-transparent pb-1 text-3xl font-bold tracking-tight focus:border-primary focus:outline-none"
                />
            {:else}
                <button
                    type="button"
                    onclick={startTitleEdit}
                    class="block max-w-full truncate text-left text-3xl font-bold tracking-tight"
                >
                    {workout.title ?? 'Untitled workout'}
                </button>
            {/if}

            {#if editingDate}
                <input
                    type="datetime-local"
                    bind:value={performedAt}
                    use:focusInput
                    onblur={save}
                    onkeydown={handleKeydown}
                    aria-label="Performed at"
                    class="mt-1 border-b border-border bg-transparent pb-1 font-mono text-xs tracking-widest uppercase focus:border-primary focus:outline-none"
                />
            {:else}
                <button
                    type="button"
                    onclick={startDateEdit}
                    class="pt-1 font-mono text-xs tracking-widest text-muted-foreground uppercase"
                >
                    {formatWorkoutDate(workout.performed_at)}
                </button>
            {/if}
        </div>

        <Button
            variant="ghost"
            size="sm"
            onclick={() => (confirmingDelete = true)}
            class="text-muted-foreground hover:text-destructive"
        >
            <Trash2 class="size-4"/>
            Delete
        </Button>
    </header>

    <ConfirmDialog
        bind:open={confirmingDelete}
        title="Delete this workout?"
        description="This removes the workout along with every exercise and set it holds. This cannot be undone."
        confirmLabel="Delete workout"
        onconfirm={() =>
            router.delete(WorkoutSessionController.destroy.url(workout.id))}
    />

    <div>
        {#each workout.exercises ?? [] as exercise (exercise.id)}
            <ExerciseCard {exercise}/>
        {:else}
            <p class="border-t border-border py-6 text-sm text-muted-foreground">
                No exercises yet. Add the first one below.
            </p>
        {/each}
    </div>

    <div class="border-t border-border pt-6">
        <h2
            class="pb-4 font-mono text-xs tracking-widest text-muted-foreground uppercase"
        >
            Add an exercise
        </h2>

        <AddExerciseForm workoutId={workout.id} {exercises}/>
    </div>
</div>
