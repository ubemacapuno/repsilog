<script lang="ts">
    import { Button } from '@/components/ui/button';
    import {
        Dialog,
        DialogContent,
        DialogDescription,
        DialogFooter,
        DialogTitle,
    } from '@/components/ui/dialog';

    let {
        open = $bindable(false),
        title,
        description,
        confirmLabel = 'Delete',
        onconfirm,
    }: {
        open?: boolean;
        title: string;
        description: string;
        confirmLabel?: string;
        onconfirm: () => void;
    } = $props();

    function confirm() {
        open = false;
        onconfirm();
    }
</script>

<Dialog bind:open>
    <DialogContent class="space-y-4">
        <div class="space-y-2">
            <DialogTitle>{title}</DialogTitle>
            <DialogDescription>{description}</DialogDescription>
        </div>

        <DialogFooter class="gap-2">
            <Button variant="secondary" onclick={() => (open = false)}>
                Cancel
            </Button>
            <Button variant="destructive" onclick={confirm}>
                {confirmLabel}
            </Button>
        </DialogFooter>
    </DialogContent>
</Dialog>
