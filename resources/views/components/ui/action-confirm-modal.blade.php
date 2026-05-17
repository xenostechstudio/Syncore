@props([
    'show',                              // Alpine state variable name as string, e.g. 'showDuplicateModal'
    'icon',                              // Flux icon name, e.g. 'document-duplicate'
    'color' => 'zinc',                   // zinc | amber | red — drives the icon container + primary button color
    'title',                             // Modal title shown next to the icon
    'subtitle' => null,                  // Optional one-line subtitle below the title
    'confirmLabel',                      // Primary button text, e.g. 'Duplicate Order'
    'confirmLoadingLabel' => null,       // Optional swap-in while wire:loading, e.g. 'Duplicating...'
    'confirmMethod',                     // Livewire method to call on click, e.g. 'duplicate'
    'cancelLabel' => 'Cancel',           // Secondary button text
    'maxWidth' => 'md',                  // sm | md | lg
])

@php
    // Tailwind JIT requires literal class strings — no string interpolation
    // like bg-{{ $color }}-100, that won't render. Match block keeps the
    // emitted classes literal and tracked by the JIT scanner.
    [$iconClasses, $confirmClasses] = match ($color) {
        'amber' => [
            'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
            'bg-amber-600 text-white hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600',
        ],
        'red' => [
            'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
            'bg-red-600 text-white hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600',
        ],
        default /* zinc */ => [
            'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
            'bg-zinc-900 text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200',
        ],
    };

    // Split into bg/text for the icon container and a separate text class
    // so the icon glyph picks up the foreground color, not the bg.
    // For the icon: split iconClasses by "bg-" and "text-" tokens.
    preg_match_all('/(?:^|\s)(bg-\S+)/', $iconClasses, $bgs);
    preg_match_all('/(?:^|\s)(text-\S+)/', $iconClasses, $texts);
    $iconBg   = implode(' ', $bgs[1]);
    $iconText = implode(' ', $texts[1]);

    $maxWidthClass = match ($maxWidth) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-lg',
        default => 'max-w-md',
    };
@endphp

<div
    x-show="{{ $show }}"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-zinc-900/60" @click="{{ $show }} = false"></div>

    {{-- Modal Card --}}
    <div
        class="relative w-full {{ $maxWidthClass }} rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900 dark:ring-1 dark:ring-white/10"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="{{ $show }} = false"
    >
        {{-- Header: icon + title + optional subtitle, left-aligned row --}}
        <div class="mb-4 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $iconBg }}">
                <flux:icon :name="$icon" class="size-5 {{ $iconText }}" />
            </div>
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        {{-- Body: default slot is the description paragraph (also handles
             multi-sentence copy like Purchase Bills' lifecycle warning). --}}
        <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">{{ $slot }}</p>

        {{-- Footer: right-aligned Cancel + primary --}}
        <div class="flex justify-end gap-3">
            <button
                type="button"
                @click="{{ $show }} = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
            >
                {{ $cancelLabel }}
            </button>
            <button
                type="button"
                wire:click="{{ $confirmMethod }}"
                wire:loading.attr="disabled"
                wire:target="{{ $confirmMethod }}"
                @click="{{ $show }} = false"
                class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium transition-colors disabled:opacity-50 {{ $confirmClasses }}"
            >
                <flux:icon :name="$icon" wire:loading.remove wire:target="{{ $confirmMethod }}" class="size-4" />
                <flux:icon name="arrow-path" wire:loading wire:target="{{ $confirmMethod }}" class="size-4 animate-spin" />
                <span wire:loading.remove wire:target="{{ $confirmMethod }}">{{ $confirmLabel }}</span>
                @if($confirmLoadingLabel)
                    <span wire:loading wire:target="{{ $confirmMethod }}">{{ $confirmLoadingLabel }}</span>
                @endif
            </button>
        </div>
    </div>
</div>
