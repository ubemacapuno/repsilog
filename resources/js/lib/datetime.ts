import { format } from 'date-fns';

export function toDateTimeLocal(value: string | Date = new Date()): string {
    return format(value, "yyyy-MM-dd'T'HH:mm");
}

export function fromDateTimeLocal(value: string): string {
    const parsed = new Date(value);

    return Number.isNaN(parsed.getTime()) ? value : parsed.toISOString();
}

export function formatDuration(totalSeconds: number | null): string {
    const total = Math.max(0, Math.trunc(totalSeconds ?? 0));
    const hours = Math.floor(total / 3600);
    const minutes = Math.floor((total % 3600) / 60);
    const seconds = total % 60;

    const parts = [
        hours ? `${hours}h` : '',
        minutes ? `${minutes}m` : '',
        seconds || (hours === 0 && minutes === 0) ? `${seconds}s` : '',
    ];

    return parts.filter(Boolean).join(' ');
}

export function formatWorkoutDate(value: string): string {
    return format(value, 'EEE MMM d').toUpperCase();
}
