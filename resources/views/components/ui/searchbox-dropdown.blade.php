@props([
    'wireModel' => 'search',
    'placeholder' => 'Search...',
])

<flux:dropdown position="bottom" align="center" class="w-[360px]">
    <div class="relative flex h-9 w-full items-center overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
        <input
            type="text"
            wire:model.live.debounce.300ms="{{ $wireModel }}"
            placeholder="{{ $placeholder }}"
            class="h-full w-full border-0 bg-transparent pl-9 pr-10 text-sm outline-none focus:ring-0"
        />
        <button type="button" class="absolute right-0 top-0 flex h-full items-center border-l border-zinc-200 bg-white/80 px-2.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/80 dark:hover:bg-zinc-700 dark:hover:text-zinc-200">
            <flux:icon name="chevron-down" class="size-4" />
        </button>
    </div>

    <flux:menu class="w-[640px] max-w-[90vw]">
        {{ $slot }}
    </flux:menu>
</flux:dropdown>
