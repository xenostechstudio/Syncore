<div
    class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900 dark:ring-1 dark:ring-white/10"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    @click.outside="showDeleteModal = false"
>
    <div class="mb-4 flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
            <flux:icon name="trash" class="size-5 text-red-600 dark:text-red-400" />
        </div>
        <div>
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Delete Quotation</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">This action cannot be undone.</p>
        </div>
    </div>

    <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">
        This quotation has not been confirmed into an order, so there is nothing to keep. The record will be permanently removed.
    </p>

    <div class="flex justify-end gap-3">
        <button
            type="button"
            @click="showDeleteModal = false"
            class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
        >
            Cancel
        </button>
        <button
            type="button"
            wire:click="delete"
            wire:loading.attr="disabled"
            wire:target="delete"
            @click="showDeleteModal = false"
            class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 disabled:opacity-50 dark:bg-red-500 dark:hover:bg-red-600"
        >
            <flux:icon name="trash" wire:loading.remove wire:target="delete" class="size-4" />
            <flux:icon name="arrow-path" wire:loading wire:target="delete" class="size-4 animate-spin" />
            <span wire:loading.remove wire:target="delete">Delete Quotation</span>
            <span wire:loading wire:target="delete">Deleting...</span>
        </button>
    </div>
</div>
