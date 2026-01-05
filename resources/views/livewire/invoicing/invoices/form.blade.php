<div x-data="{ 
    activeTab: 'lines',
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false,
    showCancelModal: false,
    showShareModal: $wire.entangle('showShareModal')
}">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            {{-- Left Group: Back Button, Title with SO Link, Gear Dropdown --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('invoicing.invoices.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    {{-- Invoice / SO Number (clickable) --}}
                    <div class="flex items-center gap-1 text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        <span>Invoice</span>
                        @if($invoice && $invoice->salesOrder)
                            <span>/</span>
                            <a href="{{ route('sales.orders.edit', $invoice->salesOrder->id) }}" wire:navigate class="text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300">
                                {{ $invoice->salesOrder->order_number }}
                            </a>
                        @endif
                    </div>
                    {{-- Invoice Number and (SO Number) --}}
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $invoiceId ? ($invoice_number ?? 'Invoice') : 'New Invoice' }}
                            @if($invoice && $invoice->salesOrder)
                                <span class="text-zinc-400 dark:text-zinc-500">({{ $invoice->salesOrder->order_number }})</span>
                            @endif
                        </span>
                        <flux:dropdown position="bottom" align="start">
                            <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                <flux:icon name="cog-6-tooth" class="size-4" />
                            </button>
                            <flux:menu class="w-40">
                                <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <flux:icon name="document-duplicate" class="size-4" />
                                    <span>Duplicate</span>
                                </button>
                                @if($invoiceId)
                                <a href="{{ route('pdf.invoice', $invoiceId) }}" target="_blank" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <flux:icon name="arrow-down-tray" class="size-4" />
                                    <span>Download PDF</span>
                                </a>
                                @endif
                                <flux:menu.separator />
                                <button type="button" wire:click="delete" wire:confirm="Are you sure?" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <flux:icon name="trash" class="size-4" />
                                    <span>Delete</span>
                                </button>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </div>
            </div>

            {{-- Right Group: Sales Order Badge --}}
            @if($invoice && $invoice->salesOrder)
                <div class="flex items-center gap-2">
                    <a 
                        href="{{ route('sales.orders.edit', $invoice->salesOrder->id) }}" 
                        wire:navigate
                        class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm font-medium text-amber-700 transition-colors hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50"
                    >
                        <flux:icon name="shopping-cart" class="size-4" />
                        <span>{{ $invoice->salesOrder->order_number }}</span>
                        @php
                            $soStatus = $invoice->salesOrder->status;
                            $soStatusConfig = match($soStatus) {
                                'quotation', 'draft' => ['bg' => 'bg-zinc-200 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300'],
                                'confirmed', 'sales_order', 'processing' => ['bg' => 'bg-blue-200 dark:bg-blue-800', 'text' => 'text-blue-700 dark:text-blue-300'],
                                'shipped', 'in_progress' => ['bg' => 'bg-violet-200 dark:bg-violet-800', 'text' => 'text-violet-700 dark:text-violet-300'],
                                'delivered', 'done', 'paid' => ['bg' => 'bg-emerald-200 dark:bg-emerald-800', 'text' => 'text-emerald-700 dark:text-emerald-300'],
                                'cancelled', 'canceled' => ['bg' => 'bg-red-200 dark:bg-red-800', 'text' => 'text-red-700 dark:text-red-300'],
                                default => ['bg' => 'bg-zinc-200 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300'],
                            };
                        @endphp
                        <span class="rounded px-1.5 py-0.5 text-xs font-medium {{ $soStatusConfig['bg'] }} {{ $soStatusConfig['text'] }}">
                            {{ ucfirst(str_replace('_', ' ', $soStatus)) }}
                        </span>
                    </a>
                </div>
            @endif
        </div>
    </x-slot:header>

    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">
                {{ session('success') }}
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
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
            {{-- Left: Action Buttons (col-span-9) --}}
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    @if($invoiceId && $status !== 'paid' && $status !== 'cancelled')
                        <button type="button" wire:click="openPaymentModal" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <flux:icon name="banknotes" class="size-4" />
                            Add Payment
                        </button>
                    @endif
                    <button type="button" wire:click="save" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="document-check" class="size-4" />
                        Save
                    </button>
                    @if($invoiceId)
                        <a href="{{ route('pdf.invoice', $invoiceId) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            Download PDF
                        </a>
                    @endif
                    @if($invoiceId)
                        <button type="button" wire:click="openShareModal" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                            <flux:icon name="paper-airplane" class="size-4" />
                            Send
                        </button>
                    @endif
                    @if($invoiceId && $status !== 'cancelled')
                        <button type="button" @click="showCancelModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
                            <flux:icon name="x-mark" class="size-4" />
                            Cancel
                        </button>
                    @endif
                </div>

                {{-- Stepper --}}
                @php
                    $steps = [
                        ['key' => 'draft', 'label' => 'Draft'],
                        ['key' => 'sent', 'label' => 'Sent'],
                        ['key' => 'partial', 'label' => 'Partial'],
                        ['key' => 'paid', 'label' => 'Paid'],
                    ];
                    $currentIndex = collect($steps)->search(fn($s) => $s['key'] === $status);
                    if ($currentIndex === false) $currentIndex = 0;
                    $isCancelled = $status === 'cancelled';
                    $isOverdue = $status === 'overdue';
                @endphp
                @if($isCancelled)
                    <span class="inline-flex h-[38px] items-center rounded-lg bg-red-100 px-4 text-sm font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">Cancelled</span>
                @elseif($isOverdue)
                    <span class="inline-flex h-[38px] items-center rounded-lg bg-red-100 px-4 text-sm font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">Overdue</span>
                @else
                    <div class="flex items-center">
                        @foreach($steps as $index => $step)
                            @php
                                $isActive = $index === $currentIndex;
                                $isCompleted = $index < $currentIndex;
                                $isPending = $index > $currentIndex;
                                $isFirst = $index === 0;
                            @endphp
                            <div class="relative flex items-center {{ !$isFirst ? '-ml-2' : '' }}" style="z-index: {{ count($steps) - $index }};">
                                <div class="relative flex h-[38px] items-center px-4 {{ $isActive ? 'bg-violet-600 text-white' : '' }} {{ $isCompleted ? 'bg-emerald-500 text-white' : '' }} {{ $isPending ? 'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' : '' }}" style="clip-path: polygon({{ $isFirst ? '0 0' : '10px 0' }}, calc(100% - 10px) 0, 100% 50%, calc(100% - 10px) 100%, {{ $isFirst ? '0 100%' : '10px 100%' }}, {{ $isFirst ? '0 50%' : '0 100%, 10px 50%, 0 0' }});">
                                    <span class="flex items-center gap-1 text-sm font-medium whitespace-nowrap">
                                        @if($isCompleted)<flux:icon name="check" class="size-4" />@endif
                                        {{ $step['label'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Right: Chatter Icons (col-span-3) --}}
            <div class="col-span-3">
                <x-ui.chatter-buttons />
            </div>
        </div>

        <div 
            x-show="showShareModal" 
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="absolute inset-0 bg-zinc-900/60" @click="showShareModal = false"></div>

            <div 
                class="relative w-full max-w-4xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl ring-1 ring-black/5 dark:border-zinc-800 dark:bg-zinc-900 dark:ring-white/10"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.outside="showShareModal = false"
            >
                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 bg-zinc-50 px-6 py-5 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Send Invoice</h3>
                        <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Share the public link with your customer.</p>
                    </div>

                    <button 
                        type="button"
                        @click="showShareModal = false"
                        class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        aria-label="Close"
                    >
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <div class="px-6 py-5">
                    <div class="space-y-4">
                        @if($shareLink)
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Public link</label>
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-stretch">
                                    <input type="text" readonly value="{{ $shareLink }}" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    <button type="button" x-data x-on:click="navigator.clipboard.writeText('{{ $shareLink }}')" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 sm:w-auto dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800">
                                        <flux:icon name="clipboard" class="size-4" />
                                        Copy
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Link expires {{ optional(optional($invoice)->share_token_expires_at)->diffForHumans() ?? 'in 30 days' }}.</p>
                            </div>
                        @else
                            <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                                <flux:icon name="exclamation-triangle" class="size-5 flex-shrink-0 text-amber-500 dark:text-amber-400" />
                                <p class="text-sm font-medium text-amber-800 dark:text-amber-300">Please generate a link to share this invoice.</p>
                            </div>
                        @endif

                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-zinc-300">
                            Your customer can view invoice details and choose payment method.
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <button 
                        type="button"
                        wire:click="regenerateShareLink"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Regenerate Link
                    </button>

                    <button 
                        type="button"
                        @click="showShareModal = false"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Close
                    </button>

                    @if($shareLink)
                        <button 
                            type="button"
                            onclick="window.open('{{ $shareLink }}', '_blank')"
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
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left: Invoice Card --}}
            <div class="lg:col-span-9">
                <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    {{-- Invoice Info Section --}}
                    <div class="p-5">
                        {{-- Invoice Number --}}
                        <div class="mb-5">
                            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $invoiceId ? ($invoice_number ?? 'Invoice #' . $invoiceId) : 'New Invoice' }}
                            </h1>
                        </div>
                        
                        <div class="grid gap-6 sm:grid-cols-2">
                            {{-- Customer Selection --}}
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Customer <span class="text-red-500">*</span></label>
                                @if($sales_order_id)
                                    {{-- Disabled when created from sales order --}}
                                    <div class="flex w-full items-center gap-3 rounded-lg bg-zinc-100 px-4 py-2.5 text-sm dark:bg-zinc-800">
                                        @php
                                            $selectedCustomer = $customers->firstWhere('id', $customer_id);
                                        @endphp
                                        @if($selectedCustomer)
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-200 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                {{ strtoupper(substr($selectedCustomer->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $selectedCustomer->name }}</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $selectedCustomer->email ?? '' }}</p>
                                            </div>
                                        @else
                                            <span class="text-zinc-500">No customer selected</span>
                                        @endif
                                    </div>
                                @else
                                    {{-- Editable customer selection --}}
                                    <div class="relative" x-data="{ open: false, search: '' }">
                                        <button 
                                            type="button"
                                            @click="open = !open; $nextTick(() => { if(open) $refs.customerSearch.focus() })"
                                            class="flex w-full items-center justify-between rounded-lg border border-transparent bg-transparent px-4 py-2.5 text-left text-sm transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:hover:border-zinc-700"
                                        >
                                            @php
                                                $selectedCustomer = $customers->firstWhere('id', $customer_id);
                                            @endphp
                                            @if($selectedCustomer)
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                        {{ strtoupper(substr($selectedCustomer->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $selectedCustomer->name }}</p>
                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $selectedCustomer->email ?? '' }}</p>
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
                                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $customer->email ?? '' }}</p>
                                                        </div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @error('customer_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- Right Column: Dates --}}
                            <div class="space-y-3">
                                {{-- Invoice Date --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-28 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Invoice Date <span class="text-red-500">*</span></label>
                                    <div class="relative flex-1">
                                        <input 
                                            type="date" 
                                            wire:model="invoice_date"
                                            class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                        />
                                    </div>
                                </div>

                                {{-- Due Date --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-28 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Due Date</label>
                                    <div class="relative flex-1">
                                        <input 
                                            type="date" 
                                            wire:model="due_date"
                                            class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Headers: Invoice Lines & Other Info --}}
                    <div class="flex items-center border-y border-zinc-100 dark:border-zinc-800">
                        <button 
                            type="button"
                            @click="activeTab = 'lines'"
                            :class="activeTab === 'lines' ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                            class="px-5 py-3 text-sm font-medium transition-colors"
                        >
                            Invoice Lines
                        </button>
                        <button 
                            type="button"
                            @click="activeTab = 'other'"
                            :class="activeTab === 'other' ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                            class="px-5 py-3 text-sm font-medium transition-colors"
                        >
                            Other Info
                        </button>
                    </div>
                    
                    {{-- Tab Content: Invoice Lines --}}
                    <div x-show="activeTab === 'lines'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        {{-- Invoice Items Table --}}
                        <div 
                            class="relative overflow-x-auto lg:overflow-visible"
                            x-data="{
                                showColumnMenu: false,
                                columns: {
                                    description: { label: 'Description', visible: true, required: false },
                                    tax: { label: 'Tax', visible: true, required: false },
                                    discount: { label: 'Discount', visible: true, required: false },
                                },
                                isColumnVisible(key) {
                                    const col = this.columns[key];
                                    return col?.visible !== false || col?.required === true;
                                }
                            }"
                        >
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Product</th>
                                        <th x-show="isColumnVisible('description')" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Description</th>
                                        <th class="w-24 px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Qty</th>
                                        <th class="w-32 px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unit Price</th>
                                        <th x-show="isColumnVisible('tax')" class="w-28 px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tax</th>
                                        <th x-show="isColumnVisible('discount')" class="w-24 px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Discount</th>
                                        <th class="w-32 px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Subtotal</th>
                                        <th class="w-10 pl-2 pr-2 py-3 text-right align-middle">
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
                                                    x-cloak
                                                    class="absolute right-0 top-full z-50 mt-1 w-48 rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                                >
                                                    <template x-for="(config, key) in columns" :key="key">
                                                        <label class="flex cursor-pointer items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                            <input 
                                                                type="checkbox" 
                                                                x-model="columns[key].visible"
                                                                :disabled="config.required === true"
                                                                class="rounded border-zinc-300 text-violet-600 focus:ring-violet-500 disabled:opacity-50"
                                                            />
                                                            <span x-text="config.label" :class="config.required === true ? 'text-zinc-400' : ''"></span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @if($invoice && $invoice->items->count())
                                        @foreach($invoice->items as $item)
                                            <tr class="group transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                                <td class="px-4 py-3">
                                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item->product->name ?? '-' }}</span>
                                                </td>
                                                <td x-show="isColumnVisible('description')" class="px-4 py-3">
                                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $item->description ?? '-' }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $item->quantity }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                                </td>
                                                <td x-show="isColumnVisible('tax')" class="px-4 py-3 text-left">
                                                    @php
                                                        $itemTax = $item->tax;
                                                        $taxLabel = '-';
                                                        if ($itemTax) {
                                                            $rateLabel = $itemTax->type === 'percentage'
                                                                ? number_format((float) $itemTax->rate, 2) . '%'
                                                                : 'Rp ' . number_format((float) $itemTax->rate, 0, ',', '.');
                                                            $taxLabel = ($itemTax->name ?? 'Tax') . ' (' . $rateLabel . ')';
                                                        }
                                                    @endphp
                                                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                                        {{ $taxLabel }}
                                                    </span>
                                                </td>
                                                <td x-show="isColumnVisible('discount')" class="px-4 py-3 text-right">
                                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Rp {{ number_format($item->discount ?? 0, 0, ',', '.') }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                                                </td>
                                                <td class="px-2 py-3 text-right"></td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                                No invoice lines yet.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        {{-- Table Footer with Totals --}}
                        <div class="border-t border-zinc-100 bg-zinc-50/50 p-5 dark:border-zinc-800 dark:bg-zinc-900/30">
                            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex-1">
                                    {{-- Notes (Transparent) --}}
                                    <textarea 
                                        wire:model="notes"
                                        rows="3"
                                        placeholder="Notes..."
                                        class="w-full resize-none border-0 bg-transparent px-0 py-0 text-sm text-zinc-700 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-300 dark:placeholder-zinc-500"
                                    ></textarea>
                                </div>

                                <div class="w-full space-y-2 lg:w-72">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-zinc-500 dark:text-zinc-400">Untaxed Amount</span>
                                        <span class="text-zinc-900 dark:text-zinc-100">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-zinc-500 dark:text-zinc-400">Taxes</span>
                                        <span class="text-zinc-900 dark:text-zinc-100">Rp {{ number_format($tax, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between border-t border-zinc-200 pt-2 dark:border-zinc-700">
                                        <span class="font-medium text-zinc-900 dark:text-zinc-100">Total</span>
                                        <span class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($total, 0, ',', '.') }}</span>
                                    </div>
                                    @if($paidAmount > 0)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-emerald-600 dark:text-emerald-400">Paid</span>
                                            <span class="font-medium text-emerald-600 dark:text-emerald-400">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                    @if($remainingAmount > 0)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="font-medium text-zinc-500 dark:text-zinc-400">Amount Due</span>
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Content: Other Info --}}
                    <div x-show="activeTab === 'other'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="p-6">
                            <div class="grid gap-8 lg:grid-cols-2">
                                {{-- Invoice Details Section --}}
                                <div class="space-y-4">
                                    <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invoice Details</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Reference</label>
                                            <input type="text" placeholder="Invoice reference..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Payment Method</label>
                                            <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                <option value="">Select payment method...</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                                <option value="cash">Cash</option>
                                                <option value="credit_card">Credit Card</option>
                                                <option value="check">Check</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Accounting Section --}}
                                <div class="space-y-4">
                                    <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Accounting</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Journal</label>
                                            <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                <option value="">Select journal...</option>
                                                <option value="sales">Sales Journal</option>
                                                <option value="cash">Cash Journal</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Fiscal Position</label>
                                            <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                <option value="">Select fiscal position...</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payments History --}}
                @if($invoice && $invoice->payments->count() > 0)
                    <div class="mt-4 overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="border-b border-zinc-100 px-5 py-3 dark:border-zinc-800">
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Payment History</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                        <th class="px-4 py-2 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Payment #</th>
                                        <th class="px-4 py-2 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Date</th>
                                        <th class="px-4 py-2 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Method</th>
                                        <th class="px-4 py-2 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Reference</th>
                                        <th class="px-4 py-2 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @foreach($invoice->payments as $payment)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                            <td class="px-4 py-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $payment->payment_number ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $payment->payment_date?->format('M d, Y') ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? '-')) }}</td>
                                            <td class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $payment->reference ?? '-' }}</td>
                                            <td class="px-4 py-2 text-right text-sm font-medium text-emerald-600 dark:text-emerald-400">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column: Activity Timeline --}}
            <div class="lg:col-span-3">
                {{-- Chatter Forms --}}
                <x-ui.chatter-forms />

                {{-- Activity Timeline --}}
                @if($invoiceId)
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
                                @php $activity = $item['data']; @endphp
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <x-ui.user-avatar :user="$activity->causer" size="md" :showPopup="true" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <x-ui.user-name :user="$activity->causer" />
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                            @if($activity->event === 'created')
                                                Invoice created
                                            @elseif($activity->properties->has('old') && $activity->event === 'updated')
                                                @php
                                                    $old = $activity->properties->get('old', []);
                                                    $new = $activity->properties->get('attributes', []);
                                                    $changes = collect($new)->filter(fn($val, $key) => isset($old[$key]) && $old[$key] !== $val);
                                                @endphp
                                                @if($changes->isNotEmpty())
                                                    @foreach($changes as $key => $newVal)
                                                        @php
                                                            $oldVal = $old[$key] ?? '-';
                                                            $label = ucfirst(str_replace('_', ' ', $key));
                                                        @endphp
                                                        <span class="block">
                                                            Updated <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $label }}</span>:
                                                            <span class="text-zinc-400 line-through">{{ is_string($oldVal) ? $oldVal : $oldVal }}</span>
                                                            <flux:icon name="arrow-right" class="inline size-3 mx-1" />
                                                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ is_string($newVal) ? $newVal : $newVal }}</span>
                                                        </span>
                                                    @endforeach
                                                @else
                                                    {{ $activity->description }}
                                                @endif
                                            @else
                                                {{ $activity->description }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @empty
                            {{-- Invoice Created (fallback when no activities yet) --}}
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-ui.user-name :user="auth()->user()" />
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ now()->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Invoice created</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @else
                    {{-- Empty State for New Invoice --}}
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

    {{-- Cancel Modal --}}
    <div 
        x-show="showCancelModal" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-black/50" @click="showCancelModal = false"></div>
        <div 
            class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900"
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
                    @click="showCancelModal = false"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700"
                >
                    Cancel Invoice
                </button>
            </div>
        </div>
    </div>

    {{-- Payment Modal --}}
    @if($showPaymentModal)
        <div 
            class="fixed inset-0 z-50 flex items-center justify-center"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="absolute inset-0 bg-zinc-900/60" wire:click="closePaymentModal"></div>

            <div 
                class="relative w-full max-w-4xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl ring-1 ring-black/5 dark:border-zinc-800 dark:bg-zinc-900 dark:ring-white/10"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                {{-- Header with gradient --}}
                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 bg-zinc-50 px-6 py-5 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Record Payment</h3>
                        <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Choose payment method for this invoice</p>
                    </div>

                    <button 
                        type="button"
                        wire:click="closePaymentModal"
                        class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        aria-label="Close"
                    >
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                {{-- Payment Summary Cards --}}
                <div class="px-6 py-5">
                    <div class="flex items-start justify-between gap-6">
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
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">Xendit</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400"> — Generate payment link + QR and wait for webhook update.</span>
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
                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 pl-10 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        />
                                    </div>
                                    @error('paymentAmount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Payment Date <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model="paymentDate" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Payment Method <span class="text-red-500">*</span></label>
                                    <select wire:model="paymentMethod" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="cash">Cash</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="check">Check</option>
                                        <option value="e_wallet">E-Wallet</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Reference / Transaction ID</label>
                                    <input type="text" wire:model="paymentReference" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                </div>
                            </div>
                        </div>

                        <div x-show="$wire.paymentType === 'xendit'" x-transition>
                            @if(!$this->xenditConfigured)
                                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-zinc-300">
                                    Xendit is not configured. Please add your API keys in Settings &gt; Payment Gateway.
                                </div>
                            @elseif($invoice && $invoice->xendit_invoice_url && !in_array(strtolower((string) ($invoice->xendit_status ?? 'pending')), ['paid', 'expired'], true))
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Payment URL</span>
                                            <span class="rounded px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">{{ strtoupper((string) ($invoice->xendit_status ?? 'pending')) }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <input 
                                                type="text" 
                                                readonly
                                                value="{{ $invoice->xendit_invoice_url }}"
                                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            />
                                        </div>
                                        <div class="mt-3 flex justify-end">
                                            <a href="{{ $invoice->xendit_invoice_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                                <flux:icon name="arrow-top-right-on-square" class="size-4" />
                                                Open
                                            </a>
                                        </div>
                                    </div>
                                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">QR Code</span>
                                        <div class="mt-3 flex items-center justify-center">
                                            <img 
                                                alt="Xendit payment QR"
                                                class="h-48 w-48 rounded-lg bg-white p-2"
                                                src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($invoice->xendit_invoice_url) }}"
                                            />
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-zinc-300">
                                    No pending Xendit payment found. Generate a new payment link to continue.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <button 
                        type="button"
                        wire:click="closePaymentModal"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Cancel
                    </button>

                    <button 
                        type="button"
                        wire:click="addPayment"
                        x-show="$wire.paymentType === 'manual'"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        <flux:icon name="banknotes" class="size-4" />
                        Record Payment
                    </button>

                    <button 
                        type="button"
                        wire:click="createXenditPayment"
                        wire:loading.attr="disabled"
                        x-show="$wire.paymentType === 'xendit'"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                        <span wire:loading.remove wire:target="createXenditPayment">Generate / Reuse Xendit</span>
                        <span wire:loading wire:target="createXenditPayment">Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
