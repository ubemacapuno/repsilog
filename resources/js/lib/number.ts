/**
 * e.g. 95.00 reads as 95 while "95.01" stays the same
 */
export function formatDecimal(value: string | null): string {
    if (value === null || value === '') {
        return '';
    }

    const parsed = Number(value);

    return Number.isNaN(parsed) ? value : String(parsed);
}
