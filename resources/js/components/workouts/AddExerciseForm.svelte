<script lang="ts">
    import {Form} from '@inertiajs/svelte';
    import WorkoutSessionExerciseController from '@/actions/App/Http/Controllers/WorkoutSessionExerciseController';
    import InputError from '@/components/InputError.svelte';
    import {Button} from '@/components/ui/button';
    import {Input} from '@/components/ui/input';
    import {Label} from '@/components/ui/label';
    import type {Exercise, ExerciseType} from '@/types';

    let {
        workoutId,
        exercises = [],
    }: {
        workoutId: number;
        exercises?: Exercise[];
    } = $props();

    let name = $state('');
    let chosenType = $state<ExerciseType>('strength');
    let minutes = $state('');
    let seconds = $state('');
    let open = $state(false);
    let highlighted = $state(0);

    const query = $derived(name.trim().toLowerCase());

    const suggestions = $derived(
        exercises
            .filter((exercise) => exercise.name.toLowerCase().includes(query))
            .slice(0, 12)
            .sort((a, b) => a.name.localeCompare(b.name)),
    );

    const known = $derived(
        exercises.find(
            (exercise) => exercise.name.toLowerCase() === query,
        ),
    );

    const type = $derived(known?.type ?? chosenType);

    const isNew = $derived(query !== '' && known === undefined);

    const durationSeconds = $derived(
        (Number(minutes) || 0) * 60 + (Number(seconds) || 0),
    );

    function choose(suggestion: Exercise) {
        name = suggestion.name;
        open = false;
        highlighted = 0;
    }

    function reset() {
        name = '';
        chosenType = 'strength';
        minutes = '';
        seconds = '';
        open = false;
        highlighted = 0;
    }

    function handleKeydown(event: KeyboardEvent) {
        if (event.key === 'Escape') {
            open = false;

            return;
        }

        if (event.key === 'ArrowDown' && !open) {
            open = true;

            return;
        }

        if (!open || suggestions.length === 0) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            highlighted = (highlighted + 1) % suggestions.length;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            highlighted =
                (highlighted - 1 + suggestions.length) % suggestions.length;
        }

        if (event.key === 'Enter') {
            const suggestion = suggestions[highlighted];

            if (suggestion === undefined) {
                return;
            }

            event.preventDefault();
            choose(suggestion);
        }
    }
</script>

<Form
    {...WorkoutSessionExerciseController.store.form(workoutId)}
    class="grid gap-4"
    resetOnSuccess
    options={{ preserveScroll: true }}
    onSuccess={reset}
>
    {#snippet children({errors, processing})}
        <div class="grid gap-2">
            <Label for="name">Exercise</Label>

            <div class="relative">
                <Input
                    id="name"
                    name="name"
                    bind:value={name}
                    onfocus={() => {
                        open = true;
                        highlighted = 0;
                    }}
                    onblur={() => (open = false)}
                    oninput={() => {
                        open = true;
                        highlighted = 0;
                    }}
                    onkeydown={handleKeydown}
                    autocomplete="off"
                    aria-expanded={open}
                    required
                    placeholder="Search or add an exercise"
                />

                {#if open && suggestions.length}
                    <div
                        class="absolute z-10 mt-1 w-full overflow-hidden rounded-md border bg-background shadow-md"
                    >
                        {#each suggestions as suggestion, position (suggestion.name + suggestion.type)}
                            <button
                                type="button"
                                onmousedown={(event) => event.preventDefault()}
                                onclick={() => choose(suggestion)}
                                class="flex w-full items-center justify-between px-3 py-2 text-left text-sm {position ===
                                highlighted
                                    ? 'bg-accent text-accent-foreground'
                                    : ''}"
                            >
                                <span>{suggestion.name}</span>
                                <span class="text-xs text-muted-foreground">
                                    {suggestion.type}
                                </span>
                            </button>
                        {/each}
                    </div>
                {/if}
            </div>

            <InputError message={errors.name}/>
        </div>

        {#if isNew}
            <div class="grid gap-2">
                <Label for="type">Type</Label>
                <select
                    id="type"
                    name="type"
                    bind:value={chosenType}
                    class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                >
                    <option value="strength">Strength</option>
                    <option value="cardio">Cardio</option>
                </select>
                <InputError message={errors.type}/>
            </div>
        {:else}
            <input type="hidden" name="type" value={type}/>
        {/if}

        {#if type === 'cardio'}
            <div class="grid gap-2">
                <span class="text-sm leading-none font-medium">Duration</span>
                <div class="flex items-end gap-2">
                    <div class="grid flex-1 gap-1">
                        <Label
                            for="minutes"
                            class="text-xs text-muted-foreground"
                        >
                            Minutes
                        </Label>
                        <Input
                            id="minutes"
                            type="text"
                            inputmode="numeric"
                            placeholder="0"
                            bind:value={minutes}
                        />
                    </div>
                    <div class="grid flex-1 gap-1">
                        <Label
                            for="seconds"
                            class="text-xs text-muted-foreground"
                        >
                            Seconds
                        </Label>
                        <Input
                            id="seconds"
                            type="text"
                            inputmode="numeric"
                            placeholder="0"
                            bind:value={seconds}
                        />
                    </div>
                </div>
                <input
                    type="hidden"
                    name="duration_seconds"
                    value={durationSeconds}
                />
                <InputError message={errors.duration_seconds}/>
            </div>

            <div class="grid gap-2">
                <Label for="distance_miles">Distance (miles)</Label>
                <Input
                    id="distance_miles"
                    name="distance_miles"
                    type="text"
                    inputmode="decimal"
                />
                <InputError message={errors.distance_miles}/>
            </div>
        {/if}

        <Button type="submit" disabled={processing}>Add exercise</Button>
    {/snippet}
</Form>
