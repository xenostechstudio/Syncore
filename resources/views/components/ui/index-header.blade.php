@props([
    'title' => 'Items',
    'createRoute' => null,
    'showNew' => true,
    'searchPlaceholder' => null,
    'views' => ['list', 'grid'],
    'view' => 'list',
    'paginator' => null,
    'showPagination' => true,
    'selected' => [],
])

@php
    $searchPlaceholder = $searchPlaceholder ?? __('common.search') . '...';
@endphp

<div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
    <div class="flex w-full items-center justify-between gap-4">
        {{-- Left Group: New Button, Title, Gear --}}
        <div class="flex items-center gap-3">
            @if($showNew && $createRoute)
                <a href="{{ $createRoute }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    {{ __('common.new') }}
                </a>
            @endif
            <span class="text-md font-light text-zinc-600 dark:text-zinc-400">
                {{ $title }}
            </span>

            {{-- Actions Menu (Gear) --}}
            <flux:dropdown position="bottom" align="start">
                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="cog-6-tooth" class="size-5" />
                </button>

                <flux:menu class="w-48">
                    {{ $actions ?? '' }}
                    @if(!isset($actions))
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>{{ __('common.import_records') }}</span>
                        </button>
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>{{ __('common.export_all') }}</span>
                        </button>
                    @endif
                </flux:menu>
            </flux:dropdown>
        </div>

        {{-- Center: Search with dropdown or Selection Toolbar --}}
        <div class="flex flex-1 items-center justify-center">
            @if(count($selected) > 0)
                {{-- Selection Toolbar --}}
                <div class="flex items-center gap-2 animate-in fade-in slide-in-from-top-2 duration-200">
                    <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-zinc-100 px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-200 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-600">
                        <flux:icon name="x-mark" class="size-4" />
                        <span>{{ count($selected) }} {{ __('common.selected') }}</span>
                    </button>
                    {{ $selectionActions ?? '' }}
                </div>
            @else
                {{-- Search Input with Dropdown --}}
                <div class="relative flex h-9 w-[360px] items-center overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ $searchPlaceholder }}" 
                        class="h-full w-full border-0 bg-transparent pl-9 pr-10 text-sm outline-none focus:ring-0" 
                    />

                    {{-- Filters / Sort / Group dropdown --}}
                    @if(isset($filters))
                        <flux:dropdown position="bottom" align="center">
                            <button class="absolute inset-y-0 right-0 flex w-10 items-center justify-center border-l border-zinc-200 bg-white/80 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/80 dark:hover:bg-zinc-700 dark:hover:text-zinc-200">
                                <flux:icon name="chevron-down" class="size-4" />
                            </button>

                            <flux:menu class="w-[560px] max-w-[90vw]">
                                <div class="flex flex-col gap-4 p-3 md:flex-row">
                                    {{ $filters }}
                                </div>
                            </flux:menu>
                        </flux:dropdown>
                    @endif
                </div>
            @endif
        </div>

        {{-- Right Group: Pagination Info + View Toggle --}}
        <div class="flex items-center gap-3">
            @if($showPagination && $paginator)
                {{-- Pagination Info & Navigation --}}
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }}/{{ $paginator->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button 
                            type="button"
                            wire:click="goToPreviousPage"
                            @disabled($paginator->onFirstPage())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button 
                            type="button"
                            wire:click="goToNextPage"
                            @disabled(!$paginator->hasMorePages())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>
            @endif

            {{-- View Toggle --}}
            @if(count($views) > 0)
                <x-ui.view-toggle :view="$view" :views="$views" />
            @endif
        </div>
    </div>
</div>
