<script lang="ts">
    import { Form, setLayoutProps } from '@inertiajs/svelte';
    import WorkoutController from '@/actions/App/Http/Controllers/WorkoutController';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import AddExerciseForm from '@/components/workouts/AddExerciseForm.svelte';
    import ExerciseCard from '@/components/workouts/ExerciseCard.svelte';
    import { toDateTimeLocal } from '@/lib/datetime';
    import { index, show } from '@/routes/workouts';
    import type { ExerciseType, Workout } from '@/types';

    let {
        workout,
        recentExercises,
    }: {
        workout: Workout;
        recentExercises: { name: string; type: ExerciseType }[];
    } = $props();

    const breadcrumbs = $derived([
        { title: 'Workouts', href: index() },
        { title: workout.title ?? 'Workout', href: show(workout.id) },
    ]);

    $effect(() => {
        setLayoutProps({ breadcrumbs });
    });
</script>

<AppHead title={workout.title ?? 'Workout'} />

<div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
    <Form
        {...WorkoutController.update.form(workout.id)}
        class="grid gap-4"
        options={{ preserveScroll: true }}
    >
        {#snippet children({ errors, processing })}
            <div class="grid gap-2">
                <Label for="title">Title</Label>
                <Input
                    id="title"
                    name="title"
                    value={workout.title ?? ''}
                    placeholder="Push day"
                />
                <InputError message={errors.title} />
            </div>

            <div class="grid gap-2">
                <Label for="performed_at">Performed at</Label>
                <Input
                    id="performed_at"
                    name="performed_at"
                    type="datetime-local"
                    value={toDateTimeLocal(workout.performed_at)}
                    required
                />
                <InputError message={errors.performed_at} />
            </div>

            <div class="flex justify-end">
                <Button type="submit" variant="secondary" disabled={processing}>
                    Save workout
                </Button>
            </div>
        {/snippet}
    </Form>

    {#each workout.exercises ?? [] as exercise (exercise.id)}
        <ExerciseCard {exercise} />
    {:else}
        <p class="text-sm text-muted-foreground">
            No exercises yet. Add the first one below.
        </p>
    {/each}

    <div class="rounded-3xl bg-card p-4 shadow-sm">
        <h2 class="pb-4 text-lg font-semibold">Add an exercise</h2>

        <AddExerciseForm workoutId={workout.id} {recentExercises} />
    </div>

    <Form {...WorkoutController.destroy.form(workout.id)} class="flex justify-end">
        {#snippet children({ processing })}
            <Button type="submit" variant="destructive" disabled={processing}>
                Delete workout
            </Button>
        {/snippet}
    </Form>
</div>
