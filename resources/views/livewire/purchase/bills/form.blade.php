<div x-data="{ activeTab: 'products', showLogNote: false, showScheduleActivity: false, showCancelModal: false, showPaymentModal: $wire.entangle('showPaymentModal') }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            {{-- Left Group: Back Button, Title, Gear Dropdown --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('purchase.bills.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        Vendor Bill
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $billId ? $billNumber : 'New Bill' }}
                        </span>
                        @if($billId)
                            <flux:dropdown position="bottom" align="start">
                                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="cog-6-tooth" class="size-4" />
                                </button>
                                <flux:menu class="w-40">
                                    <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                        <flux:icon name="document-duplicate" class="size-4" />
                                        <span>Duplicate</span>
                                    </button>
                                    <flux:menu.separator />
                                    <button type="button" wire:click="delete" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                        <flux:icon name="trash" class="size-4" />
                                        <span>Delete</span>
                                    </button>
                                </flux:menu>
                            </flux:dropdown>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-slot:header>

    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">{{ session('error') }}</x-ui.alert>
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
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    @if(!$billId)
                        <button type="button" wire:click="save"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <flux:icon name="document-check" class="size-4" />
                            Save
                        </button>
                    @elseif($status === 'draft')
                        <button type="button" wire:click="confirm"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <flux:icon name="check" class="size-4" />
                            Confirm Bill
                        </button>
                        <button type="button" wire:click="save"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                            <flux:icon name="document-check" class="size-4" />
                            Save
                        </button>
                    @elseif(in_array($status, ['pending', 'partial', 'overdue']))
                        <button type="button" wire:click="openPaymentModal"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-700">
                            <flux:icon name="banknotes" class="size-4" />
                            Register Payment
                        </button>
                    @endif
                    @if($billId && $status !== 'cancelled' && $status !== 'paid')
                        <button type="button" @click="showCancelModal = true"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
                            <flux:icon name="x-mark" class="size-4" />
                            Cancel
                        </button>
                    @endif
                </div>

                {{-- Stepper --}}
                @php
                    $steps = \App\Enums\VendorBillState::steps();
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

            <div class="col-span-3 flex items-center justify-end gap-1">
                <x-ui.chatter-buttons :showMessage="false" />
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="p-5">
                        <h1 class="mb-5 text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $billId ? $billNumber : 'New' }}
                        </h1>

                        <div class="grid gap-6 sm:grid-cols-2">
                            {{-- Left Column: Supplier --}}
                            <div class="space-y-3">
                                <div>
                                    <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Supplier <span class="text-red-500">*</span></label>
                                    <div class="relative" x-data="{ open: false, search: '' }">
                                        <button type="button" @click="open = !open; $nextTick(() => { if(open) $refs.supplierSearch.focus() })"
                                            class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-left text-sm transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                            @disabled($status !== 'draft')>
                                            @php $selectedSupplier = $suppliers->firstWhere('id', $supplier_id); @endphp
                                            @if($selectedSupplier)
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                        {{ strtoupper(substr($selectedSupplier->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $selectedSupplier->name }}</p>
                                                        @if($selectedSupplier->email)
                                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $selectedSupplier->email }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-zinc-400">Select a supplier...</span>
                                            @endif
                                            <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                        </button>
                                        @if($status === 'draft')
                                        <div x-show="open" @click.outside="open = false; search = ''" x-transition
                                            class="absolute left-0 top-full z-[100] mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                            <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                                <input type="text" x-ref="supplierSearch" x-model="search" placeholder="Search suppliers..."
                                                    class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                                    @keydown.escape="open = false; search = ''" />
                                            </div>
                                            <div class="max-h-60 overflow-auto py-1">
                                                @foreach($suppliers as $supplier)
                                                    <button type="button"
                                                        x-show="'{{ strtolower($supplier->name) }}'.includes(search.toLowerCase()) || search === ''"
                                                        wire:click="$set('supplier_id', {{ $supplier->id }})"
                                                        @click="open = false; search = ''"
                                                        class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $supplier_id === $supplier->id ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}">
                                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                            {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                                        </div>
                                                        <div>
                                                            <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $supplier->name }}</p>
                                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $supplier->contact_person ?? $supplier->email ?? '—' }}</p>
                                                        </div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    @error('supplier_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <input type="text" wire:model="vendor_reference" placeholder="Vendor reference..." @disabled($status !== 'draft')
                                        class="w-full border-0 border-b border-transparent bg-transparent px-0 py-1.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:hover:border-zinc-700 disabled:opacity-50" />
                                </div>
                            </div>

                            {{-- Right Column: Dates --}}
                            <div class="space-y-3">
                                <div class="flex items-center gap-4">
                                    <label class="w-32 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Bill Date <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model="bill_date" @disabled($status !== 'draft')
                                        class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700 disabled:opacity-50" />
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="w-32 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Due Date</label>
                                    <input type="date" wire:model="due_date" @disabled($status !== 'draft')
                                        class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700 disabled:opacity-50" />
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="w-32 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Purchase Order</label>
                                    <select wire:model="purchase_rfq_id" @disabled($status !== 'draft')
                                        class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700 disabled:opacity-50">
                                        <option value="">No linked PO</option>
                                        @foreach($purchaseOrders as $po)
                                            <option value="{{ $po->id }}">{{ $po->reference }} - {{ $po->order_date->format('M d, Y') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Headers --}}
                    <div class="flex items-center border-y border-zinc-100 dark:border-zinc-800">
                        <button type="button" @click="activeTab = 'products'"
                            :class="activeTab === 'products' ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                            class="px-5 py-3 text-sm font-medium transition-colors">
                            Products
                        </button>
                        <button type="button" @click="activeTab = 'other'"
                            :class="activeTab === 'other' ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                            class="px-5 py-3 text-sm font-medium transition-colors">
                            Other Info
                        </button>
                    </div>

                    {{-- Products Tab --}}
                    <div 
                        x-show="activeTab === 'products'" 
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
                                subtotal: { label: 'Subtotal', visible: true, required: true },
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
                                        <th x-show="isColumnVisible('qty')" class="w-16 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Qty</th>
                                        <th x-show="isColumnVisible('unit_price')" class="w-32 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unit Price</th>
                                        <th x-show="isColumnVisible('subtotal')" class="px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Subtotal</th>
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
                                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                                    @forelse($items as $index => $item)
                                        <tr class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30" wire:key="item-{{ $index }}">
                                            {{-- Row Number --}}
                                            <td class="px-2 py-2">
                                                <div class="flex items-center justify-center text-xs text-zinc-400">
                                                    {{ $index + 1 }}
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
                                                            @disabled($status !== 'draft')
                                                        >
                                                            <div>
                                                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $item['name'] }}</p>
                                                                <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $item['sku'] ?? '' }}</p>
                                                            </div>
                                                        </button>
                                                    @else
                                                        <button 
                                                            type="button"
                                                            @click="open = true; $nextTick(() => $refs.productSearch{{ $index }}.focus())"
                                                            class="text-sm text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                                                            @disabled($status !== 'draft')
                                                        >
                                                            Select a product...
                                                        </button>
                                                    @endif

                                                    {{-- Product Dropdown --}}
                                                    @if($status === 'draft')
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
                                                            @foreach(\App\Models\Inventory\Product::where('status', 'active')->orderBy('name')->limit(50)->get() as $product)
                                                                <button 
                                                                    type="button"
                                                                    x-show="'{{ strtolower($product->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($product->sku ?? '') }}'.includes(search.toLowerCase()) || search === ''"
                                                                    wire:click="selectProduct({{ $index }}, {{ $product->id }})"
                                                                    @click="open = false; search = ''"
                                                                    class="flex w-full items-center gap-3 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                                                >
                                                                    <div class="flex-1">
                                                                        <p class="text-zinc-900 dark:text-zinc-100">{{ $product->name }}</p>
                                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product->sku ?? 'No SKU' }} · Rp {{ number_format($product->cost_price ?? 0, 0, ',', '.') }}</p>
                                                                    </div>
                                                                    <span class="text-xs text-zinc-400">{{ $product->quantity ?? 0 }} in stock</span>
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- Description --}}
                                            <td x-show="isColumnVisible('description')" class="px-3 py-2">
                                                <input 
                                                    type="text"
                                                    wire:model.live="items.{{ $index }}.description"
                                                    placeholder="Add description..."
                                                    class="w-full bg-transparent text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none dark:text-zinc-100"
                                                    @disabled($status !== 'draft')
                                                />
                                            </td>

                                            {{-- Quantity --}}
                                            <td x-show="isColumnVisible('qty')" class="w-16 px-3 py-2">
                                                <input 
                                                    type="text"
                                                    wire:model.live="items.{{ $index }}.quantity"
                                                    class="w-full bg-transparent text-right text-sm text-zinc-900 focus:outline-none dark:text-zinc-100"
                                                    @disabled($status !== 'draft')
                                                />
                                            </td>

                                            {{-- Unit Price --}}
                                            <td x-show="isColumnVisible('unit_price')" class="w-32 px-3 py-2">
                                                <input 
                                                    type="text"
                                                    wire:model.live="items.{{ $index }}.unit_price"
                                                    class="w-full bg-transparent text-right text-sm text-zinc-900 focus:outline-none dark:text-zinc-100"
                                                    @disabled($status !== 'draft')
                                                />
                                            </td>

                                            {{-- Subtotal --}}
                                            <td x-show="isColumnVisible('subtotal')" class="px-3 py-2 text-right">
                                                <span class="text-sm text-zinc-900 dark:text-zinc-100">Rp {{ number_format($item['total'], 0, ',', '.') }}</span>
                                            </td>

                                            {{-- Remove --}}
                                            <td class="pl-2 pr-2 py-2 text-right">
                                                @if(count($items) > 1 && $status === 'draft')
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
                                @if($status === 'draft')
                                    <button 
                                        type="button"
                                        wire:click="addItem"
                                        class="inline-flex items-center gap-1.5 text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                                    >
                                        <flux:icon name="plus" class="size-4" />
                                        Add a line
                                    </button>
                                @else
                                    <span class="inline-flex cursor-not-allowed items-center gap-1.5 text-sm text-zinc-300 dark:text-zinc-600">
                                        <flux:icon name="lock-closed" class="size-4" />
                                        Items locked
                                    </span>
                                @endif
                                @error('items') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Totals Row --}}
                        <div class="border-t border-zinc-100 bg-zinc-50/50 p-5 dark:border-zinc-800 dark:bg-zinc-900/30">
                            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                                {{-- Notes (Left Side) --}}
                                <div class="flex-1">
                                    <textarea 
                                        wire:model="notes"
                                        rows="3"
                                        placeholder="Internal notes..."
                                        class="w-full resize-none border-0 bg-transparent px-0 py-0 text-sm text-zinc-700 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-300 dark:placeholder-zinc-500"
                                        @disabled($status !== 'draft')
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

                    {{-- Other Info Tab --}}
                    <div x-show="activeTab === 'other'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="p-6">
                            <div class="space-y-4">
                                <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Additional Information</h3>
                                <div class="flex items-center gap-4">
                                    <label class="w-32 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Created</label>
                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $createdAt ?? '-' }}</span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="w-32 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Updated</label>
                                    <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $updatedAt ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Sidebar: Chatter --}}
            <div class="lg:col-span-3">
                {{-- Chatter Forms --}}
                <x-ui.chatter-forms :showMessage="false" />

                {{-- Activity Timeline --}}
                @if($billId)
                    <div class="flex items-center gap-3 py-2">
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                            @if($this->activitiesAndNotes->isNotEmpty() && $this->activitiesAndNotes->first()['created_at']->isToday())
                                Today
                            @else
                                Activity
                            @endif
                        </span>
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    </div>

                    <div class="space-y-3">
                        @forelse($this->activitiesAndNotes as $item)
                            @if($item['type'] === 'note')
                                <x-ui.note-item :note="$item['data']" />
                            @else
                                <x-ui.activity-item :activity="$item['data']" emptyMessage="Vendor bill created" />
                            @endif
                        @empty
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-ui.user-name :user="auth()->user()" />
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $createdAt ?? now()->format('H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Vendor bill created</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @else
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
    <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/50" @click="showCancelModal = false"></div>
        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            @click.outside="showCancelModal = false">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon name="x-circle" class="size-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Cancel Bill</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">This action cannot be undone.</p>
                </div>
            </div>
            <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">
                Are you sure you want to cancel this vendor bill? This will mark the bill as cancelled.
            </p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="showCancelModal = false"
                    class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                    Keep Bill
                </button>
                <button type="button" wire:click="cancel" @click="showCancelModal = false"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700">
                    Cancel Bill
                </button>
            </div>
        </div>
    </div>

    {{-- Payment Modal --}}
    <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/50" @click="showPaymentModal = false"></div>
        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            @click.outside="showPaymentModal = false">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                    <flux:icon name="banknotes" class="size-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Register Payment</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Record a payment for this bill.</p>
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Payment Date</label>
                    <input type="date" wire:model="paymentDate"
                        class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Amount</label>
                    <input type="number" wire:model="paymentAmount" step="0.01" min="0"
                        class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                    @error('paymentAmount') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Payment Method</label>
                    <select wire:model="paymentMethod"
                        class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="check">Check</option>
                        <option value="credit_card">Credit Card</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Reference</label>
                    <input type="text" wire:model="paymentReference" placeholder="Transaction reference"
                        class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="showPaymentModal = false"
                    class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                    Cancel
                </button>
                <button type="button" wire:click="registerPayment"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-700">
                    Register Payment
                </button>
            </div>
        </div>
    </div>
</div>
