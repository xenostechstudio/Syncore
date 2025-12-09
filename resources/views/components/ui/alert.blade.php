@props([
    'type' => 'info',
    'dismissible' => true,
    'duration' => 5000,
])

@php
    $typeConfig = [
        'success' => [
            'bg' => 'bg-emerald-50 dark:bg-emerald-900/20',
            'border' => 'border-emerald-200 dark:border-emerald-800',
            'text' => 'text-emerald-800 dark:text-emerald-300',
            'icon' => 'check-circle',
            'iconColor' => 'text-emerald-500 dark:text-emerald-400',
            'progressBg' => 'bg-emerald-500',
        ],
        'error' => [
            'bg' => 'bg-red-50 dark:bg-red-900/20',
            'border' => 'border-red-200 dark:border-red-800',
            'text' => 'text-red-800 dark:text-red-300',
            'icon' => 'x-circle',
            'iconColor' => 'text-red-500 dark:text-red-400',
            'progressBg' => 'bg-red-500',
        ],
        'warning' => [
            'bg' => 'bg-amber-50 dark:bg-amber-900/20',
            'border' => 'border-amber-200 dark:border-amber-800',
            'text' => 'text-amber-800 dark:text-amber-300',
            'icon' => 'exclamation-triangle',
            'iconColor' => 'text-amber-500 dark:text-amber-400',
            'progressBg' => 'bg-amber-500',
        ],
        'info' => [
            'bg' => 'bg-blue-50 dark:bg-blue-900/20',
            'border' => 'border-blue-200 dark:border-blue-800',
            'text' => 'text-blue-800 dark:text-blue-300',
            'icon' => 'information-circle',
            'iconColor' => 'text-blue-500 dark:text-blue-400',
            'progressBg' => 'bg-blue-500',
        ],
    ];
    $config = $typeConfig[$type] ?? $typeConfig['info'];
@endphp

<div 
    x-data="{ 
        show: true, 
        progress: 100,
        duration: {{ $duration }},
        startTimer() {
            const interval = 50;
            const step = (interval / this.duration) * 100;
            const timer = setInterval(() => {
                this.progress -= step;
                if (this.progress <= 0) {
                    clearInterval(timer);
                    this.show = false;
                }
            }, interval);
        }
    }"
    x-init="startTimer()"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-lg border ' . $config['bg'] . ' ' . $config['border']]) }}
>
    <div class="flex items-start gap-3 p-4">
        <flux:icon name="{{ $config['icon'] }}" class="size-5 flex-shrink-0 {{ $config['iconColor'] }}" />
        <div class="flex-1 {{ $config['text'] }}">
            <p class="text-sm font-medium">{{ $slot }}</p>
        </div>
        @if($dismissible)
            <button 
                type="button" 
                @click="show = false"
                class="flex-shrink-0 rounded p-0.5 transition-colors hover:bg-black/5 dark:hover:bg-white/10 {{ $config['text'] }}"
            >
                <flux:icon name="x-mark" class="size-4" />
            </button>
        @endif
    </div>
    {{-- Progress Bar --}}
    <div class="absolute bottom-0 left-0 h-1 w-full bg-black/5 dark:bg-white/10">
        <div 
            class="h-full transition-all duration-100 ease-linear {{ $config['progressBg'] }}"
            :style="'width: ' + progress + '%'"
        ></div>
    </div>
</div>
