@props([
    'icon' => 'inbox',
    'title' => null,
    'description' => null,
    'actionLabel' => null,
    'actionUrl' => null,
    'layout' => 'inline',
])

@php
    $title = $title ?? __('common.no_data');
    $isFullscreen = $layout === 'fullscreen';
@endphp

@if ($isFullscreen)
    <div {{ $attributes->merge(['class' => '-mx-4 -mt-6 -mb-6 flex min-h-[70vh] items-center justify-center bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900']) }}>
        <div class="-mt-16 flex flex-col items-center gap-4 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon name="{{ $icon }}" class="size-8 text-zinc-400" />
            </div>
            <div>
                <p class="text-base font-medium text-zinc-900 dark:text-zinc-100">{{ $title }}</p>
                @if ($description)
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
                @endif
            </div>
            @if ($actionLabel && $actionUrl)
                <a
                    href="{{ $actionUrl }}"
                    wire:navigate
                    class="mt-2 inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    <flux:icon name="plus" class="size-4" />
                    {{ $actionLabel }}
                </a>
            @endif
            {{ $slot }}
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 px-4']) }}>
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
            <flux:icon name="{{ $icon }}" class="size-8 text-zinc-400" />
        </div>

        <h3 class="mt-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>

        @if ($description)
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
        @endif

        @if ($actionLabel && $actionUrl)
            <a
                href="{{ $actionUrl }}"
                wire:navigate
                class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                <flux:icon name="plus" class="size-4" />
                {{ $actionLabel }}
            </a>
        @endif

        {{ $slot }}
    </div>
@endif
