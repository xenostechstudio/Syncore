@props([
    'show' => false,
    'title' => 'Confirm Deletion',
    'message' => 'Are you sure you want to delete this item?',
    'confirmLabel' => 'Delete',
    'cancelLabel' => 'Cancel',
    'confirmAction' => 'bulkDelete',
    'cancelAction' => 'cancelDelete',
    'maxWidth' => 'sm',
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        default => 'max-w-sm',
    };
@endphp

@if ($show)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-zinc-900/60" wire:click="{{ $cancelAction }}"></div>

        <div class="relative w-full {{ $maxWidthClass }} rounded-lg bg-white p-6 shadow-xl dark:bg-zinc-900">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $message }}</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button
                    type="button"
                    wire:click="{{ $cancelAction }}"
                    class="inline-flex items-center rounded-md border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
                >
                    {{ $cancelLabel }}
                </button>
                <button
                    type="button"
                    wire:click="{{ $confirmAction }}"
                    class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700"
                >
                    {{ $confirmLabel }}
                </button>
            </div>
        </div>
    </div>
@endif
