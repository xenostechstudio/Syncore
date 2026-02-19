<div 
    x-show="showInvoiceModal" 
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-zinc-900/60" @click="showInvoiceModal = false"></div>
    
    {{-- Modal Content --}}
    <div 
        class="relative w-full max-w-4xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl ring-1 ring-black/5 dark:border-zinc-800 dark:bg-zinc-900 dark:ring-white/10"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="showInvoiceModal = false"
    >
        <div class="flex items-start justify-between gap-4 border-b border-zinc-100 bg-zinc-50/50 px-6 py-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div>
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Create Invoice</h3>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Choose payment type for this invoice</p>
            </div>

            <button 
                type="button"
                @click="showInvoiceModal = false"
                class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                aria-label="Close"
            >
                <flux:icon name="x-mark" class="size-5" />
            </button>
        </div>
        
        {{-- Payment Type Options --}}
        <div class="px-6 py-5">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:gap-6">
                <span class="pt-1 text-sm font-medium text-zinc-700 whitespace-nowrap dark:text-zinc-300">Payment Type</span>
                <div class="flex-1 space-y-2 text-sm text-zinc-700 dark:text-zinc-300">
                    {{-- Regular Payment --}}
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-transparent p-2 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <input type="radio" wire:model="invoiceType" value="regular" class="mt-0.5 h-4 w-4 border-zinc-300 text-zinc-900 focus:ring-zinc-900 dark:border-zinc-600 dark:bg-zinc-700 dark:checked:bg-zinc-100 dark:checked:border-zinc-100" />
                        <div class="min-w-0 whitespace-nowrap leading-snug">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">Regular (Full Amount)</span>
                            <span class="block text-xs text-zinc-500 dark:text-zinc-400">Create an invoice for the full order total.</span>
                        </div>
                    </label>

                    {{-- Down Payment (Percentage) --}}
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-transparent p-2 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <input type="radio" wire:model="invoiceType" value="down_payment_percentage" class="mt-0.5 h-4 w-4 border-zinc-300 text-zinc-900 focus:ring-zinc-900 dark:border-zinc-600 dark:bg-zinc-700 dark:checked:bg-zinc-100 dark:checked:border-zinc-100" />
                        <div class="min-w-0 whitespace-nowrap leading-snug">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">Down Payment (Percentage)</span>
                            <span class="block text-xs text-zinc-500 dark:text-zinc-400">Invoice a percentage of the order (e.g. 30%).</span>
                        </div>
                    </label>

                    {{-- Down Payment (Fixed Amount) --}}
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-transparent p-2 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <input type="radio" wire:model="invoiceType" value="down_payment_fixed" class="mt-0.5 h-4 w-4 border-zinc-300 text-zinc-900 focus:ring-zinc-900 dark:border-zinc-600 dark:bg-zinc-700 dark:checked:bg-zinc-100 dark:checked:border-zinc-100" />
                        <div class="min-w-0 whitespace-nowrap leading-snug">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">Down Payment (Fixed Amount)</span>
                            <span class="block text-xs text-zinc-500 dark:text-zinc-400">Invoice a specific amount now (e.g. Rp 5.000.000).</span>
                        </div>
                    </label>
                </div>
            </div>
            
            {{-- Dynamic Fields based on Type --}}
            <div class="mt-4 space-y-3 pl-0 sm:pl-[120px]">
                <div x-show="$wire.invoiceType === 'down_payment_percentage'" x-transition>
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Percentage</span>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.live="downPaymentPercentage" min="1" max="100" step="1" class="w-24 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-zinc-500" placeholder="%" />
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">%</span>
                        </div>
                    </div>
                </div>

                <div x-show="$wire.invoiceType === 'down_payment_fixed'" x-transition>
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Amount</span>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">Rp</span>
                            <input type="number" wire:model.live="downPaymentAmount" min="0" step="1000" class="w-40 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-zinc-500" placeholder="Amount" />
                        </div>
                    </div>
                </div>

                {{-- Invoice Amount Display --}}
                @php
                    $invoiceAmount = $this->total;
                    if ($invoiceType === 'down_payment_percentage') {
                        $invoiceAmount = $this->total * (($downPaymentPercentage ?? 0) / 100);
                    } elseif ($invoiceType === 'down_payment_fixed') {
                        $invoiceAmount = min($this->total, (float) ($downPaymentAmount ?? 0));
                    }
                @endphp

                <div class="mt-3 rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-800/50">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Invoice Amount</span>
                        <span class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($invoiceAmount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900">
            <button 
                type="button"
                @click="showInvoiceModal = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
            >
                Cancel
            </button>
            <button 
                type="button"
                wire:click="createInvoice"
                wire:loading.attr="disabled"
                wire:target="createInvoice"
                class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                <flux:icon name="document-text" wire:loading.remove wire:target="createInvoice" class="size-4" />
                <flux:icon name="arrow-path" wire:loading wire:target="createInvoice" class="size-4 animate-spin" />
                <span wire:loading.remove wire:target="createInvoice">Create Invoice Draft</span>
                <span wire:loading wire:target="createInvoice">Creating...</span>
            </button>
        </div>
    </div>
</div>