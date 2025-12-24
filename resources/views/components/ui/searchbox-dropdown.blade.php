@props([
    'wireModel' => 'search',
    'placeholder' => 'Search...',
    'widthClass' => 'w-[360px]',
    'width' => null,
    'align' => 'center',
])

<div 
    class="relative flex-none shrink-0 {{ $widthClass }}" 
    @if($width) style="width: {{ $width }}; min-width: {{ $width }}; max-width: {{ $width }};" @endif
>
    <div class="relative flex h-9 w-full items-center overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
        <div class="flex h-full w-full min-w-0 items-center gap-2 pl-9 pr-12">
            @isset($badge)
                <div class="flex-none" wire:key="searchbox-badge">
                    {{ $badge }}
                </div>
            @endisset
            <input
                type="text"
                wire:model.live.debounce.300ms="{{ $wireModel }}"
                placeholder="{{ $placeholder }}"
                class="h-full w-full min-w-0 flex-1 border-0 bg-transparent text-sm outline-none focus:ring-0"
            />
        </div>

        <div class="absolute inset-y-0 right-0 flex items-stretch">
            <flux:dropdown position="bottom" align="{{ $align }}">
                <button
                    type="button"
                    class="flex h-full w-10 items-center justify-center border-l border-zinc-200 bg-white/90 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/80 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
                > 
                    <flux:icon name="chevron-down" class="size-4" />
                </button>

                <flux:menu class="w-[640px] max-w-[90vw]">
                    {{ $slot }}
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>
</div>
