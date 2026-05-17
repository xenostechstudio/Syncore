<x-ui.confirm-modal show="showDuplicateModal" maxWidth="md">
    <x-slot:icon>
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
            <flux:icon name="document-duplicate" class="size-7" />
        </div>
    </x-slot:icon>

    <x-slot:title>
        Duplicate this order?
    </x-slot:title>

    <x-slot:description>
        A new draft will be created with the same customer and line items. You'll be redirected to the new draft to review and confirm.
    </x-slot:description>

    <x-slot:actions>
        <button
            type="button"
            @click="showDuplicateModal = false"
            class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
        >
            Keep Editing
        </button>

        <button
            type="button"
            wire:click="duplicate"
            wire:loading.attr="disabled"
            wire:target="duplicate"
            @click="showDuplicateModal = false"
            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        >
            <flux:icon name="document-duplicate" wire:loading.remove wire:target="duplicate" class="size-4" />
            <flux:icon name="arrow-path" wire:loading wire:target="duplicate" class="size-4 animate-spin" />
            Duplicate Order
        </button>
    </x-slot:actions>
</x-ui.confirm-modal>
