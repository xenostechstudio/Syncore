@props([
    'href',
    'icon',
    'title',
    'subtitle' => null,
    'iconColor' => 'zinc',
    'chevron' => false,
])

@php
    // Literal classes so Tailwind JIT picks them up.
    [$iconBg, $iconText] = match ($iconColor) {
        'blue'    => ['bg-blue-50 dark:bg-blue-900/20', 'text-blue-600 dark:text-blue-400'],
        'purple'  => ['bg-purple-50 dark:bg-purple-900/20', 'text-purple-600 dark:text-purple-400'],
        'emerald' => ['bg-emerald-50 dark:bg-emerald-900/20', 'text-emerald-600 dark:text-emerald-400'],
        'amber'   => ['bg-amber-50 dark:bg-amber-900/20', 'text-amber-600 dark:text-amber-400'],
        'red'     => ['bg-red-50 dark:bg-red-900/20', 'text-red-600 dark:text-red-400'],
        'violet'  => ['bg-violet-50 dark:bg-violet-900/20', 'text-violet-600 dark:text-violet-400'],
        default   => ['bg-zinc-100 dark:bg-zinc-800', 'text-zinc-600 dark:text-zinc-400'],
    };
@endphp

@if($chevron)
    <a href="{{ $href }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
        <div class="flex items-center gap-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $iconBg }}">
                <flux:icon :name="$icon" class="size-5 {{ $iconText }}" />
            </div>
            <div>
                <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $title }}</p>
                @if($subtitle)
                    <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        <flux:icon name="chevron-right" class="size-4 text-zinc-300 dark:text-zinc-600" />
    </a>
@else
    <a href="{{ $href }}" wire:navigate class="flex items-center gap-4 rounded-lg border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $iconBg }}">
            <flux:icon :name="$icon" class="size-5 {{ $iconText }}" />
        </div>
        <div>
            <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $title }}</p>
            @if($subtitle)
                <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $subtitle }}</p>
            @endif
        </div>
    </a>
@endif
