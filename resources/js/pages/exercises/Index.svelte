<script module lang="ts">
    import {index} from '@/routes/exercises';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Exercises',
                href: index(),
            },
        ],
    };
</script>

<script lang="ts">
    import {Link, router} from '@inertiajs/svelte';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import ExerciseController from '@/actions/App/Http/Controllers/ExerciseController';
    import AppHead from '@/components/AppHead.svelte';
    import ConfirmDialog from '@/components/ConfirmDialog.svelte';
    import {Button} from '@/components/ui/button';
    import type {Exercise, Paginated} from '@/types';

    let {exercises}: { exercises: Paginated<Exercise> } = $props();

    let editingId = $state<number | null>(null);
    let name = $state('');
    let confirming = $state(false);
    let target = $state<Exercise | null>(null);

    function focusInput(node: HTMLInputElement) {
        node.focus();
        node.select();
    }

    function startEditing(exercise: Exercise) {
        name = exercise.name;
        editingId = exercise.id;
    }

    function save(exercise: Exercise) {
        if (editingId !== exercise.id) {
            return;
        }

        editingId = null;

        const trimmed = name.trim();

        if (trimmed === '' || trimmed === exercise.name) {
            return;
        }

        router.patch(
            ExerciseController.update.url(exercise.id),
            {name: trimmed},
            {preserveScroll: true, preserveState: true},
        );
    }

    function handleKeydown(event: KeyboardEvent, exercise: Exercise) {
        if (event.key === 'Enter') {
            save(exercise);
        }

        if (event.key === 'Escape') {
            editingId = null;
        }
    }

    function askDelete(exercise: Exercise) {
        target = exercise;
        confirming = true;
    }

    function confirmDelete() {
        if (target === null) {
            return;
        }

        router.delete(ExerciseController.destroy.url(target.id), {
            preserveScroll: true,
        });
    }
</script>

<AppHead title="Exercises"/>

<div class="mx-auto flex w-full max-w-2xl flex-col gap-4 p-4">
    <div>
        <h1 class="text-2xl font-bold">Exercises</h1>
        <p class="pt-1 text-sm text-muted-foreground">
            Every movement you have logged. Renaming one updates it everywhere
            it appears. Only exercises no workout uses can be deleted.
        </p>
    </div>

    <table class="w-full text-sm">
        <thead>
        <tr
            class="border-b border-border font-mono text-xs tracking-widest text-muted-foreground uppercase"
        >
            <th class="py-2 text-left font-normal">Name</th>
            <th class="py-2 text-left font-normal">Type</th>
            <th class="py-2 text-right font-normal">Workouts</th>
            <th class="w-10"></th>
        </tr>
        </thead>

        <tbody>
        {#each exercises.data as exercise (exercise.id)}
            {@const used = exercise.workout_session_exercises_count ?? 0}

            <tr class="border-b border-border">
                <td class="py-2 pr-4">
                    {#if editingId === exercise.id}
                        <input
                            bind:value={name}
                            use:focusInput
                            onblur={() => save(exercise)}
                            onkeydown={(event) =>
                                    handleKeydown(event, exercise)}
                            aria-label="Exercise name"
                            class="w-full border-b border-border bg-transparent pb-1 font-semibold focus:border-primary focus:outline-none"
                        />
                    {:else}
                        <button
                            type="button"
                            onclick={() => startEditing(exercise)}
                            class="max-w-full truncate text-left font-semibold"
                        >
                            {exercise.name}
                        </button>
                    {/if}
                </td>

                <td class="py-2 pr-4">
                        <span
                            class="font-mono text-xs tracking-widest text-muted-foreground uppercase"
                        >
                            {exercise.type}
                        </span>
                </td>

                <td
                    class="py-2 pr-4 text-right font-mono tabular-nums text-muted-foreground"
                >
                    {used}
                </td>

                <td class="py-2 text-right">
                    {#if used <= 0}
                        <button
                            type="button"
                            onclick={() => askDelete(exercise)}
                            aria-label="Delete {exercise.name}"
                            title={used > 0
                                ? 'Used in a workout, so it cannot be deleted'
                                : 'Delete'}
                            class="text-muted-foreground transition-colors hover:text-destructive disabled:pointer-events-none disabled:opacity-40"
                        >
                            <Trash2 class="size-4"/>
                        </button>
                    {/if}
                </td>
            </tr>
        {:else}
            <tr>
                <td colspan="4" class="py-6 text-muted-foreground">
                    No exercises yet. They appear here once you add one to a
                    workout.
                </td>
            </tr>
        {/each}
        </tbody>
    </table>

    <ConfirmDialog
        bind:open={confirming}
        title="Delete {target?.name}?"
        description="No workout uses this exercise, so nothing you have logged is affected."
        onconfirm={confirmDelete}
    />

    {#if exercises.prev_page_url || exercises.next_page_url}
        <div class="flex items-center justify-between pt-2">
            <span class="font-mono text-xs text-muted-foreground">
                {exercises.from}&ndash;{exercises.to} of {exercises.total}
            </span>

            <div class="flex gap-2">
                <Button
                    asChild={Boolean(exercises.prev_page_url)}
                    variant="outline"
                    size="sm"
                    disabled={!exercises.prev_page_url}
                >
                    {#snippet children(props)}
                        {#if exercises.prev_page_url}
                            <Link {...props} href={exercises.prev_page_url}>
                                Previous
                            </Link>
                        {:else}
                            Previous
                        {/if}
                    {/snippet}
                </Button>

                <Button
                    asChild={Boolean(exercises.next_page_url)}
                    variant="outline"
                    size="sm"
                    disabled={!exercises.next_page_url}
                >
                    {#snippet children(props)}
                        {#if exercises.next_page_url}
                            <Link {...props} href={exercises.next_page_url}>
                                Next
                            </Link>
                        {:else}
                            Next
                        {/if}
                    {/snippet}
                </Button>
            </div>
        </div>
    {/if}
</div>
