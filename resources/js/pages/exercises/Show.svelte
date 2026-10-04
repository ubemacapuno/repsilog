<script lang="ts">
    import {Link, setLayoutProps} from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import {Button} from '@/components/ui/button';
    import {formatDuration, formatWorkoutDate} from '@/lib/datetime';
    import {index, show} from '@/routes/exercises';
    import {show as showWorkout} from '@/routes/workouts';
    import type {
        Exercise,
        Paginated,
        WorkoutSession,
        WorkoutSessionExercise,
    } from '@/types';

    let {
        exercise,
        workouts,
    }: {
        exercise: Exercise;
        workouts: Paginated<WorkoutSession>;
    } = $props();

    function entryHref(workout: WorkoutSession, entry: WorkoutSessionExercise) {
        return `${showWorkout.url(workout.id)}#exercise-${entry.id}`;
    }

    function summarize(entry: WorkoutSessionExercise): string {
        if (exercise.type === 'cardio') {
            const distance = Number(entry.distance_miles ?? 0);
            const parts = [
                distance > 0 ? `${distance} mi` : '',
                entry.duration_seconds
                    ? formatDuration(entry.duration_seconds)
                    : '',
            ].filter(Boolean);

            return parts.length > 0 ? parts.join(' / ') : 'logged';
        }

        const count = entry.sets.length;

        return `${count} ${count === 1 ? 'set' : 'sets'}`;
    }

    const breadcrumbs = $derived([
        {title: 'Exercises', href: index()},
        {title: exercise.name, href: show(exercise.id)},
    ]);

    $effect(() => {
        setLayoutProps({breadcrumbs});
    });
</script>

<AppHead title={exercise.name}/>

<div class="mx-auto flex w-full max-w-2xl flex-col gap-4 p-4">
    <div>
        <h1 class="text-2xl font-bold">{exercise.name}</h1>
        <p
            class="pt-1 font-mono text-xs tracking-widest text-muted-foreground uppercase"
        >
            {exercise.type} / {workouts.total}
            {workouts.total === 1 ? 'workout' : 'workouts'}
        </p>
    </div>

    {#each workouts.data as workout (workout.id)}
        {#each workout.exercises ?? [] as entry (entry.id)}
            <Link
                href={entryHref(workout, entry)}
                class="flex items-center justify-between border-t border-border px-2 py-3 transition-colors hover:bg-accent"
            >
                <div>
                    <p class="font-semibold">
                        {workout.title ?? 'Untitled workout'}
                    </p>
                    <p
                        class="font-mono text-xs tracking-widest text-muted-foreground uppercase"
                    >
                        {formatWorkoutDate(workout.performed_at)}
                    </p>
                </div>
                <span class="font-mono text-xs text-muted-foreground">
                    {summarize(entry)}
                </span>
            </Link>
        {/each}
    {:else}
        <p class="border-t border-border py-6 text-sm text-muted-foreground">
            No workouts use this exercise yet.
        </p>
    {/each}

    {#if workouts.prev_page_url || workouts.next_page_url}
        <div
            class="flex items-center justify-between border-t border-border pt-4"
        >
            <span class="font-mono text-xs text-muted-foreground">
                {workouts.from}&ndash;{workouts.to} of {workouts.total}
            </span>

            <div class="flex gap-2">
                <Button
                    asChild={Boolean(workouts.prev_page_url)}
                    variant="outline"
                    size="sm"
                    disabled={!workouts.prev_page_url}
                >
                    {#snippet children(props)}
                        {#if workouts.prev_page_url}
                            <Link {...props} href={workouts.prev_page_url}>
                                Newer
                            </Link>
                        {:else}
                            Newer
                        {/if}
                    {/snippet}
                </Button>

                <Button
                    asChild={Boolean(workouts.next_page_url)}
                    variant="outline"
                    size="sm"
                    disabled={!workouts.next_page_url}
                >
                    {#snippet children(props)}
                        {#if workouts.next_page_url}
                            <Link {...props} href={workouts.next_page_url}>
                                Older
                            </Link>
                        {:else}
                            Older
                        {/if}
                    {/snippet}
                </Button>
            </div>
        </div>
    {/if}
</div>
