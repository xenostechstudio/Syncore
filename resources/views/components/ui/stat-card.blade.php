@props([
    'label',
    'value',
    'icon' => null,
    'trend' => null,
    'trendUp' => true,
    'subtitle' => null,
])

<x-ui.card {{ $attributes }}>
    <div class="space-y-3">
        {{-- Header with label and icon --}}
        <div class="flex items-center justify-between">
            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">{{ $label }}</span>
            @if($icon)
                <flux:icon :name="$icon" class="size-4 text-zinc-400 dark:text-zinc-500" />
            @endif
        </div>
        
        {{-- Value --}}
        <p class="text-2xl font-normal text-zinc-900 dark:text-zinc-100">{{ $value }}</p>
        
        {{-- Trend or Subtitle --}}
        @if($trend)
            <div class="flex items-center gap-1.5 text-xs {{ $trendUp ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                @if($trendUp)
                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                    </svg>
                @else
                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5l15 15m0 0V8.25m0 11.25H8.25" />
                    </svg>
                @endif
                <span>{{ $trend }}</span>
            </div>
        @elseif($subtitle)
            <p class="text-xs font-light text-zinc-400 dark:text-zinc-500">{{ $subtitle }}</p>
        @endif
    </div>
</x-ui.card>
