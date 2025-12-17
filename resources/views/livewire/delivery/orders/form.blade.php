<div x-data="{ 
    activeTab: 'lines',
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false,
    showCancelModal: false
}">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            {{-- Left Group: Back Button, Title, Gear Dropdown --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('delivery.orders.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    @php
                        $so = null;
                        if (isset($delivery) && $delivery && $delivery->salesOrder) {
                            $so = $delivery->salesOrder;
                        } elseif (!empty($sales_order_id ?? null)) {
                            $so = $salesOrders->firstWhere('id', $sales_order_id);
                        }

                        $soNumber = $so?->order_number ?? ($so ? ('SO #' . $so->id) : null);
                        $soId = $so?->id;
                    @endphp
                    <div class="flex items-center gap-1 text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        <span>Delivery Order</span>
                        @if($soId && $soNumber)
                            <span>/</span>
                            <a href="{{ route('sales.orders.edit', $soId) }}" wire:navigate class="text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300">
                                {{ $soNumber }}
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $deliveryId ? ($delivery_number ?? 'Delivery') : 'New Delivery Order' }}
                            @if($soNumber)
                                <span class="text-zinc-400 dark:text-zinc-500">({{ $soNumber }})</span>
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

            @if($soId && $soNumber)
                <div class="flex items-center gap-2">
                    <a 
                        href="{{ route('sales.orders.edit', $soId) }}" 
                        wire:navigate
                        class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm font-medium text-amber-700 transition-colors hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50"
                    >
                        <flux:icon name="shopping-cart" class="size-4" />
                        <span>{{ $soNumber }}</span>
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
                    <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="printer" class="size-4" />
                        Print
                    </button>
                    @if($deliveryId && !in_array($status, ['delivered', 'returned']))
                        <button type="button" @click="showCancelModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
                            <flux:icon name="x-mark" class="size-4" />
                            Cancel
                        </button>
                    @endif
                </div>

                {{-- Stepper --}}
                @php
                    $steps = [
                        ['key' => 'pending', 'label' => 'Pending'],
                        ['key' => 'picked', 'label' => 'Picked'],
                        ['key' => 'in_transit', 'label' => 'In Transit'],
                        ['key' => 'delivered', 'label' => 'Delivered'],
                    ];
                    $currentIndex = collect($steps)->search(fn($s) => $s['key'] === $status);
                    if ($currentIndex === false) $currentIndex = 0;
                    $isFailed = $status === 'failed';
                    $isReturned = $status === 'returned';
                @endphp
                @if($isFailed)
                    <span class="inline-flex h-[38px] items-center rounded-lg bg-red-100 px-4 text-sm font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">Failed</span>
                @elseif($isReturned)
                    <span class="inline-flex h-[38px] items-center rounded-lg bg-amber-100 px-4 text-sm font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Returned</span>
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
            {{-- Left: Delivery Info --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="p-5">
                        <div class="mb-6">
                            <h2 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $deliveryId ? ($delivery_number ?? 'Delivery Order') : 'New Delivery Order' }}
                            </h2>
                        </div>

                        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                            {{-- Sales Order --}}
                            <div class="hidden">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Sales Order <span class="text-red-500">*</span></label>
                                <select wire:model="sales_order_id" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                    <option value="">Select sales order...</option>
                                    @foreach($salesOrders as $order)
                                        <option value="{{ $order->id }}">
                                            {{ $order->order_number }} - {{ $order->customer->name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Warehouse <span class="text-red-500">*</span></label>
                                <div class="relative" x-data="{ open: false, search: '' }">
                                    <button 
                                        type="button"
                                        @click="open = !open; $nextTick(() => { if(open) $refs.warehouseSearch.focus() })"
                                        class="flex w-full items-center justify-between rounded-lg border border-transparent bg-transparent px-4 py-2.5 text-left text-sm transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:hover:border-zinc-700"
                                    >
                                        @php
                                            $selectedWarehouse = $warehouses->firstWhere('id', $warehouse_id);
                                        @endphp
                                        @if($selectedWarehouse)
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                    {{ strtoupper(substr($selectedWarehouse->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $selectedWarehouse->name }}</p>
                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Warehouse</p>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-zinc-400">Select a warehouse...</span>
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
                                                x-ref="warehouseSearch"
                                                x-model="search"
                                                placeholder="Search warehouses..."
                                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                                @keydown.escape="open = false; search = ''"
                                            />
                                        </div>
                                        <div class="max-h-60 overflow-auto py-1">
                                            @foreach($warehouses as $warehouse)
                                                <button 
                                                    type="button"
                                                    x-show="'{{ strtolower($warehouse->name) }}'.includes(search.toLowerCase()) || search === ''"
                                                    wire:click="$set('warehouse_id', {{ $warehouse->id }})"
                                                    @click="open = false; search = ''"
                                                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $warehouse_id === $warehouse->id ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}"
                                                >
                                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                        {{ strtoupper(substr($warehouse->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $warehouse->name }}</p>
                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Warehouse</p>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                {{-- Delivery Date (Right) --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Delivery Date <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model="delivery_date" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                </div>

                                {{-- Actual Delivery Date (Right - below) --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Actual Date</label>
                                    <input type="date" wire:model="actual_delivery_date" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center border-y border-zinc-100 dark:border-zinc-800">
                        <button 
                            type="button"
                            @click="activeTab = 'lines'"
                            :class="activeTab === 'lines' ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                            class="px-5 py-3 text-sm font-medium transition-colors"
                        >
                            Delivery Lines
                        </button>
                        <button 
                            type="button"
                            @click="activeTab = 'recipient'"
                            :class="activeTab === 'recipient' ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                            class="px-5 py-3 text-sm font-medium transition-colors"
                        >
                            Recipient
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

                    <div x-show="activeTab === 'lines'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        @if($delivery && $delivery->items->count())
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Product</th>
                                            <th class="w-24 px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Ordered</th>
                                            <th class="w-28 px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">To Deliver</th>
                                            <th class="w-28 px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Delivered</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                                        @foreach($delivery->items as $item)
                                            <tr class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30">
                                                <td class="px-4 py-3">
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item->salesOrderItem->product->name ?? '-' }}</span>
                                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->salesOrderItem->product->sku ?? '—' }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-300">{{ $item->salesOrderItem->quantity ?? 0 }}</td>
                                                <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-300">{{ $item->quantity_to_deliver }}</td>
                                                <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-300">{{ $item->quantity_delivered }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="px-6 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No delivery lines yet.
                            </div>
                        @endif
                    </div>

                    <div x-show="activeTab === 'recipient'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="p-6">
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Recipient Name <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="recipient_name" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Recipient Phone</label>
                                    <input type="text" wire:model="recipient_phone" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Shipping Address</label>
                                    <textarea wire:model="shipping_address" rows="4" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-show="activeTab === 'other'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="p-6">
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Courier</label>
                                    <input type="text" wire:model="courier" placeholder="e.g., JNE, J&T" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Tracking Number</label>
                                    <input type="text" wire:model="tracking_number" placeholder="Tracking number" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Notes</label>
                                    <textarea wire:model="notes" rows="4" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Right: Activity --}}
            <div class="lg:col-span-3">
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

                @if($deliveryId)
                    <div class="flex items-center gap-3 py-2">
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Today</span>
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="relative flex-shrink-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-200 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name ?? 'User' }}</span>
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $delivery?->created_at?->format('H:i') ?? now()->format('H:i') }}</span>
                                </div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">Delivery Order created</p>
                            </div>
                        </div>

                        @if(isset($activityLog) && count($activityLog) > 0)
                            @foreach($activityLog as $activity)
                                <div class="flex gap-3">
                                    <div class="relative flex-shrink-0">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-200 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                            {{ strtoupper(substr($activity['user'] ?? 'U', 0, 2)) }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $activity['user'] }}</span>
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $activity['time'] ?? $activity['date'] }}</span>
                                        </div>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $activity['message'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @if($status !== 'pending')
                            <div class="flex gap-3">
                                <div class="relative flex-shrink-0">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-200 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name ?? 'User' }}</span>
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $delivery?->updated_at?->format('H:i') ?? now()->format('H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Status changed to <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="py-8 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon name="chat-bubble-left-right" class="size-6 text-zinc-400" />
                        </div>
                        <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No activity yet</p>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500">Activity will appear here once the delivery order is saved</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
