import { format, intervalToDuration } from 'date-fns';

export function toDateTimeLocal(value: string | Date = new Date()): string {
    return format(value, "yyyy-MM-dd'T'HH:mm");
}

export function fromDateTimeLocal(value: string): string {
    const parsed = new Date(value);

    return Number.isNaN(parsed.getTime()) ? value : parsed.toISOString();
}

export function formatDuration(totalSeconds: number | null): string {
    const {
        hours = 0,
        minutes = 0,
        seconds = 0,
    } = intervalToDuration({ start: 0, end: (totalSeconds ?? 0) * 1000 });

    const parts = [
        hours ? `${hours}h` : '',
        minutes ? `${minutes}m` : '',
        seconds || (!hours && !minutes) ? `${seconds}s` : '',
    ];

    return parts.filter(Boolean).join(' ');
}

export function formatWorkoutDate(value: string): string {
    return format(value, 'EEE MMM d').toUpperCase();
}
