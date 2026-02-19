<x-ui.confirm-modal show="showCancelModal" maxWidth="md">
    <x-slot:icon>
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
            <flux:icon name="exclamation-triangle" class="size-7" />
        </div>
    </x-slot:icon>

    <x-slot:title>
        Cancel this order?
    </x-slot:title>

    <x-slot:description>
        This action will cancel the order and cannot be undone.
    </x-slot:description>

    <x-slot:content>
        {{-- Warning Box --}}
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
            <div class="flex items-start gap-2">
                <flux:icon name="exclamation-circle" class="mt-0.5 size-4 flex-shrink-0 text-amber-600 dark:text-amber-400" />
                <p class="text-left text-xs text-amber-700 dark:text-amber-300">
                    Please review related documents before cancelling. Invoices and delivery orders may need to be handled separately.
                </p>
            </div>
        </div>

        {{-- Related Documents --}}
        @if($orderId && ($invoices->count() > 0 || $deliveries->count() > 0))
            <div class="mt-3 space-y-2">
                {{-- Invoices --}}
                @if($invoices->count() > 0)
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        @foreach($invoices as $invoice)
                            <a 
                                href="{{ route('invoicing.invoices.edit', $invoice->id) }}" 
                                wire:navigate
                                class="inline-flex items-center gap-2 rounded-lg border border-violet-200 bg-violet-50 px-3 py-1.5 text-sm transition-colors hover:bg-violet-100 dark:border-violet-800 dark:bg-violet-900/20 dark:hover:bg-violet-900/30"
                            >
                                <flux:icon name="document-text" class="size-4 text-violet-600 dark:text-violet-400" />
                                <span class="font-medium text-violet-700 dark:text-violet-400">{{ $invoice->invoice_number }}</span>
                                <x-ui.status-badge :status="$invoice->status" type="invoice" />
                                <flux:icon name="arrow-top-right-on-square" class="size-3.5 text-violet-400" />
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Delivery Orders --}}
                @if($deliveries->count() > 0)
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        @foreach($deliveries as $delivery)
                            <a 
                                href="{{ route('delivery.orders.edit', $delivery->id) }}" 
                                wire:navigate
                                class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm transition-colors hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/20 dark:hover:bg-blue-900/30"
                            >
                                <flux:icon name="truck" class="size-4 text-blue-600 dark:text-blue-400" />
                                <span class="font-medium text-blue-700 dark:text-blue-400">{{ $delivery->delivery_number }}</span>
                                <x-ui.status-badge :status="$delivery->status->value" type="delivery" />
                                <flux:icon name="arrow-top-right-on-square" class="size-3.5 text-blue-400" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </x-slot:content>

    <x-slot:actions>
        <button 
            type="button"
            @click="showCancelModal = false"
            class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
        >
            Keep Order
        </button>

        <button 
            type="button"
            wire:click="cancel"
            wire:loading.attr="disabled"
            wire:target="cancel"
            @click="showCancelModal = false"
            class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 disabled:opacity-50 dark:bg-red-500 dark:hover:bg-red-600"
        >
            <flux:icon name="x-mark" wire:loading.remove wire:target="cancel" class="size-4" />
            <flux:icon name="arrow-path" wire:loading wire:target="cancel" class="size-4 animate-spin" />
            Cancel Order
        </button>
    </x-slot:actions>
</x-ui.confirm-modal>