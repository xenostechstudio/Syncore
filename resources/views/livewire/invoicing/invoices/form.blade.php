<div x-data="{ 
    activeTab: 'lines',
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false,
    showCancelModal: false,
    showShareModal: $wire.entangle('showShareModal'),
    showPaymentModal: $wire.entangle('showPaymentModal')
}">
    <x-slot:header>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
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
                                @if($invoiceId)
                                <button type="button" wire:click="duplicate" wire:loading.attr="disabled" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <flux:icon name="document-duplicate" wire:loading.remove wire:target="duplicate" class="size-4" />
                                    <flux:icon name="arrow-path" wire:loading wire:target="duplicate" class="size-4 animate-spin" />
                                    <span>Duplicate</span>
                                </button>
                                <a href="{{ route('pdf.invoice', $invoiceId) }}" target="_blank" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <flux:icon name="arrow-down-tray" class="size-4" />
                                    <span>Download PDF</span>
                                </a>
                                @endif
                                <flux:menu.separator />
                                <button type="button" wire:click="delete" wire:confirm="Are you sure?" wire:loading.attr="disabled" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <flux:icon name="trash" wire:loading.remove wire:target="delete" class="size-4" />
                                    <flux:icon name="arrow-path" wire:loading wire:target="delete" class="size-4 animate-spin" />
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
                        @endphp
                        <x-ui.status-badge :status="$soStatus" type="order" />
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
        <div class="flex flex-col-reverse gap-4 lg:grid lg:grid-cols-12 lg:items-center lg:gap-6">
            {{-- Left: Action Buttons (col-span-9) --}}
            <div class="col-span-9 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
                    @if($invoiceId && $status !== 'paid' && $status !== 'cancelled')
                        <button type="button" @click="$wire.set('showPaymentModal', true)" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <flux:icon name="banknotes" class="size-4" />
                            Add Payment
                        </button>
                    @endif
                    <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 disabled:opacity-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="document-check" wire:loading.remove wire:target="save" class="size-4" />
                        <flux:icon name="arrow-path" wire:loading wire:target="save" class="size-4 animate-spin" />
                        <span wire:loading.remove wire:target="save">Save</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                    @if($invoiceId)
                        <a href="{{ route('pdf.invoice', $invoiceId) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            Download PDF
                        </a>
                    @endif
                    @if($invoiceId)
                        <button type="button" @click="showShareModal = true; $wire.prepareShareModal()" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
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
                    {{-- Desktop Stepper --}}
                    <div class="hidden items-center lg:flex">
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

                    {{-- Mobile Badge --}}
                    <div class="flex items-center lg:hidden">
                        <span class="inline-flex h-[32px] items-center rounded-lg px-3 text-sm font-medium
                            {{ match($status) {
                                'draft' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
                                'sent' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'partial' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                'paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                'overdue' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                default => 'bg-zinc-100 text-zinc-700'
                            } }}">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- Right: Chatter Icons (col-span-3) --}}
            <div class="col-span-3 flex items-center justify-end gap-1">
                <x-ui.chatter-buttons />
            </div>
        </div>

        @include('livewire.invoicing.invoices.modals.share')
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
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
                                    <label class="text-sm font-light text-zinc-600 sm:w-28 sm:flex-shrink-0 dark:text-zinc-400">Invoice Date <span class="text-red-500">*</span></label>
                                    <div class="relative flex-1">
                                        <input 
                                            type="date" 
                                            wire:model="invoice_date"
                                            class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                        />
                                    </div>
                                </div>

                                {{-- Due Date --}}
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
                                    <label class="text-sm font-light text-zinc-600 sm:w-28 sm:flex-shrink-0 dark:text-zinc-400">Due Date</label>
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
                                                    <div>
                                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item->product->name ?? '-' }}</p>
                                                        @if($item->description)
                                                            <p x-show="isColumnVisible('description')" class="text-xs text-zinc-400 dark:text-zinc-500">{{ $item->description }}</p>
                                                        @endif
                                                    </div>
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
                                                    @endphp
                                                    @if($itemTax)
                                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                                                            {{ $itemTax->code ?? $itemTax->name }}
                                                            @if($itemTax->type === 'percentage')
                                                                {{ ' ' . number_format((float) $itemTax->rate, 0) . '%' }}
                                                            @endif
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-zinc-400">No Tax</span>
                                                    @endif
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
                                            <td colspan="7" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
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
                            <div class="grid gap-x-16 gap-y-6 lg:grid-cols-2">
                                {{-- Invoice Details Section --}}
                                <div class="pb-2 pr-4">
                                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invoice Details</h3>
                                    <div class="space-y-3">
                                        {{-- Reference --}}
                                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-3">
                                            <label class="text-sm font-medium text-zinc-700 sm:w-36 dark:text-zinc-300">Reference</label>
                                            <div class="flex-1">
                                                <input 
                                                    type="text" 
                                                    placeholder="Invoice reference..."
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                                />
                                            </div>
                                        </div>

                                        {{-- Payment Method --}}
                                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-3">
                                            <label class="text-sm font-medium text-zinc-700 sm:w-36 dark:text-zinc-300">Payment Method</label>
                                            <div class="relative flex-1">
                                                <select class="w-full appearance-none rounded-lg border border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500">
                                                    <option value="">Select payment method...</option>
                                                    <option value="bank_transfer">Bank Transfer</option>
                                                    <option value="cash">Cash</option>
                                                    <option value="credit_card">Credit Card</option>
                                                    <option value="check">Check</option>
                                                </select>
                                                <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Accounting Section --}}
                                <div class="pb-2 pr-4">
                                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Accounting</h3>
                                    <div class="space-y-3">
                                        {{-- Journal --}}
                                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-3">
                                            <label class="text-sm font-medium text-zinc-700 sm:w-36 dark:text-zinc-300">Journal</label>
                                            <div class="relative flex-1">
                                                <select class="w-full appearance-none rounded-lg border border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500">
                                                    <option value="">Select journal...</option>
                                                    <option value="sales">Sales Journal</option>
                                                    <option value="cash">Cash Journal</option>
                                                </select>
                                                <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                            </div>
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
                    <div class="space-y-3">
                        @forelse($activities as $item)
                            @if($item['type'] === 'note')
                                {{-- Note Item - Compact --}}
                                <x-ui.note-item :note="$item['data']" />
                            @else
                                {{-- Activity Log Item --}}
                                <x-ui.activity-item :activity="$item['data']" emptyMessage="Invoice created" />
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
    @include('livewire.invoicing.invoices.modals.cancel')

    {{-- Payment Modal --}}
    @include('livewire.invoicing.invoices.modals.payment')
</div>
