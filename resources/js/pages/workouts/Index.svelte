<script module lang="ts">
    import {index} from '@/routes/workouts';

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
    import {Form, Link} from '@inertiajs/svelte';
    import Plus from '@lucide/svelte/icons/plus';
    import WorkoutSessionController from '@/actions/App/Http/Controllers/WorkoutSessionController';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import {Button} from '@/components/ui/button';
    import {
        Dialog,
        DialogContent,
        DialogTitle,
        DialogTrigger,
    } from '@/components/ui/dialog';
    import {Input} from '@/components/ui/input';
    import {Label} from '@/components/ui/label';
    import {
        formatWorkoutDate,
        fromDateTimeLocal,
        toDateTimeLocal,
    } from '@/lib/datetime';
    import {show} from '@/routes/workouts';
    import type {Paginated, WorkoutSession} from '@/types';

    let {workouts}: { workouts: Paginated<WorkoutSession> } = $props();

    let open = $state(false);
    let performedAt = $state(toDateTimeLocal());
</script>

<AppHead title="Workouts"/>

<div class="mx-auto flex w-full max-w-2xl flex-col gap-4 p-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Workouts</h1>

        <Dialog bind:open>
            <DialogTrigger asChild>
                {#snippet children(props)}
                    <Button {...props} variant="outline" class="text-primary">
                        <Plus class="size-4"/>
                        New workout
                    </Button>
                {/snippet}
            </DialogTrigger>

            <DialogContent class="grid gap-4">
                <DialogTitle>New workout</DialogTitle>

                <Form
                    {...WorkoutSessionController.store.form()}
                    class="grid gap-4"
                    onSuccess={() => (open = false)}
                >
                    {#snippet children({errors, processing})}
                        <div class="grid gap-2">
                            <Label for="title">Title</Label>
                            <Input
                                id="title"
                                name="title"
                                placeholder="Push day"
                            />
                            <InputError message={errors.title}/>
                        </div>

                        <div class="grid gap-2">
                            <Label for="performed_at">Performed at</Label>
                            <Input
                                id="performed_at"
                                type="datetime-local"
                                bind:value={performedAt}
                                required
                            />
                            <input
                                type="hidden"
                                name="performed_at"
                                value={fromDateTimeLocal(performedAt)}
                            />
                            <InputError message={errors.performed_at}/>
                        </div>

                        <Button type="submit" disabled={processing}>
                            Start workout
                        </Button>
                    {/snippet}
                </Form>
            </DialogContent>
        </Dialog>
    </div>

    {#each workouts.data as workout (workout.id)}
        <Link
            href={show(workout.id)}
            class="flex items-center justify-between border-t border-border px-2 py-3 transition-colors hover:bg-accent"
        >
            <div>
                <p class="font-semibold">{workout.title ?? 'Untitled workout'}</p>
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
            No workouts yet. Start one to get going.
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
