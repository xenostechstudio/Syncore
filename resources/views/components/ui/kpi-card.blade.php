@props([
    'title',
    'value',
    'icon' => 'chart-bar',
    'color' => 'blue', // blue, emerald, amber, violet, red, zinc
    'change' => null, // percentage change
    'changeLabel' => 'vs last period',
    'prefix' => '',
    'suffix' => '',
    'badge' => null,
    'badgeColor' => 'amber',
])

@php
    $colorClasses = [
        'blue' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
        'emerald' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
        'amber' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
        'violet' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400',
        'red' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
        'zinc' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400',
    ];

    $badgeColorClasses = [
        'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'red' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900']) }}>
    <div class="flex items-center justify-between">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $colorClasses[$color] ?? $colorClasses['blue'] }}">
            <flux:icon name="{{ $icon }}" class="size-5" />
        </div>
        
        @if($change !== null && $change != 0)
            <span class="inline-flex items-center gap-1 text-xs font-medium {{ $change > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                <flux:icon name="{{ $change > 0 ? 'arrow-trending-up' : 'arrow-trending-down' }}" class="size-3" />
                {{ abs(round($change, 1)) }}%
            </span>
        @elseif($badge)
            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeColorClasses[$badgeColor] ?? $badgeColorClasses['amber'] }}">
                {{ $badge }}
            </span>
        @endif
    </div>
    
    <div class="mt-3">
        <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
            {{ $prefix }}{{ $value }}{{ $suffix }}
        </p>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $title }}</p>
    </div>
    
    {{ $slot }}
</div>
