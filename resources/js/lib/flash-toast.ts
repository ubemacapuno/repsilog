import { router } from '@inertiajs/svelte';
import { toast } from 'svelte-sonner';
import type { FlashToast } from '@/types/ui';

export function initializeFlashToast(): void {
    router.on('flash', (event) => {
        const flash = (event as CustomEvent).detail?.flash;
        const data = flash?.toast as FlashToast | undefined;

        if (!data) {
            return;
        }

        toast[data.type](data.message);
    });

    router.on('error', (event) => {
        const errors = (event as CustomEvent).detail?.errors as
            | Record<string, string>
            | undefined;

        const messages = Object.values(errors ?? {});

        if (messages.length === 0) {
            return;
        }

        toast.error(messages[0]);
    });
}
