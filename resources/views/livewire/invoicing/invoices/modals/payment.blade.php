<div 
    x-show="showPaymentModal"
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
        class="relative w-full max-w-4xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl ring-1 ring-black/5 dark:border-zinc-800 dark:bg-zinc-900 dark:ring-white/10"
        x-show="showPaymentModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="showPaymentModal = false"
    >
        {{-- Header with gradient --}}
        <div class="flex items-start justify-between gap-4 border-b border-zinc-100 bg-zinc-50 px-6 py-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div>
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Record Payment</h3>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Choose payment method for this invoice</p>
            </div>

            <button 
                type="button"
                @click="showPaymentModal = false"
                class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                aria-label="Close"
            >
                <flux:icon name="x-mark" class="size-5" />
            </button>
        </div>

        {{-- Payment Summary Cards --}}
        <div class="px-6 py-5">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:gap-6">
                <span class="pt-1 text-sm font-medium text-zinc-700 dark:text-zinc-300 whitespace-nowrap">Payment Method</span>
                <div class="flex-1 space-y-2 text-sm text-zinc-700 dark:text-zinc-300">
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg px-1 py-1 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                        <input type="radio" wire:model="paymentType" value="manual" class="mt-0.5 h-4 w-4 border-zinc-300 text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700" />
                        <div class="min-w-0 whitespace-nowrap leading-snug">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">Manual Payment</span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400"> — Record payment received offline (cash/bank transfer).</span>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg px-1 py-1 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                        <input type="radio" wire:model="paymentType" value="xendit" class="mt-0.5 h-4 w-4 border-zinc-300 text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700" />
                        <div class="min-w-0 whitespace-nowrap leading-snug">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">Online Payment</span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400"> — Generate payment link + QR for customer to pay online.</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                <div x-show="$wire.paymentType === 'manual'" x-transition>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-2 flex items-center justify-between">
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Payment Amount <span class="text-red-500">*</span></span>
                                <button 
                                    type="button"
                                    wire:click="$set('paymentAmount', {{ $remainingAmount }})"
                                    class="text-xs font-medium text-zinc-700 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100"
                                >
                                    Pay Full Amount
                                </button>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium text-zinc-500 dark:text-zinc-400">Rp</span>
                                <input 
                                    type="number" 
                                    step="0.01"
                                    wire:model="paymentAmount"
                                    class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 pl-10 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                />
                            </div>
                            @error('paymentAmount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Payment Date <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="paymentDate" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100" />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Payment Method <span class="text-red-500">*</span></label>
                            <select wire:model="paymentMethod" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="check">Check</option>
                                <option value="e_wallet">E-Wallet</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Reference / Transaction ID</label>
                            <input type="text" wire:model="paymentReference" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100" />
                        </div>
                    </div>
                </div>

                <div x-show="$wire.paymentType === 'xendit'" x-transition>
                    @if(!$this->xenditConfigured)
                        <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                            <flux:icon name="exclamation-triangle" class="size-5 flex-shrink-0 text-amber-500 dark:text-amber-400" />
                            <div>
                                <p class="text-sm font-medium text-amber-800 dark:text-amber-300">Payment gateway not configured</p>
                                <p class="mt-1 text-xs text-amber-700 dark:text-amber-400">Please add your API keys in Settings → Payment Gateway to enable online payments.</p>
                            </div>
                        </div>
                    @elseif($invoice && $invoice->xendit_invoice_url && !in_array(strtolower((string) ($invoice->xendit_status ?? 'pending')), ['paid', 'expired'], true))
                        {{-- Active Payment Link --}}
                        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-gradient-to-br from-zinc-50 to-zinc-100/50 dark:border-zinc-700 dark:from-zinc-800/50 dark:to-zinc-900/50">
                            {{-- Status Header --}}
                            <div class="flex items-center justify-between border-b border-zinc-200 bg-white/50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/40">
                                        <flux:icon name="link" class="size-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Payment Link Active</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Share with customer to collect payment</p>
                                    </div>
                                </div>
                                @php
                                    $xenditStatus = strtolower((string) ($invoice->xendit_status ?? 'pending'));
                                    $statusColors = [
                                        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                        'paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                        'expired' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                    ];
                                @endphp
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusColors[$xenditStatus] ?? $statusColors['pending'] }}">
                                    @if($xenditStatus === 'pending')
                                        <span class="relative flex h-2 w-2">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                                            <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-500"></span>
                                        </span>
                                    @endif
                                    {{ ucfirst($xenditStatus) }}
                                </span>
                            </div>

                            <div class="grid gap-4 p-4 sm:grid-cols-5">
                                {{-- QR Code Section --}}
                                <div class="flex flex-col items-center justify-center sm:col-span-2">
                                    <div class="rounded-xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-600 dark:bg-zinc-800">
                                        <img 
                                            alt="Payment QR Code"
                                            class="h-40 w-40"
                                            src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($invoice->xendit_invoice_url) }}&margin=0"
                                        />
                                    </div>
                                    <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">Scan to pay</p>
                                </div>

                                {{-- Payment Details --}}
                                <div class="flex flex-col justify-center space-y-4 sm:col-span-3">
                                    {{-- Amount --}}
                                    <div class="rounded-lg bg-white/80 p-3 dark:bg-zinc-800/80">
                                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Amount Due</p>
                                        <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</p>
                                    </div>

                                    {{-- Payment URL --}}
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-zinc-500 dark:text-zinc-400">Payment URL</label>
                                        <div class="flex gap-2">
                                            <input 
                                                type="text" 
                                                readonly
                                                value="{{ $invoice->xendit_invoice_url }}"
                                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 focus:outline-none dark:border-zinc-600 dark:bg-zinc-950 dark:text-zinc-300"
                                            />
                                            <button 
                                                type="button" 
                                                x-data 
                                                x-on:click="navigator.clipboard.writeText('{{ $invoice->xendit_invoice_url }}')"
                                                class="flex items-center justify-center rounded-lg border border-zinc-200 bg-white px-3 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                                title="Copy URL"
                                            >
                                                <flux:icon name="clipboard" class="size-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Create Payment Link --}}
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6 text-center dark:border-zinc-800 dark:bg-zinc-900/50">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm dark:bg-zinc-800">
                                <flux:icon name="link" class="size-6 text-zinc-400" />
                            </div>
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Generate Payment Link</h3>
                            <p class="mx-auto mt-1 max-w-sm text-sm text-zinc-500 dark:text-zinc-400">Create a payment link to share with your customer. They can pay via bank transfer, credit card, or e-wallet.</p>
                            
                            <button 
                                type="button" 
                                wire:click="createXenditPayment"
                                wire:loading.attr="disabled"
                                class="mt-4 inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                            >
                                <flux:icon name="plus" wire:loading.remove wire:target="createXenditPayment" class="size-4" />
                                <flux:icon name="arrow-path" wire:loading wire:target="createXenditPayment" class="size-4 animate-spin" />
                                Create Payment Link
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900">
            <button 
                type="button"
                @click="showPaymentModal = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                Cancel
            </button>
            
            @if($paymentType === 'manual')
                <button 
                    type="button"
                    wire:click="addPayment"
                    wire:loading.attr="disabled"
                    wire:target="addPayment"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    <flux:icon name="check" wire:loading.remove wire:target="addPayment" class="size-4" />
                    <flux:icon name="arrow-path" wire:loading wire:target="addPayment" class="size-4 animate-spin" />
                    Record Payment
                </button>
            @endif
        </div>
    </div>
</div>