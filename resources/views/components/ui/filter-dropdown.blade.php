@props([
    'label' => 'Filter',
    'value' => null,
    'options' => [],
    'allLabel' => 'All',
])

<flux:dropdown>
    <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-light text-zinc-600 transition-colors hover:border-zinc-300 hover:text-zinc-900 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-zinc-700 dark:hover:text-zinc-100">
        <span>{{ $label }}{{ $value ? ': ' . $value : '' }}</span>
        <svg class="size-[20px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <flux:menu>
        {{ $slot }}
    </flux:menu>
</flux:dropdown>
