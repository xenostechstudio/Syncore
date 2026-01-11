<div x-data="{ activeTab: 'items', showLogNote: false, showSendMessage: false, showScheduleActivity: false, showCancelModal: false, showPreviewModal: false, showEmailModal: $wire.entangle('showEmailModal'), showInvoiceModal: $wire.entangle('showInvoiceModal'), showDeliveryModal: $wire.entangle('showDeliveryModal') }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            {{-- Left Group: Back Button, Title, Gear Dropdown --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.orders.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    {{-- Small module label --}}
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        Quotation
                    </span>

                    {{-- Order number + gear dropdown inline --}}
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $orderId ? ($orderNumber ?? 'SO-' . str_pad($orderId, 5, '0', STR_PAD_LEFT)) : 'New Quotation' }}
                        </span>

                        {{-- Header actions dropdown (Duplicate, Archive, Delete) --}}
                        <flux:dropdown position="bottom" align="start">
                            <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                <flux:icon name="cog-6-tooth" class="size-4" />
                            </button>

                            <flux:menu class="w-40">
                                @if($orderId)
                                <button type="button" wire:click="duplicate" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <flux:icon name="document-duplicate" class="size-4" />
                                    <span>Duplicate</span>
                                </button>
                                @endif
                                <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <flux:icon name="archive-box" class="size-4" />
                                    <span>Archive</span>
                                </button>
                                <flux:menu.separator />
                                <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <flux:icon name="trash" class="size-4" />
                                    <span>Delete</span>
                                </button>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </div>
            </div>

            {{-- Right Group: Related Documents (Delivery first, then Invoice) --}}
            @if($orderId && $status === \App\Enums\SalesOrderState::SALES_ORDER->value)
                <div class="flex items-center gap-2">
                    @foreach($deliveries as $delivery)
                        <a 
                            href="{{ route('delivery.orders.edit', $delivery->id) }}" 
                            wire:navigate
                            class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                        >
                            <flux:icon name="truck" class="size-4" />
                            <span>{{ $delivery->delivery_number }}</span>
                            @php
                                $deliveryStatusConfig = match($delivery->status->value) {
                                    'pending' => ['bg' => 'bg-zinc-200 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300'],
                                    'picked' => ['bg' => 'bg-blue-200 dark:bg-blue-800', 'text' => 'text-blue-700 dark:text-blue-300'],
                                    'in_transit' => ['bg' => 'bg-violet-200 dark:bg-violet-800', 'text' => 'text-violet-700 dark:text-violet-300'],
                                    'delivered' => ['bg' => 'bg-emerald-200 dark:bg-emerald-800', 'text' => 'text-emerald-700 dark:text-emerald-300'],
                                    'failed' => ['bg' => 'bg-red-200 dark:bg-red-800', 'text' => 'text-red-700 dark:text-red-300'],
                                    'returned' => ['bg' => 'bg-amber-200 dark:bg-amber-800', 'text' => 'text-amber-700 dark:text-amber-300'],
                                    'cancelled' => ['bg' => 'bg-red-200 dark:bg-red-800', 'text' => 'text-red-700 dark:text-red-300'],
                                    default => ['bg' => 'bg-zinc-200 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300'],
                                };
                            @endphp
                            <span class="rounded px-1.5 py-0.5 text-xs font-medium {{ $deliveryStatusConfig['bg'] }} {{ $deliveryStatusConfig['text'] }}">
                                {{ ucfirst(str_replace('_', ' ', $delivery->status->value)) }}
                            </span>
                        </a>
                    @endforeach

                    @foreach($invoices as $invoice)
                        <a 
                            href="{{ route('invoicing.invoices.edit', $invoice->id) }}" 
                            wire:navigate
                            class="inline-flex items-center gap-2 rounded-lg border border-violet-200 bg-violet-50 px-3 py-1.5 text-sm font-medium text-violet-700 transition-colors hover:bg-violet-100 dark:border-violet-800 dark:bg-violet-900/30 dark:text-violet-400 dark:hover:bg-violet-900/50"
                        >
                            <flux:icon name="document-text" class="size-4" />
                            <span>{{ $invoice->invoice_number }}</span>
                            @php
                                $invoiceStatusConfig = match($invoice->status) {
                                    'draft' => ['bg' => 'bg-zinc-200 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300'],
                                    'sent' => ['bg' => 'bg-blue-200 dark:bg-blue-800', 'text' => 'text-blue-700 dark:text-blue-300'],
                                    'partial' => ['bg' => 'bg-amber-200 dark:bg-amber-800', 'text' => 'text-amber-700 dark:text-amber-300'],
                                    'paid' => ['bg' => 'bg-emerald-200 dark:bg-emerald-800', 'text' => 'text-emerald-700 dark:text-emerald-300'],
                                    default => ['bg' => 'bg-zinc-200 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300'],
                                };
                            @endphp
                            <span class="rounded px-1.5 py-0.5 text-xs font-medium {{ $invoiceStatusConfig['bg'] }} {{ $invoiceStatusConfig['text'] }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </x-slot:header>

    {{-- Flash Messages & Validation Errors --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        @if(session('warning'))
            <x-ui.alert type="warning" :duration="6000">
                {{ session('warning') }}
            </x-ui.alert>
        @endif

        @if(session('info'))
            <x-ui.alert type="info" :duration="5000">
                {{ session('info') }}
            </x-ui.alert>
        @endif

        @if($errors->any())
            <x-ui.alert type="error" :duration="10000">
                <span class="font-medium">Please fix the following errors:</span>
                <ul class="mt-1 list-inside list-disc text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif
    </div>

    {{-- Action Buttons Bar --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50" x-data="{ showConfirmModal: false }">
        <div class="grid grid-cols-12 items-center gap-6">
            {{-- Left: Action Buttons (col-span-9 to align with card below) --}}
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    @if(!$orderId)
                        {{-- New Order: Show Save button (primary) --}}
                        <button 
                            type="button"
                            wire:click="save"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
                            <flux:icon name="document-check" class="size-4" />
                            Save
                        </button>
                    @elseif($status === 'quotation' || $status === 'draft' || $status === 'confirmed')
                        {{-- Draft/Confirmed: Show Confirm button (primary) --}}
                        <button 
                            type="button"
                            @click="showConfirmModal = true"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
                            <flux:icon name="check" class="size-4" />
                            Confirm
                        </button>
                        <button 
                            type="button"
                            wire:click="save"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        >
                            <flux:icon name="document-check" class="size-4" />
                            Save
                        </button>
                    @elseif($status === \App\Enums\SalesOrderState::SALES_ORDER->value)
                        {{-- Sales Order: Show Create Invoice button if there are items to invoice --}}
                        @if($order && $order->hasQuantityToInvoice())
                            <button 
                                type="button"
                                wire:click="openInvoiceModal"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                            >
                                <flux:icon name="document-text" class="size-4" />
                                Create Invoice
                                @if($order->total_quantity_to_invoice < $order->items->sum('quantity'))
                                    <span class="ml-1 rounded bg-white/20 px-1.5 py-0.5 text-xs">{{ $order->total_quantity_to_invoice }} left</span>
                                @endif
                            </button>
                        @endif
                        @if($order && $order->canCreateDeliveryOrder())
                            <button 
                                type="button"
                                wire:click="openDeliveryModal"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                            >
                                <flux:icon name="truck" class="size-4" />
                                Create Delivery
                                @if($order->total_quantity_to_deliver < $order->items->sum('quantity'))
                                    <span class="ml-1 rounded bg-white/20 px-1.5 py-0.5 text-xs">{{ $order->total_quantity_to_deliver }} left</span>
                                @endif
                            </button>
                        @endif
                        <button 
                            type="button"
                            wire:click="save"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        >
                            <flux:icon name="document-check" class="size-4" />
                            Save
                        </button>
                    @endif
                    @if($orderId)
                        <button 
                            type="button"
                            wire:click="openEmailModal"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        >
                            <flux:icon name="envelope" class="size-4" />
                            Send
                        </button>
                    @else
                        <button 
                            type="button"
                            disabled
                            class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-2 text-sm font-medium text-zinc-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-600"
                        >
                            <flux:icon name="envelope" class="size-4" />
                            Send
                        </button>
                    @endif
                    @if($orderId)
                        <button 
                            type="button"
                            @click="showPreviewModal = true; $wire.generatePreviewLink()"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        >
                            <flux:icon name="eye" class="size-4" />
                            Preview
                        </button>
                    @else
                        <button 
                            type="button"
                            disabled
                            class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-2 text-sm font-medium text-zinc-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-600"
                        >
                            <flux:icon name="eye" class="size-4" />
                            Preview
                        </button>
                    @endif
                    @if($orderId && $status !== 'cancelled')
                        <button 
                            type="button"
                            @click="showCancelModal = true"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20"
                        >
                            <flux:icon name="x-mark" class="size-4" />
                            Cancel Order
                        </button>
                    @endif
                </div>

                {{-- Stepper (Right side of col-span-9, same line as buttons) --}}
                @php
                    $steps = \App\Enums\SalesOrderState::steps();
                    $currentIndex = collect($steps)->search(fn($s) => $s['key'] === $status);
                    if ($currentIndex === false) $currentIndex = 0;
                    $isCancelled = $status === 'cancelled';
                @endphp
                @if($isCancelled)
                    <span class="inline-flex h-[38px] items-center rounded-lg bg-red-100 px-4 text-sm font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                        Cancelled
                    </span>
                @else
                    <div class="flex items-center">
                        @foreach($steps as $index => $step)
                            @php
                                $isActive = $index === $currentIndex;
                                $isCompleted = $index < $currentIndex;
                                $isPending = $index > $currentIndex;
                                $isFirst = $index === 0;
                                $isLast = $index === count($steps) - 1;
                            @endphp
                            <div class="relative flex items-center {{ !$isFirst ? '-ml-2' : '' }}" style="z-index: {{ count($steps) - $index }};">
                                <div class="relative flex h-[38px] items-center px-4
                                    {{ $isActive ? 'bg-violet-600 text-white' : '' }}
                                    {{ $isCompleted ? 'bg-emerald-500 text-white' : '' }}
                                    {{ $isPending ? 'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' : '' }}"
                                    style="clip-path: polygon({{ $isFirst ? '0 0' : '10px 0' }}, calc(100% - 10px) 0, 100% 50%, calc(100% - 10px) 100%, {{ $isFirst ? '0 100%' : '10px 100%' }}, {{ $isFirst ? '0 50%' : '0 100%, 10px 50%, 0 0' }});">
                                    <span class="flex items-center gap-1 text-sm font-medium whitespace-nowrap">
                                        @if($isCompleted)
                                            <flux:icon name="check" class="size-4" />
                                        @endif
                                        {{ $step['label'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Right: Chatter Icons (col-span-3 to align with right column below) --}}
            <div class="col-span-3 flex items-center justify-end gap-1">
                <x-ui.chatter-buttons :showMessage="false" />
            </div>
        </div>

        {{-- Confirm Modal --}}
        <div 
            x-show="showConfirmModal" 
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
            <div class="absolute inset-0 bg-black/50" @click="showConfirmModal = false"></div>
            
            {{-- Modal Content --}}
            <div 
                class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900"
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
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button"
                        wire:click="confirm"
                        @click="showConfirmModal = false"
                        class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        Confirm Order
                    </button>
                </div>
            </div>
        </div>

        {{-- Invoice Creation Modal --}}
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
                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 bg-zinc-50 px-6 py-5 dark:border-zinc-800 dark:bg-zinc-900/60">
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
                    <div class="flex items-start justify-between gap-6">
                        <span class="pt-1 text-sm font-medium text-zinc-700 dark:text-zinc-300 whitespace-nowrap">Payment Type</span>
                        <div class="flex-1 space-y-2 text-sm text-zinc-700 dark:text-zinc-300">
                            {{-- Regular Payment --}}
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg px-1 py-1 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                                <input type="radio" wire:model="invoiceType" value="regular" class="mt-0.5 h-4 w-4 border-zinc-300 text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700" />
                                <div class="min-w-0 whitespace-nowrap leading-snug">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">Regular (Full Amount)</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400"> — Create an invoice for the full order total.</span>
                                </div>
                            </label>

                            {{-- Down Payment (Percentage) --}}
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg px-1 py-1 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                                <input type="radio" wire:model="invoiceType" value="down_payment_percentage" class="mt-0.5 h-4 w-4 border-zinc-300 text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700" />
                                <div class="min-w-0 whitespace-nowrap leading-snug">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">Down Payment (Percentage)</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400"> — Invoice a percentage of the order (e.g. 30%).</span>
                                </div>
                            </label>

                            {{-- Down Payment (Fixed Amount) --}}
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg px-1 py-1 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                                <input type="radio" wire:model="invoiceType" value="down_payment_fixed" class="mt-0.5 h-4 w-4 border-zinc-300 text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700" />
                                <div class="min-w-0 whitespace-nowrap leading-snug">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">Down Payment (Fixed Amount)</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400"> — Invoice a specific amount now (e.g. Rp 5.000.000).</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div x-show="$wire.invoiceType === 'down_payment_percentage'" x-transition>
                            <div class="flex items-center gap-4">
                                <span class="pt-1 pr-6 text-sm font-medium text-zinc-700 dark:text-zinc-300">Percentage</span>
                                <div class="flex items-center gap-2">
                                    <input type="number" wire:model.live="downPaymentPercentage" min="1" max="100" step="1" class="w-24 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" placeholder="%" />
                                    <span class="text-sm text-zinc-500">%</span>
                                </div>
                            </div>
                        </div>

                        <div x-show="$wire.invoiceType === 'down_payment_fixed'" x-transition>
                            <div class="flex items-center gap-4">
                                <span class="pt-1 pr-6 text-sm font-medium text-zinc-700 dark:text-zinc-300">Amount</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-zinc-500">Rp</span>
                                    <input type="number" wire:model.live="downPaymentAmount" min="0" step="1000" class="w-40 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" placeholder="Amount" />
                                </div>
                            </div>
                        </div>

                        @php
                            $invoiceAmount = $this->total;
                            if ($invoiceType === 'down_payment_percentage') {
                                $invoiceAmount = $this->total * (($downPaymentPercentage ?? 0) / 100);
                            } elseif ($invoiceType === 'down_payment_fixed') {
                                $invoiceAmount = min($this->total, (float) ($downPaymentAmount ?? 0));
                            }
                        @endphp

                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                            <div class="flex items-center">
                                <span class="text-sm pr-4 text-zinc-600 dark:text-zinc-400">Invoice Amount</span>
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($invoiceAmount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <button 
                        type="button"
                        @click="showInvoiceModal = false"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button"
                        wire:click="createInvoice"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        <flux:icon name="document-text" class="size-4" />
                        Create Invoice Draft
                    </button>
                </div>
            </div>
        </div>

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
                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 bg-zinc-50 px-6 py-5 dark:border-zinc-800 dark:bg-zinc-900/60">
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
                            <select wire:model.live="deliveryWarehouseId" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                <option value="">Select warehouse...</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Delivery Date</label>
                            <input type="date" wire:model.live="deliveryDate" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Recipient Name</label>
                            <input type="text" wire:model.live="deliveryRecipientName" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" placeholder="Recipient name" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Recipient Phone</label>
                            <input type="text" wire:model.live="deliveryRecipientPhone" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" placeholder="Recipient phone" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Courier</label>
                            <input type="text" wire:model.live="deliveryCourier" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" placeholder="Courier" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Tracking Number</label>
                            <input type="text" wire:model.live="deliveryTrackingNumber" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" placeholder="Tracking number" />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Notes</label>
                            <textarea rows="3" wire:model.live="deliveryNotes" class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" placeholder="Notes..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <button 
                        type="button"
                        @click="showDeliveryModal = false"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button"
                        wire:click="createDeliveryOrder"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        <flux:icon name="truck" class="size-4" />
                        Create Delivery Draft
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        {{-- Two Column Layout: Form Left, History Right --}}
        <div class="grid gap-6 lg:grid-cols-12">
        {{-- Left Column: Main Form --}}
        <div class="lg:col-span-9">
            {{-- Unified Order Card --}}
            <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                {{-- Customer & Order Info Section --}}
                <div class="p-5">
                    {{-- Title inside card --}}
                    <h1 class="mb-5 text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $orderId ? ($orderNumber ?? 'Order #' . $orderId) : 'New' }}
                    </h1>
                    
                    <div class="grid gap-6 sm:grid-cols-2">
                        {{-- Customer Selection (Searchable) --}}
                        <div>
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Customer <span class="text-red-500">*</span></label>
                            @if($order && $order->isLocked())
                                {{-- Locked Customer Display --}}
                                <div class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-left text-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                                    @if($selectedCustomer)
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                {{ strtoupper(substr($selectedCustomer->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $selectedCustomer->name }}</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $selectedCustomer->email }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-zinc-400">No customer selected</span>
                                    @endif
                                    <flux:icon name="lock-closed" class="size-4 text-zinc-400" />
                                </div>
                            @else
                            <div class="relative" x-data="{ open: false, search: '' }">
                                <button 
                                    type="button"
                                    @click="open = !open; $nextTick(() => { if(open) $refs.customerSearch.focus() })"
                                    class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-left text-sm transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                >
                                    @if($selectedCustomer)
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                {{ strtoupper(substr($selectedCustomer->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $selectedCustomer->name }}</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $selectedCustomer->email }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-zinc-400">Select a customer...</span>
                                    @endif
                                    <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                </button>
                                <div 
                                    x-show="open" 
                                    @click.outside="open = false; search = ''"
                                    x-transition
                                    class="absolute left-0 top-full z-[100] mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                >
                                    {{-- Search Input --}}
                                    <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                        <input 
                                            type="text"
                                            x-ref="customerSearch"
                                            x-model="search"
                                            placeholder="Search customers..."
                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            @keydown.escape="open = false; search = ''"
                                        />
                                    </div>
                                    {{-- Customer List --}}
                                    <div class="max-h-60 overflow-auto py-1">
                                        @foreach($customers as $customer)
                                            <button 
                                                type="button"
                                                x-show="'{{ strtolower($customer->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($customer->email ?? '') }}'.includes(search.toLowerCase()) || search === ''"
                                                wire:click="$set('customer_id', {{ $customer->id }})"
                                                @click="open = false; search = ''"
                                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $customer_id === $customer->id ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}"
                                            >
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $customer->name }}</p>
                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $customer->email }}</p>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                            @error('customer_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Right Column: Expiration, Pricelist, Payment Terms --}}
                        <div class="space-y-3">
                            {{-- Expiration --}}
                            <div class="flex items-center gap-4">
                                <label class="w-28 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Expiration</label>
                                <div class="relative flex-1">
                                    <input 
                                        type="date" 
                                        wire:model="expected_delivery_date"
                                        class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                    />
                                </div>
                            </div>

                            {{-- Pricelist --}}
                            <div class="flex items-center gap-4" x-data="{ open: false }">
                                <label class="w-28 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Pricelist</label>
                                <div class="relative flex-1">
                                    <button 
                                        type="button"
                                        @click="open = !open"
                                        class="flex w-full items-center justify-between rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-left text-sm transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:hover:border-zinc-700"
                                    >
                                        <span class="{{ $pricelist ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400' }}">
                                            {{ $pricelist ? ucfirst($pricelist) . ' Price' : 'Select pricelist...' }}
                                        </span>
                                        <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                    </button>
                                    <div 
                                        x-show="open" 
                                        @click.outside="open = false"
                                        x-transition
                                        class="absolute left-0 top-full z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                    >
                                        <button type="button" wire:click="$set('pricelist', 'standard')" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">Standard Price</button>
                                        <button type="button" wire:click="$set('pricelist', 'wholesale')" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">Wholesale Price</button>
                                        <button type="button" wire:click="$set('pricelist', 'retail')" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">Retail Price</button>
                                    </div>
                                </div>
                            </div>

                            {{-- Payment Terms --}}
                            <div class="flex items-center gap-4" x-data="{ open: false }">
                                <label class="w-28 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Payment Terms</label>
                                <div class="relative flex-1">
                                    <button 
                                        type="button"
                                        @click="open = !open"
                                        class="flex w-full items-center justify-between rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-left text-sm transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:hover:border-zinc-700"
                                    >
                                        <span class="{{ $payment_terms ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400' }}">
                                            @php
                                                $paymentTermsLabels = [
                                                    'immediate' => 'Immediate Payment',
                                                    'net15' => 'Net 15 Days',
                                                    'net30' => 'Net 30 Days',
                                                    'net45' => 'Net 45 Days',
                                                    'net60' => 'Net 60 Days',
                                                ];
                                            @endphp
                                            {{ $payment_terms ? ($paymentTermsLabels[$payment_terms] ?? $payment_terms) : 'Select payment terms...' }}
                                        </span>
                                        <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                    </button>
                                    <div 
                                        x-show="open" 
                                        @click.outside="open = false"
                                        x-transition
                                        class="absolute left-0 top-full z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                    >
                                        <button type="button" wire:click="$set('payment_terms', 'immediate')" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">Immediate Payment</button>
                                        <button type="button" wire:click="$set('payment_terms', 'net15')" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">Net 15 Days</button>
                                        <button type="button" wire:click="$set('payment_terms', 'net30')" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">Net 30 Days</button>
                                        <button type="button" wire:click="$set('payment_terms', 'net45')" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">Net 45 Days</button>
                                        <button type="button" wire:click="$set('payment_terms', 'net60')" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">Net 60 Days</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab Headers: Order Lines & Other Info --}}
                <div class="flex items-center border-y border-zinc-100 dark:border-zinc-800">
                    <button 
                        type="button"
                        @click="activeTab = 'items'"
                        :class="activeTab === 'items' ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                        class="px-5 py-3 text-sm font-medium transition-colors"
                    >
                        Order Lines
                    </button>
                    <button 
                        type="button"
                        @click="activeTab = 'other'"
                        :class="activeTab === 'other' ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                        class="px-5 py-3 text-sm font-medium transition-colors"
                    >
                        Other Info
                    </button>
                    
                    {{-- Locked Indicator --}}
                    @if($order && $order->isLocked())
                        <div class="ml-auto mr-4 flex items-center gap-2 rounded-lg bg-amber-50 px-3 py-1.5 text-sm text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            <flux:icon name="lock-closed" class="size-4" />
                            <span>Order locked - has active invoices or deliveries</span>
                        </div>
                    @endif
                </div>
                
                {{-- Tab Content: Order Items --}}
                <div 
                    x-show="activeTab === 'items'" 
                    x-transition:enter="transition ease-out duration-200" 
                    x-transition:enter-start="opacity-0" 
                    x-transition:enter-end="opacity-100"
                    x-data="{
                        showColumnMenu: false,
                        columns: {
                            product: { label: 'Product', visible: true, required: true },
                            description: { label: 'Description', visible: false, required: false },
                            qty: { label: 'Quantity', visible: true, required: true },
                            unit_price: { label: 'Unit Price', visible: true, required: true },
                            discount: { label: 'Discount (%)', visible: false, required: false },
                            taxes: { label: 'Taxes', visible: true, required: false },
                            subtotal: { label: 'Subtotal', visible: true, required: true },
                            subtotal_after_tax: { label: 'After Tax', visible: false, required: false },
                        },
                        isColumnVisible(key) {
                            return this.columns[key] && this.columns[key].visible;
                        },
                    }"
                >
                    {{-- Items Table --}}
                    <div class="overflow-visible">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    <th class="w-10 px-2 py-2.5"></th>
                                    <th x-show="isColumnVisible('product')" class="w-[32rem] px-3 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Product</th>
                                    <th x-show="isColumnVisible('description')" class="px-3 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Description</th>
                                    <th x-show="isColumnVisible('discount')" class="w-20 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Disc %</th>
                                    <th x-show="isColumnVisible('qty')" class="w-16 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Qty</th>
                                    <th x-show="isColumnVisible('unit_price')" class="w-32 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unit Price</th>
                                    <th x-show="isColumnVisible('taxes')" class="w-28 px-3 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 whitespace-nowrap dark:text-zinc-400">Taxes</th>
                                    <th x-show="isColumnVisible('subtotal')" class="px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Subtotal</th>
                                    <th x-show="isColumnVisible('subtotal_after_tax')" class="px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 whitespace-nowrap dark:text-zinc-400">After Tax</th>
                                    <th class="w-10 pl-2 pr-2 py-2.5 text-right">
                                        {{-- Column Visibility Toggle --}}
                                        <div class="relative inline-flex items-center justify-end">
                                            <button 
                                                type="button"
                                                @click="showColumnMenu = !showColumnMenu"
                                                class="flex items-center justify-center rounded p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                                                title="Show/Hide Columns"
                                            >
                                                <flux:icon name="adjustments-horizontal" class="size-4" />
                                            </button>
                                            <div 
                                                x-show="showColumnMenu" 
                                                @click.outside="showColumnMenu = false"
                                                x-transition
                                                class="absolute right-0 top-full z-50 mt-1 w-48 rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                            >
                                                <template x-for="key in Object.keys(columns)" :key="key">
                                                    <label class="flex cursor-pointer items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                        <input 
                                                            type="checkbox" 
                                                            x-model="columns[key].visible"
                                                            :disabled="columns[key].required === true"
                                                            class="rounded border-zinc-300 text-violet-600 focus:ring-violet-500 disabled:opacity-50"
                                                        />
                                                        <span x-text="columns[key].label" :class="columns[key].required === true ? 'text-zinc-400' : ''"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody 
                                class="divide-y divide-zinc-50 dark:divide-zinc-800/50"
                                x-data="{
                                    dragging: null,
                                    dragOver: null,
                                    handleDragStart(e, index) {
                                        this.dragging = index;
                                        e.dataTransfer.effectAllowed = 'move';
                                        e.target.closest('tr').classList.add('opacity-50');
                                    },
                                    handleDragEnd(e) {
                                        e.target.closest('tr').classList.remove('opacity-50');
                                        if (this.dragging !== null && this.dragOver !== null && this.dragging !== this.dragOver) {
                                            $wire.reorderItems(this.dragging, this.dragOver);
                                        }
                                        this.dragging = null;
                                        this.dragOver = null;
                                    },
                                    handleDragOver(e, index) {
                                        e.preventDefault();
                                        this.dragOver = index;
                                    }
                                }"
                            >
                                @forelse($items as $index => $item)
                                    <tr 
                                        class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30" 
                                        wire:key="item-{{ $index }}"
                                        draggable="true"
                                        @dragstart="handleDragStart($event, {{ $index }})"
                                        @dragend="handleDragEnd($event)"
                                        @dragover="handleDragOver($event, {{ $index }})"
                                        :class="{ 'border-t-2 border-violet-500': dragOver === {{ $index }} && dragging !== {{ $index }} }"
                                    >
                                        {{-- Drag Handle --}}
                                        <td class="px-2 py-2">
                                            <div class="flex cursor-grab items-center justify-center text-zinc-300 transition-opacity hover:text-zinc-500 dark:text-zinc-600 dark:hover:text-zinc-400">
                                                <svg class="size-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                                                </svg>
                                            </div>
                                        </td>

                                                {{-- Product Selection (Searchable) --}}
                                                <td x-show="isColumnVisible('product')" class="w-[32rem] px-3 py-2 overflow-visible">
                                                    <div x-data="{ open: false, search: '' }" class="relative">
                                                        @if($item['product_id'])
                                                            <button 
                                                                type="button"
                                                                @click="open = true; $nextTick(() => $refs.productSearch{{ $index }}.focus())"
                                                                class="flex w-full items-center gap-2 text-left"
                                                            >
                                                                <div>
                                                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $item['name'] }}</p>
                                                                    <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $item['sku'] }}</p>
                                                                </div>
                                                            </button>
                                                        @else
                                                            <button 
                                                                type="button"
                                                                @click="open = true; $nextTick(() => $refs.productSearch{{ $index }}.focus())"
                                                                class="text-sm text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                                                            >
                                                                Select a product...
                                                            </button>
                                                        @endif

                                                        {{-- Product Dropdown --}}
                                                        <div 
                                                            x-show="open" 
                                                            @click.outside="open = false; search = ''"
                                                            x-transition
                                                            class="absolute left-0 top-full z-[200] mt-1 rounded-lg border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
                                                            x-init="$watch('open', value => {
                                                                if (value) {
                                                                    const cell = $el.closest('td');
                                                                    const rect = cell.getBoundingClientRect();
                                                                    $el.style.position = 'fixed';
                                                                    $el.style.top = (rect.bottom + 4) + 'px';
                                                                    $el.style.left = rect.left + 'px';
                                                                    $el.style.width = rect.width + 'px';
                                                                }
                                                            })"
                                                        >
                                                            <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                                                <input 
                                                                    type="text"
                                                                    x-ref="productSearch{{ $index }}"
                                                                    x-model="search"
                                                                    placeholder="Search products..."
                                                                    class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                                                    @keydown.escape="open = false; search = ''"
                                                                />
                                                            </div>
                                                            <div class="max-h-48 overflow-auto py-1">
                                                                @foreach($products as $invItem)
                                                                    <button 
                                                                        type="button"
                                                                        x-show="'{{ strtolower($invItem->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($invItem->sku ?? '') }}'.includes(search.toLowerCase()) || search === ''"
                                                                        wire:click="selectItem({{ $index }}, {{ $invItem->id }})"
                                                                        @click="open = false; search = ''"
                                                                        class="flex w-full items-center gap-3 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                                                    >
                                                                        <div class="flex-1">
                                                                            <p class="text-zinc-900 dark:text-zinc-100">{{ $invItem->name }}</p>
                                                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $invItem->sku }} · Rp {{ number_format($invItem->selling_price, 0, ',', '.') }}</p>
                                                                        </div>
                                                                        <span class="text-xs text-zinc-400">{{ $invItem->quantity }} in stock</span>
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                {{-- Description --}}
                                                <td x-show="isColumnVisible('description')" class="px-3 py-2">
                                                    <input 
                                                        type="text"
                                                        wire:model.live="items.{{ $index }}.description"
                                                        placeholder="Add description..."
                                                        class="w-full bg-transparent text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none dark:text-zinc-100"
                                                    />
                                                </td>

                                                {{-- Discount --}}
                                                <td x-show="isColumnVisible('discount')" class="px-3 py-2">
                                                    <input 
                                                        type="text"
                                                        wire:model.live="items.{{ $index }}.discount"
                                                        class="w-full bg-transparent text-right text-sm text-zinc-900 focus:outline-none dark:text-zinc-100"
                                                    />
                                                </td>

                                                {{-- Quantity --}}
                                                <td x-show="isColumnVisible('qty')" class="w-16 px-3 py-2">
                                                    <input 
                                                        type="text"
                                                        wire:model.live="items.{{ $index }}.quantity"
                                                        class="w-full bg-transparent text-right text-sm text-zinc-900 focus:outline-none dark:text-zinc-100"
                                                    />
                                                </td>

                                                {{-- Unit Price --}}
                                                <td x-show="isColumnVisible('unit_price')" class="w-32 px-3 py-2">
                                                    <input 
                                                        type="text"
                                                        wire:model.live="items.{{ $index }}.unit_price"
                                                        class="w-full bg-transparent text-right text-sm text-zinc-900 focus:outline-none dark:text-zinc-100"
                                                    />
                                                </td>

                                        {{-- Taxes --}}
                                        <td x-show="isColumnVisible('taxes')" class="w-28 px-3 py-2" x-data="{ open: false }">
                                            <div class="relative">
                                                @php
                                                    $selectedTax = isset($item['tax_id']) ? $taxes->firstWhere('id', $item['tax_id']) : null;
                                                @endphp
                                                <button 
                                                    type="button"
                                                    @click="open = !open"
                                                    class="flex w-full justify-start text-xs text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300"
                                                >
                                                    @if($selectedTax)
                                                        <span class="inline-flex max-w-full items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                                                            <span class="truncate">
                                                                {{ $selectedTax->code ?? $selectedTax->name }}
                                                                @if($selectedTax->type === 'percentage')
                                                                    {{ ' ' . $selectedTax->formatted_rate }}
                                                                @endif
                                                            </span>
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-zinc-400">No Tax</span>
                                                    @endif
                                                </button>
                                                <div 
                                                    x-show="open" 
                                                    @click.outside="open = false"
                                                    x-transition
                                                    class="absolute right-0 top-full z-50 mt-1 w-52 rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                                >
                                                    <button type="button" wire:click="$set('items.{{ $index }}.tax_id', null)" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">No Tax</button>
                                                    @foreach($taxes as $tax)
                                                        <button 
                                                            type="button" 
                                                            wire:click="$set('items.{{ $index }}.tax_id', {{ $tax->id }})" 
                                                            @click="open = false" 
                                                            class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                                        >
                                                            {{ $tax->code ?? $tax->name }}@if($tax->type === 'percentage') {{ ' · ' . $tax->formatted_rate }}@endif
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </td>

                                        <td x-show="isColumnVisible('subtotal')" class="px-3 py-2 text-right">
                                            <span class="text-sm text-zinc-900 dark:text-zinc-100">Rp {{ number_format($item['total'], 0, ',', '.') }}</span>
                                        </td>

                                        <td x-show="isColumnVisible('subtotal_after_tax')" class="px-3 py-2 text-right">
                                            @php
                                                $lineBase = (float) ($item['total'] ?? 0);
                                                $lineTax = 0.0;

                                                if (! empty($item['tax_id'])) {
                                                    $lineTaxModel = $taxes->firstWhere('id', $item['tax_id']);

                                                    if ($lineTaxModel) {
                                                        if ($lineTaxModel->type === 'percentage') {
                                                            $lineTax = $lineBase * ((float) $lineTaxModel->rate / 100);
                                                        } else {
                                                            $lineTax = (float) $lineTaxModel->rate;
                                                        }
                                                    }
                                                }

                                                $lineAfterTax = $lineBase + $lineTax;
                                            @endphp

                                            <span class="text-sm text-zinc-900 dark:text-zinc-100">Rp {{ number_format($lineAfterTax, 0, ',', '.') }}</span>
                                        </td>

                                        {{-- Remove --}}
                                        <td class="pl-2 pr-2 py-2 text-right">
                                            @if(count($items) > 1 && (!$order || !$order->isLocked()))
                                                <button 
                                                    type="button"
                                                    wire:click="removeItem({{ $index }})"
                                                    class="rounded p-1 text-zinc-300 opacity-0 transition-all hover:text-red-500 group-hover:opacity-100 dark:text-zinc-600 dark:hover:text-red-400"
                                                >
                                                    <flux:icon name="trash" class="size-4" />
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-sm text-zinc-400">
                                            No items added yet
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Add Line Button + Items Error --}}
                    <div class="border-t border-zinc-100 px-4 py-3 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            @if($order && $order->isLocked())
                                <span class="inline-flex cursor-not-allowed items-center gap-1.5 text-sm text-zinc-300 dark:text-zinc-600">
                                    <flux:icon name="lock-closed" class="size-4" />
                                    Items locked
                                </span>
                            @else
                                <button 
                                    type="button"
                                    wire:click="addItem"
                                    class="inline-flex items-center gap-1.5 text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                                >
                                    <flux:icon name="plus" class="size-4" />
                                    Add a line
                                </button>
                            @endif
                            @error('items') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Terms & Totals Row --}}
                    <div class="border-t border-zinc-100 bg-zinc-50/50 p-5 dark:border-zinc-800 dark:bg-zinc-900/30">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            {{-- Terms & Conditions (Left Side) --}}
                            <div class="flex-1">
                                <textarea 
                                    wire:model="terms"
                                    rows="3"
                                    placeholder="Terms & Conditions"
                                    class="w-full resize-none border-0 bg-transparent px-0 py-0 text-sm text-zinc-700 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-300 dark:placeholder-zinc-500"
                                ></textarea>
                            </div>

                            {{-- Totals (Right Side) --}}
                            <div class="w-full space-y-2 lg:w-72">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-500 dark:text-zinc-400">Untaxed Amount</span>
                                    <span class="text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-500 dark:text-zinc-400">Taxes</span>
                                    <span class="text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->tax, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between border-t border-zinc-200 pt-2 dark:border-zinc-700">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">Total</span>
                                    <span class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab Content: Other Info --}}
                <div x-show="activeTab === 'other'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="p-6">
                        <div class="grid gap-8 lg:grid-cols-2">
                            {{-- Sales Section --}}
                            <div class="space-y-4">
                                <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Sales</h3>
                                <div class="space-y-4">
                                    {{-- Salesperson (Searchable) --}}
                                    <div x-data="{ 
                                        open: false, 
                                        search: '', 
                                        selected: '{{ auth()->user()->name ?? '' }}',
                                        users: [
                                            { id: {{ auth()->id() ?? 1 }}, name: '{{ auth()->user()->name ?? 'Current User' }}' }
                                        ],
                                        get filtered() {
                                            return this.users.filter(u => u.name.toLowerCase().includes(this.search.toLowerCase()));
                                        }
                                    }">
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Salesperson</label>
                                        <div class="relative">
                                            <button 
                                                type="button"
                                                @click="open = !open"
                                                class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-3 py-2 text-left text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            >
                                                <span x-text="selected || 'Select salesperson...'"></span>
                                                <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-transition class="absolute left-0 top-full z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                                <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                                    <input type="text" x-model="search" placeholder="Search..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                                </div>
                                                <div class="max-h-40 overflow-auto py-1">
                                                    <template x-for="user in filtered" :key="user.id">
                                                        <button type="button" @click="selected = user.name; open = false" class="flex w-full items-center px-3 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800" x-text="user.name"></button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Sales Team (Searchable) --}}
                                    <div x-data="{ 
                                        open: false, 
                                        search: '', 
                                        selected: '',
                                        teams: [
                                            { id: 1, name: 'Direct Sales' },
                                            { id: 2, name: 'Online Sales' },
                                            { id: 3, name: 'Retail' },
                                            { id: 4, name: 'Enterprise' },
                                            { id: 5, name: 'Partners' }
                                        ],
                                        get filtered() {
                                            return this.teams.filter(t => t.name.toLowerCase().includes(this.search.toLowerCase()));
                                        }
                                    }">
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Sales Team</label>
                                        <div class="relative">
                                            <button 
                                                type="button"
                                                @click="open = !open"
                                                class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-3 py-2 text-left text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            >
                                                <span x-text="selected || 'Select sales team...'"></span>
                                                <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-transition class="absolute left-0 top-full z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                                <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                                    <input type="text" x-model="search" placeholder="Search..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                                </div>
                                                <div class="max-h-40 overflow-auto py-1">
                                                    <template x-for="team in filtered" :key="team.id">
                                                        <button type="button" @click="selected = team.name; open = false" class="flex w-full items-center px-3 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800" x-text="team.name"></button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Customer Reference</label>
                                        <input type="text" placeholder="Customer PO number..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Tags</label>
                                        <input type="text" placeholder="Add tags..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </div>
                                </div>
                            </div>

                            {{-- Invoicing Section --}}
                            <div class="space-y-4">
                                <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invoicing</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Fiscal Position</label>
                                        <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            <option value="">Automatic</option>
                                            <option value="1">Domestic</option>
                                            <option value="2">Export</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Payment Terms</label>
                                        <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            <option value="">Immediate Payment</option>
                                            <option value="1">15 Days</option>
                                            <option value="2">30 Days</option>
                                            <option value="3">End of Month</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Delivery Section --}}
                            <div class="space-y-4">
                                <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Delivery</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Shipping Policy</label>
                                        <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            <option value="1">As soon as possible</option>
                                            <option value="2">When all products are ready</option>
                                        </select>
                                    </div>

                                    {{-- Delivery Method (Searchable) --}}
                                    <div x-data="{ 
                                        open: false, 
                                        search: '', 
                                        selected: '',
                                        methods: [
                                            { id: 1, name: 'Free Delivery' },
                                            { id: 2, name: 'Standard Shipping' },
                                            { id: 3, name: 'Express Shipping' },
                                            { id: 4, name: 'Same Day Delivery' },
                                            { id: 5, name: 'Pick Up' }
                                        ],
                                        get filtered() {
                                            return this.methods.filter(m => m.name.toLowerCase().includes(this.search.toLowerCase()));
                                        }
                                    }">
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Delivery Method</label>
                                        <div class="relative">
                                            <button 
                                                type="button"
                                                @click="open = !open"
                                                class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-3 py-2 text-left text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            >
                                                <span x-text="selected || 'Select delivery method...'"></span>
                                                <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-transition class="absolute left-0 top-full z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                                <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                                    <input type="text" x-model="search" placeholder="Search..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                                </div>
                                                <div class="max-h-40 overflow-auto py-1">
                                                    <template x-for="method in filtered" :key="method.id">
                                                        <button type="button" @click="selected = method.name; open = false" class="flex w-full items-center px-3 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800" x-text="method.name"></button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Incoterm (Searchable) --}}
                                    <div x-data="{ 
                                        open: false, 
                                        search: '', 
                                        selected: '',
                                        incoterms: [
                                            { id: 1, name: 'EXW - Ex Works' },
                                            { id: 2, name: 'FCA - Free Carrier' },
                                            { id: 3, name: 'CPT - Carriage Paid To' },
                                            { id: 4, name: 'CIP - Carriage and Insurance Paid To' },
                                            { id: 5, name: 'DAP - Delivered at Place' },
                                            { id: 6, name: 'DPU - Delivered at Place Unloaded' },
                                            { id: 7, name: 'DDP - Delivered Duty Paid' },
                                            { id: 8, name: 'FAS - Free Alongside Ship' },
                                            { id: 9, name: 'FOB - Free on Board' },
                                            { id: 10, name: 'CFR - Cost and Freight' },
                                            { id: 11, name: 'CIF - Cost, Insurance & Freight' }
                                        ],
                                        get filtered() {
                                            return this.incoterms.filter(i => i.name.toLowerCase().includes(this.search.toLowerCase()));
                                        }
                                    }">
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Incoterm</label>
                                        <div class="relative">
                                            <button 
                                                type="button"
                                                @click="open = !open"
                                                class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-3 py-2 text-left text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            >
                                                <span x-text="selected || 'Select incoterm...'"></span>
                                                <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-transition class="absolute left-0 top-full z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                                <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                                    <input type="text" x-model="search" placeholder="Search..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                                </div>
                                                <div class="max-h-40 overflow-auto py-1">
                                                    <template x-for="incoterm in filtered" :key="incoterm.id">
                                                        <button type="button" @click="selected = incoterm.name; open = false" class="flex w-full items-center px-3 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800" x-text="incoterm.name"></button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tracking Section --}}
                            <div class="space-y-4">
                                <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tracking</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Source Document</label>
                                        <input type="text" placeholder="Reference of the source document..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Activity Timeline (No Card) --}}
        <div class="lg:col-span-3">
            {{-- Chatter Forms (Log Note, Schedule Activity) --}}
            <x-ui.chatter-forms :showMessage="false" />

            {{-- Activity Timeline --}}
            @if($orderId)
                {{-- Date Separator --}}
                <div class="flex items-center gap-3 py-2">
                    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                        @if($activities->isNotEmpty() && $activities->first()['created_at']->isToday())
                            Today
                        @else
                            Activity
                        @endif
                    </span>
                    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                </div>

                {{-- Activity Items --}}
                <div class="space-y-4">
                    @forelse($activities as $item)
                        @if($item['type'] === 'note')
                            {{-- Note Item --}}
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <x-ui.user-avatar :user="$item['data']->user" size="md" :showPopup="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-ui.user-name :user="$item['data']->user" />
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">
                                            {{ $item['created_at']->diffForHumans() }}
                                        </span>
                                    </div>
                                    <div class="mt-1 rounded-lg bg-amber-50 px-3 py-2 text-sm text-zinc-700 dark:bg-amber-900/20 dark:text-zinc-300">
                                        <div class="flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400 mb-1">
                                            <flux:icon name="pencil-square" class="size-3" />
                                            <span>Internal Note</span>
                                        </div>
                                        {{ $item['data']->content }}
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Activity Log Item --}}
                            <x-ui.activity-item :activity="$item['data']" emptyMessage="Sales order created" />
                        @endif
                    @empty
                        {{-- Order Created (fallback when no activities yet) --}}
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <x-ui.user-name :user="auth()->user()" />
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $createdAt ?? now()->format('H:i') }}</span>
                                </div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('activity.sales_order_created') }}</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            @else
                {{-- Empty State for New Order --}}
                <div class="py-8 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="chat-bubble-left-right" class="size-6 text-zinc-400" />
                    </div>
                    <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No activity yet</p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500">Activity will appear here once you save</p>
                </div>
            @endif
        </div>
        </div>
    </div>

    {{-- Send Email Modal (Odoo-style) --}}
    <div
        x-show="showEmailModal"
        x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-black/50 p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            class="relative w-full max-w-3xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-zinc-900"
            x-show="showEmailModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="showEmailModal = false"
        >
            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-3 dark:border-zinc-800">
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Compose Email</h3>
                <button 
                    type="button"
                    @click="showEmailModal = false"
                    class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                >
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="px-5 py-3">
                {{-- Recipients Row --}}
                <div class="flex items-start gap-3 py-2">
                    <label class="w-20 shrink-0 pt-1.5 text-sm text-zinc-500 dark:text-zinc-400">Recipients</label>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            @foreach($emailRecipients as $index => $recipient)
                                <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2 py-1 text-sm text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $recipient }}
                                    <button 
                                        type="button" 
                                        wire:click="removeEmailRecipient({{ $index }})"
                                        class="rounded-full p-0.5 text-zinc-400 transition-colors hover:bg-zinc-200 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
                                    >
                                        <flux:icon name="x-mark" class="size-3.5" />
                                    </button>
                                </span>
                            @endforeach
                            <input 
                                type="email" 
                                wire:model="emailRecipientInput"
                                wire:keydown.enter.prevent="addEmailRecipient"
                                wire:keydown.tab.prevent="addEmailRecipient"
                                placeholder="{{ empty($emailRecipients) ? 'Add recipient email...' : 'Add more...' }}"
                                class="min-w-[150px] flex-1 border-0 bg-transparent px-0 py-1 text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:placeholder-zinc-500"
                            />
                        </div>
                        @if($emailRecipientError)
                            <p class="mt-1 text-xs text-red-500">{{ $emailRecipientError }}</p>
                        @endif
                        @error('emailRecipients')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Subject Row --}}
                <div class="flex items-center gap-3 border-t border-zinc-100 py-2 dark:border-zinc-800">
                    <label class="w-20 shrink-0 text-sm text-zinc-500 dark:text-zinc-400">Subject</label>
                    <input 
                        type="text" 
                        wire:model="emailSubject"
                        placeholder="Email subject"
                        class="flex-1 border-0 bg-transparent px-0 py-1 text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:placeholder-zinc-500"
                    />
                </div>
                @error('emailSubject')
                    <p class="ml-[92px] text-xs text-red-500">{{ $message }}</p>
                @enderror

                {{-- Message Body --}}
                <div class="border-t border-zinc-100 pt-3 dark:border-zinc-800">
                    <textarea 
                        wire:model="emailBody"
                        rows="12"
                        placeholder="Write your message here..."
                        class="w-full resize-none border-0 bg-transparent px-0 py-1 text-sm leading-relaxed text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:placeholder-zinc-500"
                    ></textarea>
                </div>
                @error('emailBody')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror

                {{-- Attachments --}}
                <div class="flex items-center gap-3 border-t border-zinc-100 py-3 dark:border-zinc-800">
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 transition-colors hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700">
                        <input 
                            type="checkbox" 
                            wire:model="emailAttachPdf"
                            class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900 dark:border-zinc-600 dark:bg-zinc-700"
                        />
                        <flux:icon name="paper-clip" class="size-4 text-zinc-500" />
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ in_array($status, ['draft', 'confirmed']) ? 'Quotation' : 'Sales Order' }} - {{ $orderNumber ?? 'Order' }}.pdf</span>
                    </label>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-2 border-t border-zinc-100 px-5 py-3 dark:border-zinc-800">
                <button 
                    type="button"
                    @click="showEmailModal = false"
                    class="rounded-lg px-4 py-2 text-sm font-medium text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                >
                    Discard
                </button>

                <button 
                    type="button"
                    wire:click="sendEmail"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    <flux:icon name="paper-airplane" class="size-4" wire:loading.remove wire:target="sendEmail" />
                    <flux:icon name="arrow-path" class="size-4 animate-spin" wire:loading wire:target="sendEmail" />
                    <span>Send</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Preview Modal --}}
    <div
        x-show="showPreviewModal"
        x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-black/50 p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-zinc-900"
            x-show="showPreviewModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="showPreviewModal = false"
        >
            <div class="flex items-start justify-between gap-4 border-b border-zinc-100 bg-zinc-50 px-6 py-5 dark:border-zinc-800 dark:bg-zinc-900/60">
                <div>
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Customer Preview</h3>
                    <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Share this link with your customer to view and confirm the order.</p>
                </div>

                <button 
                    type="button"
                    @click="showPreviewModal = false"
                    class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                    aria-label="Close"
                >
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>

            <div class="px-6 py-5">
                <div class="space-y-4">
                    @if($previewLink)
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Public link</label>
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-stretch">
                                <input type="text" readonly value="{{ $previewLink }}" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                <button type="button" x-data x-on:click="navigator.clipboard.writeText('{{ $previewLink }}')" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 sm:w-auto dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                                    <flux:icon name="clipboard" class="size-4" />
                                    Copy
                                </button>
                            </div>
                            @if($orderId)
                                @php
                                    $orderForExpiry = \App\Models\Sales\SalesOrder::find($orderId);
                                @endphp
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Link expires {{ optional($orderForExpiry?->share_token_expires_at)->diffForHumans() ?? 'in 30 days' }}.</p>
                            @endif
                        </div>
                    @else
                        <div class="flex items-center justify-center py-4">
                            <flux:icon name="arrow-path" class="size-5 animate-spin text-zinc-400" />
                            <span class="ml-2 text-sm text-zinc-500">Generating link...</span>
                        </div>
                    @endif

                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-zinc-300">
                        Your customer can view order details and confirm the quotation from this link.
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                <button 
                    type="button"
                    wire:click="refreshPreviewLink"
                    class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                >
                    Regenerate Link
                </button>

                <button 
                    type="button"
                    @click="showPreviewModal = false"
                    class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                >
                    Close
                </button>

                @if($previewLink)
                    <button 
                        type="button"
                        onclick="window.open('{{ $previewLink }}', '_blank')"
                        class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        View as Customer
                    </button>
                @else
                    <button 
                        type="button"
                        disabled
                        class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white opacity-50"
                    >
                        View as Customer
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Cancel Order Confirmation Modal --}}
    <x-ui.confirm-modal show="showCancelModal">
        <x-slot:icon>
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                <flux:icon name="exclamation-triangle" class="size-7" />
            </div>
        </x-slot:icon>

        <x-slot:title>
            Cancel this order?
        </x-slot:title>

        <x-slot:description>
            This action will cancel the order and cannot be undone. Are you sure you want to proceed?
        </x-slot:description>

        <x-slot:actions>
            <button 
                type="button"
                @click="showCancelModal = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                Keep Order
            </button>

            <button 
                type="button"
                wire:click="cancel"
                @click="showCancelModal = false"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600"
            >
                Cancel Order
            </button>
        </x-slot:actions>
    </x-ui.confirm-modal>
</div>
