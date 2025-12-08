@props([
    'title' => null,
    'createRoute' => null,
    'createLabel' => 'Add New',
])

<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        @if($title)
            <h1 class="text-xl font-normal text-zinc-900 dark:text-zinc-100">{{ $title }}</h1>
        @else
            <div></div>
        @endif
        
        @if($createRoute)
            <flux:button variant="primary" icon="plus" :href="$createRoute" wire:navigate>
                {{ $createLabel }}
            </flux:button>
        @endif
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {{-- Left: Search & Filters --}}
        <div class="flex flex-1 items-center gap-3">
            {{ $toolbar ?? '' }}
        </div>

        {{-- Right: View Toggle --}}
        <div class="flex items-center gap-2">
            {{ $actions ?? '' }}
        </div>
    </div>

    {{-- Content --}}
    <div>
        {{ $slot }}
    </div>

    {{-- Pagination --}}
    @if(isset($pagination))
        <div class="flex items-center justify-between border-t border-zinc-200 pt-4 dark:border-zinc-800">
            {{ $pagination }}
        </div>
    @endif
</div>
