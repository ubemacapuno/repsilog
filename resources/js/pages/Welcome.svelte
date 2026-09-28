<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import { toUrl } from '@/lib/utils';
    import { login, register } from '@/routes';
    import { index as workouts } from '@/routes/workouts';

    const auth = $derived(page.props.auth);
</script>

<AppHead title="Rep Silog" />

<div
    class="flex min-h-screen flex-col items-center justify-center gap-10 bg-background p-6 text-foreground"
>
    <div class="flex flex-col items-center gap-3 text-center">
        <h1 class="font-mono text-sm tracking-[0.3em] uppercase">Rep Silog</h1>
        <p class="max-w-sm text-sm text-muted-foreground">
            Log every set, every rep, every mile. Nothing else.
        </p>
    </div>

    <div class="flex items-center gap-3">
        {#if auth.user}
            <Button asChild>
                {#snippet children(props)}
                    <Link {...props} href={toUrl(workouts())}>
                        Go to workouts
                    </Link>
                {/snippet}
            </Button>
        {:else}
            <Button asChild variant="outline" class="text-primary">
                {#snippet children(props)}
                    <Link {...props} href={toUrl(login())}>Log in</Link>
                {/snippet}
            </Button>
            <Button asChild>
                {#snippet children(props)}
                    <Link {...props} href={toUrl(register())}>
                        Create account
                    </Link>
                {/snippet}
            </Button>
        {/if}
    </div>
</div>
