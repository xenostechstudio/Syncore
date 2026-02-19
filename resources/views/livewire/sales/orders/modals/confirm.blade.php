<div 
    class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900 dark:ring-1 dark:ring-white/10"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    @click.outside="showConfirmModal = false"
>
    <div class="mb-4 flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
            <flux:icon name="check-circle" class="size-5 text-zinc-600 dark:text-zinc-400" />
        </div>
        <div>
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Confirm Sales Order</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">This will convert the quotation to a sales order.</p>
        </div>
    </div>
    
    <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">
        Are you sure you want to confirm this order? This action will change the status to "Sales Order" and the order will be ready for processing.
    </p>
    
    <div class="flex justify-end gap-3">
        <button 
            type="button"
            @click="showConfirmModal = false"
            class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
        >
            Cancel
        </button>
        <button 
            type="button"
            wire:click="confirm"
            wire:loading.attr="disabled"
            wire:target="confirm"
            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        >
            <flux:icon name="check" wire:loading.remove wire:target="confirm" class="size-4" />
            <flux:icon name="arrow-path" wire:loading wire:target="confirm" class="size-4 animate-spin" />
            <span wire:loading.remove wire:target="confirm">Confirm Order</span>
            <span wire:loading wire:target="confirm">Confirming...</span>
        </button>
    </div>
</div>