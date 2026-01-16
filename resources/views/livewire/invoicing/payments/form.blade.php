<div>
    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">{{ session('error') }}</x-ui.alert>
        @endif
    </div>

    {{-- Header --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('invoicing.payments.index') }}" wire:navigate class="flex h-8 w-8 items-center justify-center rounded-md text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-4" />
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">{{ $paymentId ? ($payment_number ?? 'Payment') : 'New Payment' }}</span>
            </div>
            <div class="flex items-center gap-2">
                @if($paymentId)
                    <button 
                        type="button" 
                        wire:click="confirmDelete" 
                        wire:loading.attr="disabled"
                        wire:target="confirmDelete"
                        class="flex h-8 items-center gap-1.5 rounded-md px-3 text-sm text-red-600 transition-colors hover:bg-red-50 disabled:opacity-50 dark:text-red-400 dark:hover:bg-red-950"
                    >
                        <flux:icon name="trash" wire:loading.remove wire:target="confirmDelete" class="size-4" />
                        <flux:icon name="arrow-path" wire:loading wire:target="confirmDelete" class="size-4 animate-spin" />
                        <span>Delete</span>
                    </button>
                @endif
                <button 
                    type="button" 
                    wire:click="save" 
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="flex h-8 items-center gap-1.5 rounded-md bg-zinc-900 px-3 text-sm text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    <flux:icon name="check" wire:loading.remove wire:target="save" class="size-4" />
                    <flux:icon name="arrow-path" wire:loading wire:target="save" class="size-4 animate-spin" />
                    <span wire:loading.remove wire:target="save">Save</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Payment Details</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Invoice <span class="text-red-500">*</span></label>
                        <select wire:model.live="invoice_id" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" {{ $paymentId ? 'disabled' : '' }}>
                            <option value="">Select Invoice</option>
                            @foreach($invoices as $inv)
                                <option value="{{ $inv->id }}">{{ $inv->invoice_number }} - {{ $inv->customer?->name ?? 'N/A' }} (Rp {{ number_format($inv->total, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                        @error('invoice_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Payment Date <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="payment_date" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        @error('payment_date') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Amount <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-500">Rp</span>
                            <input type="number" wire:model="amount" step="0.01" min="0" class="w-full rounded-md border border-zinc-300 bg-white py-2 pl-10 pr-3 text-sm shadow-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        </div>
                        @error('amount') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        @if($selectedInvoice)<p class="mt-1 text-xs text-zinc-500">Remaining: Rp {{ number_format($invoiceRemaining, 0, ',', '.') }}</p>@endif
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Payment Method <span class="text-red-500">*</span></label>
                        <select wire:model="payment_method" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="check">Check</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Reference</label>
                        <input type="text" wire:model="reference" placeholder="Transaction ID, Check number" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Notes</label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            @if($selectedInvoice)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invoice</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-zinc-500">Number</span><span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $selectedInvoice->invoice_number }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Customer</span><span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $selectedInvoice->customer?->name ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Total</span><span class="font-medium">Rp {{ number_format($selectedInvoice->total, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Remaining</span><span class="font-medium text-amber-600">Rp {{ number_format($invoiceRemaining, 0, ',', '.') }}</span></div>
                </div>
            </div>
            @endif

            @if($paymentId)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Info</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-zinc-500">Payment #</span><span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $payment_number }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Created</span><span class="text-zinc-600 dark:text-zinc-400">{{ $createdAt }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Updated</span><span class="text-zinc-600 dark:text-zinc-400">{{ $updatedAt }}</span></div>
                </div>
            </div>

            <x-ui.chatter-buttons />
            <x-ui.chatter-forms :activities="$activities" />
            @endif
        </div>
    </div>

    {{-- Delete Modal --}}
    @isset($showDeleteConfirm)
    <x-ui.delete-modal :show="$showDeleteConfirm" title="Delete Payment" message="Are you sure you want to delete this payment? This will update the invoice balance." />
    @endisset
</div>
