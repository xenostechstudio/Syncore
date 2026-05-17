<x-ui.confirm-modal show="showDeleteModal" maxWidth="md">
    <x-slot:icon>
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
            <flux:icon name="trash" class="size-7" />
        </div>
    </x-slot:icon>

    <x-slot:title>
        Delete this quotation?
    </x-slot:title>

    <x-slot:description>
        This quotation has not been confirmed into an order, so there is nothing to keep. This action is permanent and cannot be undone.
    </x-slot:description>

    <x-slot:actions>
        <button
            type="button"
            @click="showDeleteModal = false"
            class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
        >
            Keep Quotation
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
            Delete Permanently
        </button>
    </x-slot:actions>
</x-ui.confirm-modal>
