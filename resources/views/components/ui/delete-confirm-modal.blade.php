@props([
    'show' => false,
    'validation' => [],
    'title' => 'Confirm Delete',
    'itemLabel' => 'items',
])

@php
    $canDelete = $validation['canDelete'] ?? [];
    $cannotDelete = $validation['cannotDelete'] ?? [];
    $totalSelected = $validation['totalSelected'] ?? 0;
@endphp

<div
    x-data="{ open: @entangle($attributes->wire('model')) }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100] overflow-y-auto"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm" @click="open = false; $wire.cancelDelete()"></div>

    {{-- Modal --}}
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div
            class="relative w-full max-w-lg overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="open = false; $wire.cancelDelete()"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                        <flux:icon name="exclamation-triangle" class="size-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $totalSelected }} {{ $itemLabel }} selected</p>
                    </div>
                </div>
                <button 
                    type="button" 
                    @click="open = false; $wire.cancelDelete()"
                    class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                >
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>

            {{-- Content --}}
            <div class="max-h-[60vh] overflow-y-auto px-6 py-4">
                @if(count($canDelete) > 0)
                    <div class="mb-4">
                        <div class="mb-2 flex items-center gap-2">
                            <flux:icon name="check-circle" class="size-4 text-emerald-500" />
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                {{ count($canDelete) }} {{ $itemLabel }} can be deleted
                            </span>
                        </div>
                        <div class="space-y-1 rounded-lg border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-800 dark:bg-emerald-900/20">
                            @foreach($canDelete as $item)
                                <div class="flex items-center gap-2 text-sm text-emerald-700 dark:text-emerald-300">
                                    <flux:icon name="check" class="size-3" />
                                    <span>{{ $item['name'] }}</span>
                                    @if(isset($item['sku']))
                                        <span class="text-emerald-500 dark:text-emerald-400">({{ $item['sku'] }})</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(count($cannotDelete) > 0)
                    <div>
                        <div class="mb-2 flex items-center gap-2">
                            <flux:icon name="x-circle" class="size-4 text-red-500" />
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                {{ count($cannotDelete) }} {{ $itemLabel }} cannot be deleted
                            </span>
                        </div>
                        <div class="space-y-2 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20">
                            @foreach($cannotDelete as $item)
                                <div class="text-sm">
                                    <div class="flex items-center gap-2 font-medium text-red-700 dark:text-red-300">
                                        <flux:icon name="x-mark" class="size-3" />
                                        <span>{{ $item['name'] }}</span>
                                    </div>
                                    <p class="ml-5 text-xs text-red-600 dark:text-red-400">{{ $item['reason'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(count($canDelete) === 0)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                        <div class="flex items-center gap-2">
                            <flux:icon name="exclamation-triangle" class="size-5 text-amber-600 dark:text-amber-400" />
                            <p class="text-sm font-medium text-amber-700 dark:text-amber-300">
                                No {{ $itemLabel }} can be deleted
                            </p>
                        </div>
                        <p class="mt-1 text-sm text-amber-600 dark:text-amber-400">
                            All selected {{ $itemLabel }} have restrictions that prevent deletion.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <button
                    type="button"
                    @click="open = false; $wire.cancelDelete()"
                    class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                >
                    Cancel
                </button>
                @if(count($canDelete) > 0)
                    <button
                        type="button"
                        wire:click="bulkDelete"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600"
                    >
                        Delete {{ count($canDelete) }} {{ $itemLabel }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
