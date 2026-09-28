<script lang="ts">
    let {
        class: className = '',
        ...rest
    }: {
        class?: string;
        [key: string]: unknown;
    } = $props();
</script>

<svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 40 40"
    class={className}
    {...rest}
>
    <path
        d="M20 4c6.5-.8 12 2 14 7.5 1.6 4.5-1.6 7.7-.8 12.1 1 5.8-3.6 12-10 12.2-5.6.2-8.8-3.2-12.6-4.4C5.2 29.8 3 25.2 4.8 20c1.6-4.8.6-9.6 4.8-12.8C12.6 4.9 16 4.4 20 4Z"
        fill="#fff"
        stroke="currentColor"
        stroke-width="2"
        stroke-linejoin="round"
    />
    <circle cx="21" cy="19" r="6.5" fill="#f5a623" />
</svg>
