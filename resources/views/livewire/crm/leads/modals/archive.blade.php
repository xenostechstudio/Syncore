<div
    class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900 dark:ring-1 dark:ring-white/10"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    @click.outside="showArchiveModal = false"
>
    <div class="mb-4 flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
            <flux:icon name="archive-box" class="size-5 text-amber-600 dark:text-amber-400" />
        </div>
        <div>
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Archive Lead</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">The lead can be restored later.</p>
        </div>
    </div>

    <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">
        Archiving hides this lead from the active list while keeping the record intact. You can restore it from the "Archived" filter on the leads index.
    </p>

    <div class="flex justify-end gap-3">
        <button
            type="button"
            @click="showArchiveModal = false"
            class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
        >
            Cancel
        </button>
        <button
            type="button"
            wire:click="archive"
            wire:loading.attr="disabled"
            wire:target="archive"
            @click="showArchiveModal = false"
            class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-amber-700 disabled:opacity-50 dark:bg-amber-500 dark:hover:bg-amber-600"
        >
            <flux:icon name="archive-box" wire:loading.remove wire:target="archive" class="size-4" />
            <flux:icon name="arrow-path" wire:loading wire:target="archive" class="size-4 animate-spin" />
            <span wire:loading.remove wire:target="archive">Archive Lead</span>
            <span wire:loading wire:target="archive">Archiving...</span>
        </button>
    </div>
</div>
