<div 
    x-show="showDeliveryModal" 
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div class="absolute inset-0 bg-zinc-900/60" @click="showDeliveryModal = false"></div>

    <div 
        class="relative w-full max-w-4xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl ring-1 ring-black/5 dark:border-zinc-800 dark:bg-zinc-900 dark:ring-white/10"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="showDeliveryModal = false"
    >
        <div class="flex items-start justify-between gap-4 border-b border-zinc-100 bg-zinc-50/50 px-6 py-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div>
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Create Delivery Order</h3>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Confirm delivery information before creating the delivery order</p>
            </div>

            <button 
                type="button"
                @click="showDeliveryModal = false"
                class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                aria-label="Close"
            >
                <flux:icon name="x-mark" class="size-5" />
            </button>
        </div>

        <div class="px-6 py-5">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Warehouse</label>
                    <select wire:model.live="deliveryWarehouseId" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-zinc-500">
                        <option value="">Select warehouse...</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Delivery Date</label>
                    <input type="date" wire:model.live="deliveryDate" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-zinc-500" />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Recipient Name</label>
                    <input type="text" wire:model.live="deliveryRecipientName" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-zinc-500" placeholder="Recipient name" />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Recipient Phone</label>
                    <input type="text" wire:model.live="deliveryRecipientPhone" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-zinc-500" placeholder="Recipient phone" />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Courier</label>
                    <input type="text" wire:model.live="deliveryCourier" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-zinc-500" placeholder="Courier" />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Tracking Number</label>
                    <input type="text" wire:model.live="deliveryTrackingNumber" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-zinc-500" placeholder="Tracking number" />
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Notes</label>
                    <textarea rows="3" wire:model.live="deliveryNotes" class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-zinc-500" placeholder="Notes..."></textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900">
            <button 
                type="button"
                @click="showDeliveryModal = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
            >
                Cancel
            </button>
            <button 
                type="button"
                wire:click="createDeliveryOrder"
                wire:loading.attr="disabled"
                wire:target="createDeliveryOrder"
                class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                <flux:icon name="truck" wire:loading.remove wire:target="createDeliveryOrder" class="size-4" />
                <flux:icon name="arrow-path" wire:loading wire:target="createDeliveryOrder" class="size-4 animate-spin" />
                <span wire:loading.remove wire:target="createDeliveryOrder">Create Delivery Draft</span>
                <span wire:loading wire:target="createDeliveryOrder">Creating...</span>
            </button>
        </div>
    </div>
</div>