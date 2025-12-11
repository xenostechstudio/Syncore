<div x-data="{ 
    activeTab: 'lines',
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false,
    showCancelModal: false
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
                                <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <flux:icon name="printer" class="size-4" />
                                    <span>Print</span>
                                </button>
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
                    <button type="button" wire:click="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="document-check" class="size-4" />
                        Save
                    </button>
                    @if($invoiceId && $status !== 'paid' && $status !== 'cancelled')
                        <button type="button" wire:click="openPaymentModal" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition-colors hover:bg-emerald-100 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50">
                            <flux:icon name="banknotes" class="size-4" />
                            Add Payment
                        </button>
                    @endif
                    <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="printer" class="size-4" />
                        Print
                    </button>
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
            <div class="col-span-3 flex items-center justify-end gap-1">
                <button @click="showSendMessage = !showSendMessage; showLogNote = false; showScheduleActivity = false" :class="showSendMessage ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'" class="rounded-lg p-2 transition-colors" title="Send message">
                    <flux:icon name="chat-bubble-left" class="size-5" />
                </button>
                <button @click="showLogNote = !showLogNote; showSendMessage = false; showScheduleActivity = false" :class="showLogNote ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'" class="rounded-lg p-2 transition-colors" title="Log note">
                    <flux:icon name="pencil-square" class="size-5" />
                </button>
                <button @click="showScheduleActivity = !showScheduleActivity; showSendMessage = false; showLogNote = false" :class="showScheduleActivity ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'" class="rounded-lg p-2 transition-colors" title="Schedule activity">
                    <flux:icon name="clock" class="size-5" />
                </button>
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
                        {{-- Title inside card --}}
                        <h1 class="mb-5 text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $invoiceId ? ($invoice_number ?? 'Invoice #' . $invoiceId) : 'New' }}
                        </h1>
                        
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
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Product</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Description</th>
                                        <th class="w-24 px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Qty</th>
                                        <th class="w-32 px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unit Price</th>
                                        <th class="w-28 px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tax</th>
                                        <th class="w-24 px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Discount</th>
                                        <th class="w-32 px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @if($invoice && $invoice->items->count())
                                        @foreach($invoice->items as $item)
                                            <tr class="group transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                                <td class="px-4 py-3">
                                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item->product->name ?? '-' }}</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $item->description ?? '-' }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $item->quantity }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                                </td>
                                                {{-- Tax Type (placeholder until per-line taxes are implemented) --}}
                                                <td class="px-4 py-3 text-left">
                                                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                                        @if(property_exists($item, 'tax_label'))
                                                            {{ $item->tax_label ?? '-' }}
                                                        @else
                                                            -
                                                        @endif
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Rp {{ number_format($item->discount ?? 0, 0, ',', '.') }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                                No invoice lines yet.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                                {{-- Table Footer with Totals --}}
                                <tfoot class="border-t border-zinc-200 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/30">
                                    <tr>
                                        <td colspan="4" rowspan="5" class="px-4 py-3 align-top">
                                            {{-- Notes (Transparent) --}}
                                            <textarea 
                                                wire:model="notes"
                                                rows="3"
                                                placeholder="Notes..."
                                                class="w-full max-w-md resize-none border-0 bg-transparent px-0 py-0 text-sm text-zinc-700 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-300 dark:placeholder-zinc-500"
                                            ></textarea>
                                        </td>
                                        <td class="px-4 py-2 text-right text-sm text-zinc-500 dark:text-zinc-400">Subtotal</td>
                                        <td class="px-4 py-2 text-right text-sm text-zinc-900 dark:text-zinc-100">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-2 text-right text-sm text-zinc-500 dark:text-zinc-400">Taxes</td>
                                        <td class="px-4 py-2 text-right text-sm text-zinc-900 dark:text-zinc-100">Rp {{ number_format($tax, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-2 text-right text-sm text-zinc-500 dark:text-zinc-400">Discount</td>
                                        <td class="px-4 py-2 text-right text-sm text-zinc-900 dark:text-zinc-100">Rp {{ number_format($discount, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr class="border-t border-zinc-200 dark:border-zinc-700">
                                        <td class="px-4 py-2 text-right text-sm font-medium text-zinc-900 dark:text-zinc-100">Total</td>
                                        <td class="px-4 py-2 text-right text-lg font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($total, 0, ',', '.') }}</td>
                                    </tr>
                                    @if($paidAmount > 0)
                                        <tr>
                                            <td class="px-4 py-2 text-right text-sm text-emerald-600 dark:text-emerald-400">Paid</td>
                                            <td class="px-4 py-2 text-right text-sm font-medium text-emerald-600 dark:text-emerald-400">Rp {{ number_format($paidAmount, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    @if($remainingAmount > 0)
                                        <tr>
                                            <td class="px-4 py-2 text-right text-sm font-medium text-zinc-500 dark:text-zinc-400">Amount Due</td>
                                            <td class="px-4 py-2 text-right text-sm font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                </tfoot>
                            </table>
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

            {{-- Right Column: Activity Timeline (No Card) --}}
            <div class="lg:col-span-3">
                {{-- Message/Note Input Panels (shown when icons clicked) --}}
                <div x-show="showSendMessage" x-collapse class="mb-4">
                    <div class="flex gap-3">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                            <flux:icon name="chat-bubble-left" class="size-4" />
                        </div>
                        <div class="flex-1">
                            <textarea 
                                rows="3"
                                placeholder="Send a message to followers..."
                                class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-blue-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"
                            ></textarea>
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Attach file">
                                        <flux:icon name="paper-clip" class="size-4" />
                                    </button>
                                    <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Mention">
                                        <flux:icon name="at-symbol" class="size-4" />
                                    </button>
                                </div>
                                <button type="button" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-blue-700">
                                    Send
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="showLogNote" x-collapse class="mb-4">
                    <div class="flex gap-3">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                            <flux:icon name="pencil-square" class="size-4" />
                        </div>
                        <div class="flex-1">
                            <textarea 
                                rows="3"
                                placeholder="Log an internal note..."
                                class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-amber-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"
                            ></textarea>
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Attach file">
                                        <flux:icon name="paper-clip" class="size-4" />
                                    </button>
                                    <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Mention">
                                        <flux:icon name="at-symbol" class="size-4" />
                                    </button>
                                </div>
                                <button type="button" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-amber-700">
                                    Log Note
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="showScheduleActivity" x-collapse class="mb-4">
                    <div class="flex gap-3">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400">
                            <flux:icon name="clock" class="size-4" />
                        </div>
                        <div class="flex-1 space-y-3">
                            <div>
                                <label class="mb-1 block text-xs text-zinc-500 dark:text-zinc-400">Activity Type</label>
                                <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-violet-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                    <option value="">Select activity type...</option>
                                    <option value="call">Call</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="todo">To-Do</option>
                                    <option value="email">Email</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-zinc-500 dark:text-zinc-400">Due Date</label>
                                <input type="date" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-violet-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-zinc-500 dark:text-zinc-400">Summary</label>
                                <input type="text" placeholder="Activity summary..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-violet-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>
                            <div class="flex justify-end">
                                <button type="button" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-violet-700">
                                    Schedule
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Activity Timeline --}}
                @if($invoiceId)
                    {{-- Today's Date Separator --}}
                    <div class="mb-4 flex items-center gap-3">
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Today</span>
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    </div>

                    {{-- Sample Activity Items --}}
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                                <flux:icon name="document-text" class="size-4" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                    <span class="font-medium">Invoice Created</span>
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ now()->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center text-sm text-zinc-500 dark:text-zinc-400">
                        <p>Activity will appear here after saving.</p>
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
                    wire:click="$set('status', 'cancelled')"
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
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-data="{ 
                amount: {{ $remainingAmount }},
                formatCurrency(value) {
                    return new Intl.NumberFormat('id-ID').format(value);
                }
            }"
            x-trap.noscroll="true"
        >
            <div 
                class="absolute inset-0 bg-black/60 backdrop-blur-sm" 
                wire:click="closePaymentModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
            ></div>
            <div 
                class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                {{-- Header with gradient --}}
                <div class="relative bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-5">
                    <button 
                        type="button" 
                        wire:click="closePaymentModal" 
                        class="absolute right-4 top-4 rounded-full p-1 text-white/80 transition-colors hover:bg-white/20 hover:text-white"
                    >
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/20">
                            <flux:icon name="banknotes" class="size-6 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-white">Record Payment</h3>
                            <p class="text-sm text-emerald-100">{{ $invoice_number }}</p>
                        </div>
                    </div>
                </div>

                {{-- Payment Summary Cards --}}
                <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/50">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invoice Total</p>
                            <p class="mt-1 text-lg font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($total, 0, ',', '.') }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Paid</p>
                            <p class="mt-1 text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($paidAmount, 0, ',', '.') }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Remaining</p>
                            <p class="mt-1 text-lg font-bold text-amber-600 dark:text-amber-400">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    {{-- Progress bar --}}
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <span>Payment Progress</span>
                            <span>{{ $total > 0 ? round(($paidAmount / $total) * 100) : 0 }}%</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                            <div 
                                class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-emerald-400 transition-all duration-500"
                                style="width: {{ $total > 0 ? min(($paidAmount / $total) * 100, 100) : 0 }}%"
                            ></div>
                        </div>
                    </div>
                </div>

                {{-- Payment Form --}}
                <div class="space-y-5 px-6 py-5">
                    {{-- Amount Input with Quick Fill --}}
                    <div>
                        <label class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Payment Amount <span class="text-red-500">*</span></span>
                            <button 
                                type="button"
                                wire:click="$set('paymentAmount', {{ $remainingAmount }})"
                                class="text-xs font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300"
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
                                placeholder="0"
                                class="w-full rounded-xl border border-zinc-200 bg-white py-3 pl-10 pr-4 text-lg font-semibold text-zinc-900 placeholder-zinc-300 transition-colors focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>
                        @error('paymentAmount') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        {{-- Quick amount buttons --}}
                        <div class="mt-2 flex flex-wrap gap-2">
                            @if($remainingAmount > 0)
                                <button 
                                    type="button"
                                    wire:click="$set('paymentAmount', {{ $remainingAmount * 0.25 }})"
                                    class="rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs font-medium text-zinc-600 transition-colors hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                >
                                    25%
                                </button>
                                <button 
                                    type="button"
                                    wire:click="$set('paymentAmount', {{ $remainingAmount * 0.5 }})"
                                    class="rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs font-medium text-zinc-600 transition-colors hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                >
                                    50%
                                </button>
                                <button 
                                    type="button"
                                    wire:click="$set('paymentAmount', {{ $remainingAmount * 0.75 }})"
                                    class="rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs font-medium text-zinc-600 transition-colors hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                >
                                    75%
                                </button>
                                <button 
                                    type="button"
                                    wire:click="$set('paymentAmount', {{ $remainingAmount }})"
                                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 transition-colors hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50"
                                >
                                    100%
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Two column layout for date and method --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Payment Date <span class="text-red-500">*</span></label>
                            <input 
                                type="date" 
                                wire:model="paymentDate"
                                class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Payment Method <span class="text-red-500">*</span></label>
                            <select 
                                wire:model="paymentMethod"
                                class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="check">Check</option>
                                <option value="e_wallet">E-Wallet</option>
                            </select>
                        </div>
                    </div>

                    {{-- Reference --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Reference / Transaction ID</label>
                        <input 
                            type="text" 
                            wire:model="paymentReference"
                            placeholder="e.g., TRX-123456789"
                            class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div class="flex items-center justify-between border-t border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/50">
                    <button 
                        type="button"
                        wire:click="closePaymentModal"
                        class="rounded-xl border border-zinc-300 bg-white px-5 py-2.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button"
                        wire:click="addPayment"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-2.5 text-sm font-medium text-white shadow-lg shadow-emerald-500/25 transition-all hover:from-emerald-700 hover:to-emerald-600 hover:shadow-emerald-500/30"
                    >
                        <wire:loading.remove wire:target="addPayment">
                            <flux:icon name="check" class="size-4" />
                            Record Payment
                        </wire:loading.remove>
                        <wire:loading wire:target="addPayment">
                            <svg class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </wire:loading>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
