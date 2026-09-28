<script module lang="ts">
    import { dashboard } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { formatWorkoutDate } from '@/lib/datetime';
    import { show } from '@/routes/workouts';
    import type { Workout } from '@/types';

    let {
        stats,
        recentWorkouts,
    }: {
        stats: {
            workouts: number;
            workoutsThisWeek: number;
            setsCompleted: number;
            volumeLast7Days: number;
        };
        recentWorkouts: Workout[];
    } = $props();

    const tiles: { label: string; value: string; unit?: string }[] = $derived([
        { label: 'Workouts', value: stats.workouts.toLocaleString() },
        { label: 'This week', value: stats.workoutsThisWeek.toLocaleString() },
        { label: 'Sets done', value: stats.setsCompleted.toLocaleString() },
        {
            label: 'Volume (7d)',
            value: stats.volumeLast7Days.toLocaleString(),
            unit: 'lbs',
        },
    ]);
</script>

<AppHead title="Dashboard" />

<div class="mx-auto flex w-full max-w-2xl flex-col gap-8 p-4">
    <div class="grid grid-cols-2 gap-px overflow-hidden rounded-lg bg-border">
        {#each tiles as tile (tile.label)}
            <div class="bg-background p-4">
                <p
                    class="font-mono text-xs tracking-widest text-muted-foreground uppercase"
                >
                    {tile.label}
                </p>
                <p class="pt-1 font-mono text-2xl font-semibold tabular-nums">
                    {tile.value}{#if tile.unit}<span
                            class="pl-1 text-sm font-normal text-muted-foreground"
                            >{tile.unit}</span
                        >{/if}
                </p>
            </div>
        {/each}
    </div>

    <div>
        <h2
            class="pb-2 font-mono text-xs tracking-widest text-muted-foreground uppercase"
        >
            Recent workouts
        </h2>

        {#each recentWorkouts as workout (workout.id)}
            <Link
                href={show(workout.id)}
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
                    {workout.exercises_count} ex
                </span>
            </Link>
        {:else}
            <p class="border-t border-border py-6 text-sm text-muted-foreground">
                Nothing logged yet. Start a workout to see it here.
            </p>
        {/each}
    </div>
</div>
