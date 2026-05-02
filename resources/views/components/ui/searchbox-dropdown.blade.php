@props([
    'wireModel' => 'search',
    'placeholder' => 'Search...',
    'widthClass' => 'w-[360px]',
    'width' => null,
    'align' => 'center',
    // Number of non-default filters currently applied. When > 0, a small
    // count pill appears on the chevron so users see at a glance that
    // filters are active without having to open the menu.
    'activeFilterCount' => 0,
    // Wire action to call from the "Clear all" footer (typically
    // 'clearFilters' which is provided by the WithIndexComponent trait).
    // Set to null to omit the footer entirely.
    'clearAction' => null,
    'clearActionLabel' => 'Clear all filters',
    // Keyboard shortcut to focus the search input. Set to false to disable.
    'shortcut' => '/',
])

<div
    x-data="{
        focusSearch(e) {
            const t = e.target;
            const tag = t && t.tagName;
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || (t && t.isContentEditable)) return;
            e.preventDefault();
            this.$refs.searchInput && this.$refs.searchInput.focus();
        }
    }"
    @if($shortcut)
        @keydown.window="if ($event.key === '{{ $shortcut }}') focusSearch($event)"
    @endif
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
                x-ref="searchInput"
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
                    @class([
                        'flex h-full items-center justify-center gap-1 border-l border-zinc-200 bg-white/90 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/80 dark:hover:bg-zinc-700 dark:hover:text-zinc-200',
                        'w-10' => $activeFilterCount === 0,
                        'px-2' => $activeFilterCount > 0,
                    ])
                >
                    <flux:icon name="chevron-down" class="size-4" />
                    @if($activeFilterCount > 0)
                        <span class="inline-flex h-4 min-w-[16px] items-center justify-center rounded-full bg-zinc-900 px-1 text-[10px] font-semibold leading-none text-white dark:bg-zinc-100 dark:text-zinc-900">
                            {{ $activeFilterCount }}
                        </span>
                    @endif
                </button>

                <flux:menu class="w-[640px] max-w-[90vw]">
                    {{ $slot }}

                    @if($clearAction && $activeFilterCount > 0)
                        <flux:menu.separator />
                        <button
                            type="button"
                            wire:click="{{ $clearAction }}"
                            class="flex w-full items-center justify-center gap-1.5 rounded-md px-2 py-1.5 text-xs font-medium text-zinc-600 transition-colors hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                        >
                            <flux:icon name="arrow-path" class="size-3.5" />
                            <span>{{ $clearActionLabel }}</span>
                        </button>
                    @endif
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>
</div>
