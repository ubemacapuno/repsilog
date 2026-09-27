<script module lang="ts">
    import { index } from '@/routes/workouts';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Workouts',
                href: index(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Form, Link } from '@inertiajs/svelte';
    import WorkoutController from '@/actions/App/Http/Controllers/WorkoutController';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import {
        Dialog,
        DialogContent,
        DialogTitle,
        DialogTrigger,
    } from '@/components/ui/dialog';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { toDateTimeLocal } from '@/lib/datetime';
    import { show } from '@/routes/workouts';
    import type { Workout } from '@/types';

    let { workouts }: { workouts: Workout[] } = $props();

    let open = $state(false);
</script>

<AppHead title="Workouts" />

<div class="mx-auto flex w-full max-w-2xl flex-col gap-4 p-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Workouts</h1>

        <Dialog bind:open>
            <DialogTrigger asChild>
                {#snippet children(props)}
                    <Button {...props}>New workout</Button>
                {/snippet}
            </DialogTrigger>

            <DialogContent class="grid gap-4">
                <DialogTitle>New workout</DialogTitle>

                <Form
                    {...WorkoutController.store.form()}
                    class="grid gap-4"
                    onSuccess={() => (open = false)}
                >
                    {#snippet children({ errors, processing })}
                        <div class="grid gap-2">
                            <Label for="title">Title</Label>
                            <Input
                                id="title"
                                name="title"
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
                                value={toDateTimeLocal()}
                                required
                            />
                            <InputError message={errors.performed_at} />
                        </div>

                        <Button type="submit" disabled={processing}>
                            Start workout
                        </Button>
                    {/snippet}
                </Form>
            </DialogContent>
        </Dialog>
    </div>

    {#each workouts as workout (workout.id)}
        <Link
            href={show(workout.id)}
            class="flex items-center justify-between rounded-2xl bg-card p-4 shadow-sm transition-colors hover:bg-accent"
        >
            <div>
                <p class="font-medium">{workout.title ?? 'Workout'}</p>
                <p class="text-sm text-muted-foreground">
                    {new Date(workout.performed_at).toLocaleDateString()}
                </p>
            </div>
            <span class="text-sm text-muted-foreground">
                {workout.exercises_count} exercises
            </span>
        </Link>
    {:else}
        <p class="text-sm text-muted-foreground">
            No workouts yet. Start one to get going.
        </p>
    {/each}
</div>
