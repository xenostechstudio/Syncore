<div class="space-y-6">
    {{-- Header with Actions --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('sales.orders.index') }}" wire:navigate class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-normal text-zinc-900 dark:text-zinc-100">
                    {{ $orderId ? 'Edit Sales Order' : 'New Sales Order' }}
                </h1>
                @if($orderId)
                    <p class="text-sm font-light text-zinc-500 dark:text-zinc-400">Order #{{ $orderId }}</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($status === 'draft')
                <flux:button wire:click="confirm" variant="primary">
                    Confirm Order
                </flux:button>
            @endif
            <flux:button wire:click="save">
                {{ $orderId ? 'Update' : 'Save as Draft' }}
            </flux:button>
        </div>
    </div>

    {{-- Status Badge --}}
    @if($orderId)
        <div class="flex items-center gap-2">
            @php
                $statusConfig = match($status) {
                    'draft' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => 'Draft'],
                    'confirmed' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400', 'label' => 'Confirmed'],
                    'processing' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'label' => 'Processing'],
                    'shipped' => ['bg' => 'bg-violet-100 dark:bg-violet-900/30', 'text' => 'text-violet-700 dark:text-violet-400', 'label' => 'Shipped'],
                    'delivered' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'label' => 'Delivered'],
                    'cancelled' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'label' => 'Cancelled'],
                    default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => ucfirst($status)],
                };
            @endphp
            <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-light {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                {{ $statusConfig['label'] }}
            </span>
        </div>
    @endif

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
                        {{-- Customer Selection --}}
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Customer <span class="text-red-500">*</span></label>
                            <div class="relative" x-data="{ open: false }">
                                <button 
                                    type="button"
                                    @click="open = !open"
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
                                    <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div 
                                    x-show="open" 
                                    @click.outside="open = false"
                                    x-transition
                                    class="absolute left-0 top-full z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                >
                                    @foreach($customers as $customer)
                                        <button 
                                            type="button"
                                            wire:click="$set('customer_id', {{ $customer->id }})"
                                            @click="open = false"
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

                        {{-- Shipping Address --}}
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Shipping Address</label>
                            <textarea 
                                wire:model="shipping_address"
                                rows="2"
                                placeholder="Enter shipping address..."
                                class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order Items Table --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Order Items</h2>
                    <button 
                        type="button"
                        wire:click="addItem"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-normal text-zinc-600 transition-colors hover:border-zinc-300 hover:text-zinc-900 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-100"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Item
                    </button>
                </div>
                
                {{-- Items Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900">
                                <th class="px-4 py-3 text-left text-xs font-normal text-zinc-500 dark:text-zinc-400">Product</th>
                                <th class="px-4 py-3 text-center text-xs font-normal text-zinc-500 dark:text-zinc-400 w-24">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-normal text-zinc-500 dark:text-zinc-400 w-32">Unit Price</th>
                                <th class="px-4 py-3 text-right text-xs font-normal text-zinc-500 dark:text-zinc-400 w-28">Discount</th>
                                <th class="px-4 py-3 text-right text-xs font-normal text-zinc-500 dark:text-zinc-400 w-32">Subtotal</th>
                                <th class="px-4 py-3 text-center text-xs font-normal text-zinc-500 dark:text-zinc-400 w-16"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($items as $index => $item)
                                <tr class="group">
                                    {{-- Product Selection --}}
                                    <td class="px-4 py-3">
                                        <div x-data="{ open: false, search: '' }" class="relative">
                                            @if($item['inventory_item_id'])
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                                        <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $item['name'] }}</p>
                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item['sku'] }}</p>
                                                    </div>
                                                    <button 
                                                        type="button"
                                                        @click="open = true"
                                                        class="ml-2 rounded p-1 text-zinc-400 opacity-0 transition-all hover:bg-zinc-100 hover:text-zinc-600 group-hover:opacity-100 dark:hover:bg-zinc-800"
                                                    >
                                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            @else
                                                <button 
                                                    type="button"
                                                    @click="open = true"
                                                    class="flex w-full items-center gap-2 rounded-lg border border-dashed border-zinc-300 px-3 py-2 text-sm text-zinc-400 transition-colors hover:border-zinc-400 hover:text-zinc-600 dark:border-zinc-600 dark:hover:border-zinc-500"
                                                >
                                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                    </svg>
                                                    Select product
                                                </button>
                                            @endif

                                            {{-- Product Dropdown --}}
                                            <div 
                                                x-show="open" 
                                                @click.outside="open = false"
                                                x-transition
                                                class="absolute left-0 top-full z-50 mt-1 w-80 rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                            >
                                                <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                                    <input 
                                                        type="text"
                                                        wire:model.live.debounce.300ms="itemSearch"
                                                        placeholder="Search products..."
                                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                                    />
                                                </div>
                                                <div class="max-h-48 overflow-auto py-1">
                                                    @foreach($inventoryItems as $invItem)
                                                        <button 
                                                            type="button"
                                                            wire:click="selectItem({{ $index }}, {{ $invItem->id }})"
                                                            @click="open = false"
                                                            class="flex w-full items-center gap-3 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                                        >
                                                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                                                <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                                                </svg>
                                                            </div>
                                                            <div class="flex-1">
                                                                <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $invItem->name }}</p>
                                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $invItem->sku }} · ${{ number_format($invItem->selling_price, 2) }}</p>
                                                            </div>
                                                            <span class="text-xs text-zinc-400">{{ $invItem->quantity }} in stock</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Quantity --}}
                                    <td class="px-4 py-3">
                                        <input 
                                            type="number"
                                            wire:model.live="items.{{ $index }}.quantity"
                                            min="1"
                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-center text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        />
                                    </td>

                                    {{-- Unit Price --}}
                                    <td class="px-4 py-3">
                                        <input 
                                            type="number"
                                            wire:model.live="items.{{ $index }}.unit_price"
                                            step="0.01"
                                            min="0"
                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-right text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        />
                                    </td>

                                    {{-- Discount --}}
                                    <td class="px-4 py-3">
                                        <input 
                                            type="number"
                                            wire:model.live="items.{{ $index }}.discount"
                                            step="0.01"
                                            min="0"
                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-right text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        />
                                    </td>

                                    {{-- Subtotal --}}
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">${{ number_format($item['total'], 2) }}</span>
                                    </td>

                                    {{-- Remove --}}
                                    <td class="px-4 py-3 text-center">
                                        @if(count($items) > 1)
                                            <button 
                                                type="button"
                                                wire:click="removeItem({{ $index }})"
                                                class="rounded p-1.5 text-zinc-400 opacity-0 transition-all hover:bg-red-50 hover:text-red-500 group-hover:opacity-100 dark:hover:bg-red-900/20"
                                            >
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-400">
                                        No items added yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Totals --}}
                <div class="border-t border-zinc-100 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-900/50">
                    <div class="ml-auto w-64 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-light text-zinc-500 dark:text-zinc-400">Subtotal</span>
                            <span class="font-normal text-zinc-900 dark:text-zinc-100">${{ number_format($this->subtotal, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-light text-zinc-500 dark:text-zinc-400">Tax (11%)</span>
                            <span class="font-normal text-zinc-900 dark:text-zinc-100">${{ number_format($this->tax, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-zinc-200 pt-2 dark:border-zinc-700">
                            <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Total</span>
                            <span class="text-lg font-normal text-zinc-900 dark:text-zinc-100">${{ number_format($this->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Notes</h2>
                </div>
                <div class="p-5">
                    <textarea 
                        wire:model="notes"
                        rows="3"
                        placeholder="Add any notes or special instructions..."
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"
                    ></textarea>
                </div>
            </div>
        </div>

        {{-- Right Column: History & Summary --}}
        <div class="space-y-6 lg:col-span-4">
            {{-- Order Summary --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Summary</h2>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Items</span>
                        <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ count(array_filter($items, fn($i) => $i['inventory_item_id'])) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Total Qty</span>
                        <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ collect($items)->sum('quantity') }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Subtotal</span>
                        <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">${{ number_format($this->subtotal, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Tax</span>
                        <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">${{ number_format($this->tax, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between bg-zinc-50 px-5 py-4 dark:bg-zinc-800/50">
                        <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Total</span>
                        <span class="text-lg font-normal text-zinc-900 dark:text-zinc-100">${{ number_format($this->total, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Customer Info (if selected) --}}
            @if($selectedCustomer)
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Customer</h2>
                    </div>
                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 text-sm font-normal text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ strtoupper(substr($selectedCustomer->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $selectedCustomer->name }}</p>
                                <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $selectedCustomer->email }}</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex items-start gap-2">
                                <svg class="mt-0.5 size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                </svg>
                                <span class="font-light text-zinc-600 dark:text-zinc-300">{{ $selectedCustomer->phone }}</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="mt-0.5 size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                                <span class="font-light text-zinc-600 dark:text-zinc-300">{{ $selectedCustomer->city }}, {{ $selectedCustomer->country }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Activity Log --}}
            @if($orderId && count($activityLog) > 0)
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Activity</h2>
                    </div>
                    <div class="p-5">
                        <div class="relative space-y-4">
                            {{-- Timeline line --}}
                            <div class="absolute left-2 top-2 h-[calc(100%-16px)] w-px bg-zinc-200 dark:bg-zinc-700"></div>
                            
                            @foreach($activityLog as $activity)
                                <div class="relative flex gap-4 pl-6">
                                    {{-- Dot --}}
                                    <div class="absolute left-0 top-1.5 h-4 w-4 rounded-full border-2 border-white bg-zinc-300 dark:border-zinc-900 dark:bg-zinc-600"></div>
                                    
                                    <div class="flex-1">
                                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $activity['message'] }}</p>
                                        <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">
                                            {{ $activity['user'] }} · {{ $activity['date'] }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Quick Actions --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Actions</h2>
                </div>
                <div class="p-3">
                    <div class="space-y-1">
                        <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-light text-zinc-600 transition-colors hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                            </svg>
                            Print Order
                        </button>
                        <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-light text-zinc-600 transition-colors hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                            Send to Customer
                        </button>
                        <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-light text-zinc-600 transition-colors hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                            </svg>
                            Duplicate Order
                        </button>
                        @if($orderId && $status === 'confirmed')
                            <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-light text-zinc-600 transition-colors hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                </svg>
                                Create Delivery
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
