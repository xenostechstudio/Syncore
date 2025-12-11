@props([
    'show', // Alpine state variable name as string, e.g. 'showCancelModal'
    'maxWidth' => 'sm', // sm, md, lg
])

@php
    $maxWidthClass = match($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        default => 'max-w-sm',
    };
@endphp

<div 
    x-show="{{ $show }}" 
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="{{ $show }} = false"></div>

    {{-- Modal Content --}}
    <div 
        class="relative z-10 w-full {{ $maxWidthClass }} overflow-hidden rounded-xl bg-white shadow-xl dark:bg-zinc-900"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="{{ $show }} = false"
    >
        {{-- Modal Body --}}
        <div class="px-6 pb-4 pt-6">
            <div class="flex flex-col items-center text-center">
                @isset($icon)
                    <div class="mb-4">
                        {{ $icon }}
                    </div>
                @endisset

                @isset($title)
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $title }}
                    </h3>
                @endisset

                @isset($description)
                    <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                        {{ $description }}
                    </p>
                @endisset
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="flex items-center justify-center gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/50">
            {{ $actions ?? $slot }}
        </div>
    </div>
</div>
