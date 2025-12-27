<div x-data="{ activeTab: 'lines', showLogNote: false, showSendMessage: false, showScheduleActivity: false, showCancelModal: false }">
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
                        $soStatus = $so?->status;
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
                                @if($deliveryId)
                                <a href="{{ route('pdf.delivery-order', $deliveryId) }}" target="_blank" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
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

            @php
                $wh = null;
                if (isset($delivery) && $delivery && $delivery->warehouse) {
                    $wh = $delivery->warehouse;
                } elseif (!empty($warehouse_id ?? null)) {
                    $wh = $warehouses->firstWhere('id', $warehouse_id);
                }
                $whName = $wh?->name;
            @endphp

            @if((isset($outboundAdjustment) && $outboundAdjustment) || ($soId && $soNumber))
                <div class="flex items-center gap-2">
                    @if(isset($outboundAdjustment) && $outboundAdjustment)
                        <a
                            href="{{ route('inventory.warehouse-out.edit', $outboundAdjustment->id) }}"
                            wire:navigate
                            class="inline-flex items-center gap-2 rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-sm font-medium text-sky-700 transition-colors hover:bg-sky-100 dark:border-sky-800 dark:bg-sky-900/30 dark:text-sky-400 dark:hover:bg-sky-900/50"
                        >
                            <flux:icon name="building-storefront" class="size-4" />
                            <span>{{ $outboundAdjustment->adjustment_number }}</span>
                            @if(!empty($outboundAdjustment->status))
                                @php
                                    $outboundStatusConfig = match($outboundAdjustment->status) {
                                        'draft' => ['bg' => 'bg-zinc-200 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300'],
                                        'pending' => ['bg' => 'bg-blue-200 dark:bg-blue-800', 'text' => 'text-blue-700 dark:text-blue-300'],
                                        'approved' => ['bg' => 'bg-violet-200 dark:bg-violet-800', 'text' => 'text-violet-700 dark:text-violet-300'],
                                        'completed' => ['bg' => 'bg-emerald-200 dark:bg-emerald-800', 'text' => 'text-emerald-700 dark:text-emerald-300'],
                                        'cancelled' => ['bg' => 'bg-red-200 dark:bg-red-800', 'text' => 'text-red-700 dark:text-red-300'],
                                        default => ['bg' => 'bg-zinc-200 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300'],
                                    };
                                @endphp
                                <span class="rounded px-1.5 py-0.5 text-xs font-medium {{ $outboundStatusConfig['bg'] }} {{ $outboundStatusConfig['text'] }}">
                                    {{ ucfirst(str_replace('_', ' ', $outboundAdjustment->status)) }}
                                </span>
                            @endif
                        </a>
                    @endif

                    @if($soId && $soNumber)
                        <a 
                            href="{{ route('sales.orders.edit', $soId) }}" 
                            wire:navigate
                            class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm font-medium text-amber-700 transition-colors hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50"
                        >
                            <flux:icon name="shopping-cart" class="size-4" />
                            <span>{{ $soNumber }}</span>
                            @if(!empty($soStatus))
                                @php
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
                            @endif
                        </a>
                    @endif
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
        @if(session('error'))
            <x-ui.alert type="error" :duration="10000">
                {{ session('error') }}
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
                    @php
                        $nextStatusLabel = \App\Enums\DeliveryOrderState::tryFrom($status)?->nextActionLabel();
                    @endphp
                    @if($deliveryId && $nextStatusLabel)
                        <button type="button" wire:click="openStatusTransitionModal" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <flux:icon name="check" class="size-4" />
                            {{ $nextStatusLabel }}
                        </button>
                    @endif
                    <button type="button" wire:click="save" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="document-check" class="size-4" />
                        Save
                    </button>
                    @if($deliveryId && $status === \App\Enums\DeliveryOrderState::DELIVERED->value)
                        <button type="button" wire:click="openReturnModal" class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-amber-700">
                            <flux:icon name="arrow-uturn-left" class="size-4" />
                            Return
                        </button>
                    @endif
                    @if($deliveryId)
                        <a href="{{ route('pdf.delivery-order', $deliveryId) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            Download PDF
                        </a>
                    @endif
                    @if($deliveryId && !in_array($status, [\App\Enums\DeliveryOrderState::DELIVERED->value, \App\Enums\DeliveryOrderState::RETURNED->value, \App\Enums\DeliveryOrderState::CANCELLED->value]))
                        <button type="button" @click="showCancelModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
                            <flux:icon name="x-mark" class="size-4" />
                            Cancel
                        </button>
                    @endif
                </div>

                {{-- Stepper --}}
                @php
                    $steps = \App\Enums\DeliveryOrderState::steps();
                    $currentIndex = collect($steps)->search(fn($s) => $s['key'] === $status);
                    if ($currentIndex === false) $currentIndex = 0;
                    $isFailed = $status === \App\Enums\DeliveryOrderState::FAILED->value;
                    $isReturned = $status === \App\Enums\DeliveryOrderState::RETURNED->value;
                    $isCancelled = $status === \App\Enums\DeliveryOrderState::CANCELLED->value;
                @endphp
                @if($isCancelled)
                    <span class="inline-flex h-[38px] items-center rounded-lg bg-red-100 px-4 text-sm font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">Cancelled</span>
                @elseif($isFailed)
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
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="flex flex-col">
                                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item->salesOrderItem->product->name ?? '-' }}</span>
                                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->salesOrderItem->product->sku ?? '—' }}</span>
                                                        </div>

                                                        @php
                                                            $pid = $item->salesOrderItem?->product?->id;
                                                        @endphp
                                                        @if($deliveryId && $pid)
                                                            <button
                                                                type="button"
                                                                wire:click="openForecastModal({{ (int) $pid }})"
                                                                class="hidden shrink-0 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-50 group-hover:inline-flex dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                                            >
                                                                Forecast
                                                            </button>
                                                        @endif
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

                        @if(isset($returns) && $returns->count())
                            <div class="border-t border-zinc-100 p-5 dark:border-zinc-800">
                                <div class="mb-3 flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Returns</h3>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Return</th>
                                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Warehouse</th>
                                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Date</th>
                                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                                                <th class="w-28 px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                                            @foreach($returns as $ret)
                                                <tr class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30">
                                                    <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                                        {{ $ret->return_number }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                                        {{ $ret->warehouse?->name ?? '-' }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                                        {{ $ret->return_date?->format('Y-m-d') ?? '-' }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                                        {{ ucfirst($ret->status) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        @if($ret->status === 'draft')
                                                            <button type="button" wire:click="receiveReturn({{ $ret->id }})" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                                                                Receive
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
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

    @if($showReturnModal)
        <div class="fixed inset-0 z-[400] flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-3xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3 dark:border-zinc-800">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Delivery Return</div>
                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Create Return</div>
                    </div>
                    <button type="button" wire:click="closeReturnModal" class="rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <div class="space-y-4 p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Warehouse <span class="text-red-500">*</span></label>
                            <select wire:model="return_warehouse_id" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                <option value="">Select warehouse...</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Return Date <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="return_date" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Notes</label>
                            <textarea rows="2" wire:model="return_notes" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-800">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                                    <th class="px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Product</th>
                                    <th class="w-28 px-4 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Delivered</th>
                                    <th class="w-28 px-4 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Returned</th>
                                    <th class="w-28 px-4 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Max</th>
                                    <th class="w-32 px-4 py-2.5 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Qty</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($return_items as $i => $row)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $row['product_name'] ?? '-' }}</div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $row['sku'] ?? '' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-300">{{ (int) ($row['delivered'] ?? 0) }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-300">{{ (int) ($row['already_returned'] ?? 0) }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-300">{{ (int) ($row['max'] ?? 0) }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <input
                                                type="number"
                                                min="0"
                                                max="{{ (int) ($row['max'] ?? 0) }}"
                                                wire:model.live="return_items.{{ $i }}.quantity"
                                                @disabled(((int) ($row['max'] ?? 0)) <= 0)
                                                class="w-24 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-right focus:border-zinc-400 focus:outline-none disabled:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:disabled:bg-zinc-800/50"
                                            />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 bg-zinc-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                    <button type="button" wire:click="closeReturnModal" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                        Cancel
                    </button>
                    <button type="button" wire:click="createReturn" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        Create Return
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($showStatusModal)
        <div class="fixed inset-0 z-[410] flex items-center justify-center">
            <div class="absolute inset-0 bg-zinc-900/60"></div>

            <div class="relative w-full max-w-4xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl ring-1 ring-black/5 dark:border-zinc-800 dark:bg-zinc-900 dark:ring-white/10">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 bg-zinc-50 px-6 py-5 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $status_modal_title }}</h3>
                        <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Review the details before confirming this action</p>
                    </div>
                    <button type="button" wire:click="closeStatusTransitionModal" class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" aria-label="Close">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <div class="space-y-4 px-6 py-5">
                    @php
                        $statusModalBanner = $status_modal_can_confirm
                            ? ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'border' => 'border-emerald-200 dark:border-emerald-900', 'text' => 'text-emerald-800 dark:text-emerald-200', 'icon' => 'check-circle']
                            : ['bg' => 'bg-red-50 dark:bg-red-900/20', 'border' => 'border-red-200 dark:border-red-900', 'text' => 'text-red-800 dark:text-red-200', 'icon' => 'exclamation-triangle'];
                    @endphp

                    <div class="flex items-start gap-3 rounded-xl border p-4 text-sm {{ $statusModalBanner['bg'] }} {{ $statusModalBanner['border'] }} {{ $statusModalBanner['text'] }}">
                        <flux:icon :name="$statusModalBanner['icon']" class="mt-0.5 size-5" />
                        <div class="min-w-0">
                            <div class="font-medium">{{ $status_modal_message }}</div>
                            @if(!$status_modal_can_confirm && !empty($status_modal_shortages))
                                <div class="mt-1 text-xs opacity-90">Resolve stock shortages before proceeding.</div>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                        <div class="mb-3 flex items-center justify-between">
                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Summary</div>
                        </div>

                        <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Delivery Order</dt>
                                <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $status_modal_summary['delivery_number'] ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Next Status</dt>
                                <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $status_modal_summary['next_status'] ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Customer</dt>
                                <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $status_modal_summary['customer_name'] ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Warehouse</dt>
                                <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $status_modal_summary['warehouse_name'] ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Total Qty</dt>
                                <dd class="mt-0.5 font-medium text-zinc-900 dark:text-zinc-100">{{ (int) ($status_modal_summary['total_qty'] ?? 0) }}</dd>
                            </div>
                        </dl>
                    </div>

                    @if(!$status_modal_can_confirm && !empty($status_modal_shortages))
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-900/20">
                            <div class="flex items-center gap-2 text-sm font-medium text-red-800 dark:text-red-200">
                                <flux:icon name="exclamation-triangle" class="size-4" />
                                Insufficient stock
                            </div>
                            <div class="mt-1 text-xs text-red-700 dark:text-red-300">These items do not have enough available quantity.</div>

                            <div class="mt-3 space-y-2">
                                @foreach($status_modal_shortages as $s)
                                    <div class="rounded-lg border border-red-200/70 bg-white px-3 py-2 dark:border-red-900/60 dark:bg-zinc-950">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $s['product_name'] }}</div>
                                            </div>
                                            <span class="shrink-0 rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                                Remaining {{ (int) ($s['available'] ?? 0) }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <button type="button" wire:click="closeStatusTransitionModal" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        Cancel
                    </button>
                    <button type="button" wire:click="confirmStatusTransition" @disabled(!$status_modal_can_confirm) class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="check" class="size-4" />
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($showForecastModal)
        <div class="fixed inset-0 z-[420] flex items-center justify-center">
            <div class="absolute inset-0 bg-zinc-900/60"></div>

            <div class="relative w-full max-w-4xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl ring-1 ring-black/5 dark:border-zinc-800 dark:bg-zinc-900 dark:ring-white/10">
                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 bg-zinc-50 px-6 py-5 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Forecast</h3>
                        <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Stock visibility for this product and warehouse</p>
                    </div>
                    <button type="button" wire:click="closeForecastModal" class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" aria-label="Close">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <div class="space-y-4 px-6 py-5">
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $forecast_data['product_name'] ?? '-' }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $forecast_data['sku'] ?? '' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">Warehouse</div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $forecast_data['warehouse_name'] ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-4">
                            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">On Hand</div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ (int) ($forecast_data['on_hand'] ?? 0) }}</div>
                            </div>
                            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">Forecast In</div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ (int) ($forecast_data['forecast_in'] ?? 0) }}</div>
                            </div>
                            <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">Forecast Out</div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ (int) ($forecast_data['forecast_out'] ?? 0) }}</div>
                            </div>
                            <div class="rounded-lg bg-emerald-50 px-3 py-2 dark:bg-emerald-900/20">
                                <div class="text-xs text-emerald-700/80 dark:text-emerald-200/80">Available</div>
                                <div class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ (int) ($forecast_data['available'] ?? 0) }}</div>
                            </div>
                        </div>

                        <div class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">
                            This DO qty: <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ (int) ($forecast_data['this_do_qty'] ?? 0) }}</span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                        <div class="mb-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">Related WH/OUT (this Delivery Order)</div>

                        @if(!empty($forecast_data['outbound_adjustment']))
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <a
                                    href="{{ route('inventory.warehouse-out.edit', (int) ($forecast_data['outbound_adjustment']['id'] ?? 0)) }}"
                                    wire:navigate
                                    class="inline-flex items-center gap-2 rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-sm font-medium text-sky-700 transition-colors hover:bg-sky-100 dark:border-sky-800 dark:bg-sky-900/30 dark:text-sky-400 dark:hover:bg-sky-900/50"
                                >
                                    <flux:icon name="building-storefront" class="size-4" />
                                    <span>{{ $forecast_data['outbound_adjustment']['adjustment_number'] ?? 'WH/OUT' }}</span>
                                </a>

                                <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                    Qty: <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ (int) ($forecast_data['outbound_adjustment']['item_qty'] ?? 0) }}</span>
                                    @if(!empty($forecast_data['outbound_adjustment']['posted_at']))
                                        <span class="ml-2 rounded bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">Posted</span>
                                    @else
                                        <span class="ml-2 rounded bg-zinc-200 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">Not posted</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">No WH/OUT created yet for this Delivery Order.</div>
                        @endif
                    </div>

                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                        <div class="mb-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">Other reservations (same warehouse)</div>

                        @if(!empty($forecast_data['reservations']))
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Delivery</th>
                                            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                                            <th class="px-3 py-2 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Qty</th>
                                            <th class="px-3 py-2 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">WH/OUT</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                                        @foreach($forecast_data['reservations'] as $row)
                                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30">
                                                <td class="px-3 py-2 text-sm">
                                                    <a href="{{ route('delivery.orders.edit', (int) ($row['delivery_order_id'] ?? 0)) }}" wire:navigate class="font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300">
                                                        {{ $row['delivery_number'] ?? ('DO #' . (int) ($row['delivery_order_id'] ?? 0)) }}
                                                    </a>
                                                </td>
                                                <td class="px-3 py-2 text-sm text-zinc-600 dark:text-zinc-300">{{ ucfirst(str_replace('_', ' ', (string) ($row['status'] ?? ''))) }}</td>
                                                <td class="px-3 py-2 text-right text-sm text-zinc-600 dark:text-zinc-300">{{ (int) ($row['qty'] ?? 0) }}</td>
                                                <td class="px-3 py-2 text-right">
                                                    @if(!empty($row['has_posted_whout']))
                                                        <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">Posted</span>
                                                    @else
                                                        <span class="rounded bg-zinc-200 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">Not posted</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">No other reservations found.</div>
                        @endif
                    </div>

                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
                        <div class="mb-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">WH/OUT history (this product)</div>

                        @if(!empty($forecast_data['wh_out_history']))
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">WH/OUT</th>
                                            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Reason</th>
                                            <th class="px-3 py-2 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Qty</th>
                                            <th class="px-3 py-2 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Posted</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                                        @foreach($forecast_data['wh_out_history'] as $row)
                                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30">
                                                <td class="px-3 py-2 text-sm">
                                                    <a
                                                        href="{{ route('inventory.warehouse-out.edit', (int) ($row['id'] ?? 0)) }}"
                                                        wire:navigate
                                                        class="font-medium text-sky-700 hover:text-sky-800 dark:text-sky-400 dark:hover:text-sky-300"
                                                    >
                                                        {{ $row['adjustment_number'] ?? ('WH/OUT #' . (int) ($row['id'] ?? 0)) }}
                                                    </a>
                                                </td>
                                                <td class="px-3 py-2 text-sm text-zinc-600 dark:text-zinc-300">{{ $row['reason'] ?? '' }}</td>
                                                <td class="px-3 py-2 text-right text-sm text-zinc-600 dark:text-zinc-300">{{ (int) ($row['qty'] ?? 0) }}</td>
                                                <td class="px-3 py-2 text-right">
                                                    @if(!empty($row['posted_at']))
                                                        <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">Yes</span>
                                                    @else
                                                        <span class="rounded bg-zinc-200 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">No</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">No WH/OUT history found for this product.</div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <button type="button" wire:click="closeForecastModal" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

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
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Cancel Delivery Order</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">This action cannot be undone.</p>
                </div>
            </div>
            
            <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">
                Are you sure you want to cancel this delivery order? The delivery order will be marked as cancelled and cannot be modified.
            </p>
            
            <div class="flex justify-end gap-3">
                <button 
                    type="button"
                    @click="showCancelModal = false"
                    class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                >
                    Keep Delivery
                </button>
                <button 
                    type="button"
                    wire:click="cancel"
                    @click="showCancelModal = false"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700"
                >
                    Cancel Delivery
                </button>
            </div>
        </div>
    </div>
</div>
