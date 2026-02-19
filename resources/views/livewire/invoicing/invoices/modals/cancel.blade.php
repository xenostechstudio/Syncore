<div 
    x-show="showCancelModal" 
    x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-zinc-900/60 p-4"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div 
        class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl ring-1 ring-black/5 dark:bg-zinc-900 dark:ring-white/10"
        x-show="showCancelModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="showCancelModal = false"
    >
        <div class="mb-4 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                <flux:icon name="x-circle" class="size-5 text-red-600 dark:text-red-400" />
            </div>
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Cancel Invoice</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">This action cannot be undone.</p>
            </div>
        </div>
        
        <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">
            Are you sure you want to cancel this invoice? The invoice will be marked as cancelled and cannot be modified.
        </p>
        
        <div class="flex justify-end gap-3">
            <button 
                type="button"
                @click="showCancelModal = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                Keep Invoice
            </button>
            <button 
                type="button"
                wire:click="cancel"
                wire:loading.attr="disabled"
                wire:target="cancel"
                class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 disabled:opacity-50 dark:bg-red-600 dark:hover:bg-red-700"
            >
                <flux:icon name="x-mark" wire:loading.remove wire:target="cancel" class="size-4" />
                <flux:icon name="arrow-path" wire:loading wire:target="cancel" class="size-4 animate-spin" />
                Cancel Invoice
            </button>
        </div>
    </div>
</div>
