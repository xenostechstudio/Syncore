@props([
    'count' => 0,
    'actions' => [],
])

<div class="flex items-center gap-2 animate-in fade-in slide-in-from-top-2 duration-200">
    {{-- Count Selected Button --}}
    <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
        <span>{{ $count }} selected</span>
        <flux:icon name="x-mark" class="size-3.5" />
    </button>

    @if($slot->isNotEmpty())
        <div class="h-5 w-px bg-zinc-200 dark:bg-zinc-700"></div>
        {{ $slot }}
    @endif
</div>
