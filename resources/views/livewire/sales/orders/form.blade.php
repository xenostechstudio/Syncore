<div x-data="{ activeTab: 'items', showLogNote: false }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            {{-- Left Group: Back Button, Title --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.orders.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">
                    {{ $orderId ? ($orderNumber ?? 'SO-' . str_pad($orderId, 5, '0', STR_PAD_LEFT)) : 'New Quotation' }}
                </span>
            </div>

            {{-- Status Stepper (Odoo Style - Arrow Shape) --}}
            @php
                $steps = [
                    ['key' => 'draft', 'label' => 'Quotation'],
                    ['key' => 'confirmed', 'label' => 'Quotation Sent'],
                    ['key' => 'processing', 'label' => 'Sales Order'],
                    ['key' => 'delivered', 'label' => 'Done'],
                ];
                $currentIndex = collect($steps)->search(fn($s) => $s['key'] === $status);
                if ($currentIndex === false) $currentIndex = 0;
                $isCancelled = $status === 'cancelled';
            @endphp
            <div class="flex items-center">
                @if($isCancelled)
                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                        Cancelled
                    </span>
                @else
                    <div class="flex items-center overflow-hidden rounded-full border border-zinc-200 dark:border-zinc-700">
                        @foreach($steps as $index => $step)
                            @php
                                $isActive = $index === $currentIndex;
                                $isCompleted = $index < $currentIndex;
                                $isPending = $index > $currentIndex;
                            @endphp
                            <div class="relative flex items-center {{ $index > 0 ? '-ml-px' : '' }}">
                                <span class="relative z-10 px-3 py-1 text-xs font-medium transition-colors
                                    {{ $isActive ? 'bg-violet-600 text-white' : '' }}
                                    {{ $isCompleted ? 'bg-emerald-500 text-white' : '' }}
                                    {{ $isPending ? 'bg-zinc-50 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500' : '' }}
                                    {{ $index === 0 ? 'rounded-l-full' : '' }}
                                    {{ $index === count($steps) - 1 ? 'rounded-r-full' : '' }}
                                ">
                                    @if($isCompleted)
                                        <flux:icon name="check" class="inline-block size-3 mr-0.5" />
                                    @endif
                                    {{ $step['label'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
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
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="flex flex-wrap items-center gap-2">
            @if($status === 'draft')
                <button 
                    type="button"
                    wire:click="confirm"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    <flux:icon name="check" class="size-4" />
                    Confirm
                </button>
            @endif
            <button 
                type="button"
                wire:click="save"
                class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                <flux:icon name="document-check" class="size-4" />
                {{ $orderId ? 'Save' : 'Save Draft' }}
            </button>
            <button 
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                <flux:icon name="envelope" class="size-4" />
                Send
            </button>
            <button 
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                <flux:icon name="printer" class="size-4" />
                Print
            </button>
            <button 
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                <flux:icon name="eye" class="size-4" />
                Preview
            </button>
            @if($orderId && $status !== 'cancelled')
                <button 
                    type="button"
                    wire:click="cancel"
                    wire:confirm="Are you sure you want to cancel this order?"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20"
                >
                    <flux:icon name="x-mark" class="size-4" />
                    Cancel
                </button>
            @endif
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        {{-- Two Column Layout: Form Left, History Right --}}
        <div class="grid gap-6 lg:grid-cols-12">
        {{-- Left Column: Main Form --}}
        <div class="space-y-6 lg:col-span-8">
            {{-- Customer & Order Info Card --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Order Information</h2>
                </div>
                <div class="p-5">
                    <div class="grid gap-6 sm:grid-cols-2">
                        {{-- Customer Selection (Searchable) --}}
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Customer <span class="text-red-500">*</span></label>
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
                            @error('customer_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Order Date --}}
                        <div>
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Order Date <span class="text-red-500">*</span></label>
                            <input 
                                type="date" 
                                wire:model="order_date"
                                class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                            @error('order_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Expected Delivery --}}
                        <div>
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Expected Delivery</label>
                            <input 
                                type="date" 
                                wire:model="expected_delivery_date"
                                class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs: Order Items & Other Info --}}
            <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                {{-- Tab Headers --}}
                <div class="flex items-center border-b border-zinc-100 dark:border-zinc-800">
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
                </div>
                
                {{-- Tab Content: Order Items --}}
                <div x-show="activeTab === 'items'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    {{-- Items Table --}}
                    <div class="overflow-visible">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    <th class="w-10 px-2 py-2.5"></th>
                                    <th class="px-3 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Product</th>
                                    <th class="w-20 px-3 py-2.5 text-center text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Qty</th>
                                    <th class="w-32 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unit Price</th>
                                    <th class="w-24 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Disc %</th>
                                    <th class="w-32 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Subtotal</th>
                                    <th class="w-12 px-2 py-2.5"></th>
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
                                        <td class="px-3 py-2 overflow-visible">
                                            <div x-data="{ open: false, search: '' }" class="relative">
                                                @if($item['inventory_item_id'])
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
                                                    class="absolute left-0 top-full z-[200] mt-1 w-80 rounded-lg border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
                                                    style="position: fixed; transform: translateY(0);"
                                                    x-init="$watch('open', value => {
                                                        if (value) {
                                                            const rect = $el.previousElementSibling?.getBoundingClientRect() || $el.parentElement.getBoundingClientRect();
                                                            $el.style.position = 'fixed';
                                                            $el.style.top = (rect.bottom + 4) + 'px';
                                                            $el.style.left = rect.left + 'px';
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
                                                        @foreach($inventoryItems as $invItem)
                                                            <button 
                                                                type="button"
                                                                x-show="'{{ strtolower($invItem->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($invItem->sku ?? '') }}'.includes(search.toLowerCase()) || search === ''"
                                                                wire:click="selectItem({{ $index }}, {{ $invItem->id }})"
                                                                @click="open = false; search = ''"
                                                                class="flex w-full items-center gap-3 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
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

                                        {{-- Quantity --}}
                                        <td class="px-3 py-2">
                                            <input 
                                                type="text"
                                                wire:model.live="items.{{ $index }}.quantity"
                                                class="w-full bg-transparent text-center text-sm text-zinc-900 focus:outline-none dark:text-zinc-100"
                                            />
                                        </td>

                                        {{-- Unit Price --}}
                                        <td class="px-3 py-2">
                                            <input 
                                                type="text"
                                                wire:model.live="items.{{ $index }}.unit_price"
                                                class="w-full bg-transparent text-right text-sm text-zinc-900 focus:outline-none dark:text-zinc-100"
                                            />
                                        </td>

                                        {{-- Discount --}}
                                        <td class="px-3 py-2">
                                            <input 
                                                type="text"
                                                wire:model.live="items.{{ $index }}.discount"
                                                class="w-full bg-transparent text-right text-sm text-zinc-900 focus:outline-none dark:text-zinc-100"
                                            />
                                        </td>

                                        {{-- Subtotal --}}
                                        <td class="px-3 py-2 text-right">
                                            <span class="text-sm text-zinc-900 dark:text-zinc-100">Rp {{ number_format($item['total'], 0, ',', '.') }}</span>
                                        </td>

                                        {{-- Remove --}}
                                        <td class="px-2 py-2 text-center">
                                            @if(count($items) > 1)
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

                    {{-- Add Line Button --}}
                    <div class="border-t border-zinc-100 px-4 py-3 dark:border-zinc-800">
                        <button 
                            type="button"
                            wire:click="addItem"
                            class="inline-flex items-center gap-1.5 text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                        >
                            <flux:icon name="plus" class="size-4" />
                            Add a line
                        </button>
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
                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Campaign</label>
                                        <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            <option value="">Select campaign...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Medium</label>
                                        <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            <option value="">Select medium...</option>
                                            <option value="1">Email</option>
                                            <option value="2">Phone</option>
                                            <option value="3">Website</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Source</label>
                                        <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            <option value="">Select source...</option>
                                            <option value="1">Newsletter</option>
                                            <option value="2">Google Ads</option>
                                            <option value="3">Referral</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Chatter / Activity History (Odoo Style) --}}
        <div class="lg:col-span-4">
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                {{-- Chatter Header --}}
                <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 dark:border-zinc-800">
                    <h2 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Chatter</h2>
                    <div class="flex items-center gap-1">
                        <button class="rounded p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Send message">
                            <flux:icon name="chat-bubble-left" class="size-4" />
                        </button>
                        <button 
                            @click="showLogNote = !showLogNote" 
                            :class="showLogNote ? 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' : 'text-zinc-400'"
                            class="rounded p-1.5 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" 
                            title="Log note"
                        >
                            <flux:icon name="pencil-square" class="size-4" />
                        </button>
                        <button class="rounded p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Schedule activity">
                            <flux:icon name="clock" class="size-4" />
                        </button>
                    </div>
                </div>

                {{-- Message Input (Hidden by default) --}}
                <div x-show="showLogNote" x-collapse class="border-b border-zinc-100 p-4 dark:border-zinc-800">
                    <div class="flex gap-3">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-zinc-200 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <textarea 
                                rows="2"
                                placeholder="Log an internal note..."
                                class="w-full resize-none rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:bg-white focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:focus:bg-zinc-900"
                            ></textarea>
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    <button type="button" class="rounded p-1 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300" title="Attach file">
                                        <flux:icon name="paper-clip" class="size-4" />
                                    </button>
                                    <button type="button" class="rounded p-1 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300" title="Mention">
                                        <flux:icon name="at-symbol" class="size-4" />
                                    </button>
                                </div>
                                <button type="button" class="rounded-lg bg-zinc-900 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                                    Log Note
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Activity Timeline --}}
                <div class="max-h-[500px] overflow-y-auto">
                    @if($orderId)
                        {{-- Activity Items --}}
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            {{-- Order Created --}}
                            <div class="p-4">
                                <div class="flex gap-3">
                                    <div class="relative flex-shrink-0">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                                            <flux:icon name="document-plus" class="size-4" />
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Order Created</span>
                                            <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">System</span>
                                        </div>
                                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $createdAt ?? now()->format('M d, Y \a\t H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @if(isset($activityLog) && count($activityLog) > 0)
                                @foreach($activityLog as $activity)
                                    <div class="p-4">
                                        <div class="flex gap-3">
                                            <div class="relative flex-shrink-0">
                                                @php
                                                    $activityIcon = match($activity['type'] ?? 'note') {
                                                        'status_change' => ['icon' => 'arrow-path', 'bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-600 dark:text-blue-400'],
                                                        'email' => ['icon' => 'envelope', 'bg' => 'bg-violet-100 dark:bg-violet-900/30', 'text' => 'text-violet-600 dark:text-violet-400'],
                                                        'note' => ['icon' => 'pencil', 'bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-600 dark:text-amber-400'],
                                                        default => ['icon' => 'information-circle', 'bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                                    };
                                                @endphp
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $activityIcon['bg'] }} {{ $activityIcon['text'] }}">
                                                    <flux:icon name="{{ $activityIcon['icon'] }}" class="size-4" />
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div>
                                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $activity['user'] }}</span>
                                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $activity['message'] }}</p>
                                                    </div>
                                                </div>
                                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $activity['date'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            {{-- Status Changes (if status is not draft) --}}
                            @if($status !== 'draft')
                                <div class="p-4">
                                    <div class="flex gap-3">
                                        <div class="relative flex-shrink-0">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                                <flux:icon name="arrow-path" class="size-4" />
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Status Changed</span>
                                            </div>
                                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                                Order status changed to <span class="font-medium">{{ ucfirst($status) }}</span>
                                            </p>
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $updatedAt ?? now()->format('M d, Y \a\t H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Empty State for New Order --}}
                        <div class="p-8 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon name="chat-bubble-left-right" class="size-6 text-zinc-400" />
                            </div>
                            <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No activity yet</p>
                            <p class="text-xs text-zinc-400 dark:text-zinc-500">Activity will appear here once the order is saved</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
