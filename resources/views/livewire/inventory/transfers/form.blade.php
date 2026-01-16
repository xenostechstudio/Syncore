<div x-data="{ activeTab: 'items', showLogNote: false, showSendMessage: false, showScheduleActivity: false, showCancelModal: false }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            {{-- Left Group: Back Button, Title, Gear Dropdown --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.transfers.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Internal Transfer</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $editing && $transfer ? $transfer->transfer_number : 'New Internal Transfer' }}
                        </span>

                        {{-- Header actions dropdown --}}
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
        </div>
    </x-slot:header>

    {{-- Flash Messages & Validation Errors --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
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
            {{-- Left: Action Buttons (col-span-9 to align with card below) --}}
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="document-check" wire:loading.remove wire:target="save" class="size-4" />
                        <flux:icon name="arrow-path" wire:loading wire:target="save" class="size-4 animate-spin" />
                        <span wire:loading.remove wire:target="save">Save</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                    <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="printer" class="size-4" />
                        Print
                    </button>
                    @if($transferId && $status !== 'cancelled')
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
                        ['key' => 'pending', 'label' => 'Pending'],
                        ['key' => 'in_transit', 'label' => 'In Transit'],
                        ['key' => 'completed', 'label' => 'Completed'],
                    ];
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
                <button 
                    @click="showSendMessage = !showSendMessage; showLogNote = false; showScheduleActivity = false" 
                    :class="showSendMessage ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'"
                    class="rounded-lg p-2 transition-colors" 
                    title="Send message"
                >
                    <flux:icon name="chat-bubble-left" class="size-5" />
                </button>
                <button 
                    @click="showLogNote = !showLogNote; showSendMessage = false; showScheduleActivity = false" 
                    :class="showLogNote ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'"
                    class="rounded-lg p-2 transition-colors" 
                    title="Log note"
                >
                    <flux:icon name="pencil-square" class="size-5" />
                </button>
                <button 
                    @click="showScheduleActivity = !showScheduleActivity; showSendMessage = false; showLogNote = false" 
                    :class="showScheduleActivity ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'"
                    class="rounded-lg p-2 transition-colors" 
                    title="Schedule activity"
                >
                    <flux:icon name="clock" class="size-5" />
                </button>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        {{-- Two Column Layout: Form Left, History Right --}}
        <div class="grid gap-6 lg:grid-cols-12">
        {{-- Left Column: Main Form --}}
        <div class="lg:col-span-9">
            {{-- Unified Transfer Card --}}
            <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                {{-- Transfer Info Section --}}
                <div class="p-5">
                    {{-- Title inside card --}}
                    <h1 class="mb-5 text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $editing && $transfer ? $transfer->transfer_number : 'New Internal Transfer' }}
                    </h1>
                    
                    <div class="grid gap-6 sm:grid-cols-2">
                        {{-- Source Warehouse Selection --}}
                        <div>
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Source Warehouse <span class="text-red-500">*</span></label>
                            <div class="relative" x-data="{ open: false }">
                                <button 
                                    type="button"
                                    @click="open = !open"
                                    class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-left text-sm transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                >
                                    @php $sourceWh = $warehouses->firstWhere('id', $source_warehouse_id); @endphp
                                    @if($sourceWh)
                                        <span class="font-normal text-zinc-900 dark:text-zinc-100">{{ $sourceWh->name }}</span>
                                    @else
                                        <span class="text-zinc-400">Select source warehouse...</span>
                                    @endif
                                    <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                </button>
                                <div x-show="open" @click.outside="open = false" x-transition class="absolute left-0 top-full z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                    @foreach($warehouses as $warehouse)
                                        <button type="button" wire:click="$set('source_warehouse_id', {{ $warehouse->id }})" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">{{ $warehouse->name }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Dates --}}
                        <div class="space-y-3">
                            {{-- Transfer Date --}}
                            <div class="flex items-center gap-4">
                                <label class="w-28 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Transfer Date</label>
                                <div class="relative flex-1">
                                    <input type="date" wire:model="transfer_date" class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700" />
                                </div>
                            </div>

                            {{-- Expected Arrival --}}
                            <div class="flex items-center gap-4">
                                <label class="w-28 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Expected Arrival</label>
                                <div class="relative flex-1">
                                    <input type="date" wire:model="expected_arrival_date" class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700" />
                                </div>
                            </div>
                        </div>

                        {{-- Destination Warehouse Selection --}}
                        <div>
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Destination Warehouse <span class="text-red-500">*</span></label>
                            <div class="relative" x-data="{ open: false }">
                                <button 
                                    type="button"
                                    @click="open = !open"
                                    class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-left text-sm transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                >
                                    @php $destWh = $warehouses->firstWhere('id', $destination_warehouse_id); @endphp
                                    @if($destWh)
                                        <span class="font-normal text-zinc-900 dark:text-zinc-100">{{ $destWh->name }}</span>
                                    @else
                                        <span class="text-zinc-400">Select destination warehouse...</span>
                                    @endif
                                    <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                </button>
                                <div x-show="open" @click.outside="open = false" x-transition class="absolute left-0 top-full z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                    @foreach($warehouses as $warehouse)
                                        <button type="button" wire:click="$set('destination_warehouse_id', {{ $warehouse->id }})" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">{{ $warehouse->name }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab Headers: Transfer Items & Other Info --}}
                <div class="flex items-center border-y border-zinc-100 dark:border-zinc-800">
                    <button 
                        type="button"
                        @click="activeTab = 'items'"
                        :class="activeTab === 'items' ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                        class="px-5 py-3 text-sm font-medium transition-colors"
                    >
                        Transfer Items
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
                
                {{-- Tab Content: Transfer Items --}}
                <div x-show="activeTab === 'items'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    {{-- Items Table --}}
                    <div class="overflow-visible">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    <th class="w-10 px-2 py-2.5"></th>
                                    <th class="w-48 px-3 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Product</th>
                                    <th class="px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Qty</th>
                                    <th class="w-10 pl-2 pr-2 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                                @forelse($items as $index => $item)
                                    <tr class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30" wire:key="item-{{ $index }}">
                                        {{-- Drag Handle --}}
                                        <td class="px-2 py-2">
                                            <div class="flex cursor-grab items-center justify-center text-zinc-300 transition-opacity hover:text-zinc-500 dark:text-zinc-600 dark:hover:text-zinc-400">
                                                <svg class="size-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                                                </svg>
                                            </div>
                                        </td>

                                        {{-- Product Selection --}}
                                        <td class="w-48 px-3 py-2 overflow-visible">
                                            <div x-data="{ open: false, search: '' }" class="relative">
                                                @if($item['product_id'])
                                                    <button type="button" @click="open = true" class="flex w-full items-center gap-2 text-left">
                                                        <div>
                                                            <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $item['name'] }}</p>
                                                            <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $item['sku'] }}</p>
                                                        </div>
                                                    </button>
                                                @else
                                                    <button type="button" @click="open = true" class="text-sm text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                                                        Select a product...
                                                    </button>
                                                @endif

                                                {{-- Product Dropdown --}}
                                                <div x-show="open" @click.outside="open = false; search = ''" x-transition class="absolute left-0 top-full z-[200] mt-1 w-80 rounded-lg border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                                                    <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                                        <input type="text" x-model="search" placeholder="Search products..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                                    </div>
                                                    <div class="max-h-48 overflow-auto py-1">
                                                        @foreach($products as $product)
                                                            <button type="button" x-show="'{{ strtolower($product->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($product->sku ?? '') }}'.includes(search.toLowerCase()) || search === ''" wire:click="selectProduct({{ $index }}, {{ $product->id }})" @click="open = false; search = ''" class="flex w-full items-center gap-3 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                                                <div class="flex-1">
                                                                    <p class="text-zinc-900 dark:text-zinc-100">{{ $product->name }}</p>
                                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product->sku }} · {{ $product->quantity }} in stock</p>
                                                                </div>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Quantity --}}
                                        <td class="px-3 py-2">
                                            <input type="text" wire:model.live="items.{{ $index }}.quantity" class="w-full bg-transparent text-right text-sm text-zinc-900 focus:outline-none dark:text-zinc-100" />
                                        </td>

                                        {{-- Remove --}}
                                        <td class="pl-2 pr-2 py-2 text-right">
                                            @if(count($items) > 1)
                                                <button type="button" wire:click="removeItem({{ $index }})" class="rounded p-1 text-zinc-300 opacity-0 transition-all hover:text-red-500 group-hover:opacity-100 dark:text-zinc-600 dark:hover:text-red-400">
                                                    <flux:icon name="trash" class="size-4" />
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-zinc-400">
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
                            <button type="button" wire:click="addItem" class="inline-flex items-center gap-1.5 text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                                <flux:icon name="plus" class="size-4" />
                                Add a line
                            </button>
                            @error('items') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Notes Row --}}
                    <div class="border-t border-zinc-100 bg-zinc-50/50 p-5 dark:border-zinc-800 dark:bg-zinc-900/30">
                        <div class="flex-1">
                            <textarea wire:model="notes" rows="3" placeholder="Notes..." class="w-full resize-none border-0 bg-transparent px-0 py-0 text-sm text-zinc-700 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-300 dark:placeholder-zinc-500"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Tab Content: Other Info --}}
                <div x-show="activeTab === 'other'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="p-6">
                        <div class="grid gap-8 lg:grid-cols-2">
                            {{-- Transfer Details Section --}}
                            <div class="space-y-4">
                                <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Transfer Details</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Reference</label>
                                        <input type="text" placeholder="Internal reference..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Responsible</label>
                                        <input type="text" value="{{ auth()->user()->name ?? '' }}" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </div>
                                </div>
                            </div>

                            {{-- Shipping Section --}}
                            <div class="space-y-4">
                                <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Shipping</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Carrier</label>
                                        <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            <option value="">Select carrier...</option>
                                            <option value="1">Internal</option>
                                            <option value="2">External Courier</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Tracking Number</label>
                                        <input type="text" placeholder="Tracking number..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
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
            {{-- Chatter Forms --}}
            <x-ui.chatter-forms />

            {{-- Activity Timeline --}}
            @if($transferId)
                {{-- Date Separator --}}
                <div class="flex items-center gap-3 py-2">
                    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Activity</span>
                    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                </div>

                {{-- Activity Items --}}
                <div class="space-y-3">
                    @if(isset($activities) && $activities->isNotEmpty())
                        @foreach($activities as $item)
                            @if($item['type'] === 'note')
                                {{-- Note Item - Compact --}}
                                <x-ui.note-item :note="$item['data']" />
                            @else
                                {{-- Activity Log Item --}}
                                <x-ui.activity-item :activity="$item['data']" emptyMessage="Transfer created" />
                            @endif
                        @endforeach
                    @else
                        {{-- Fallback when no activities yet --}}
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <x-ui.user-name :user="auth()->user()" />
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ now()->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">Transfer created</p>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                {{-- Empty State for New Transfer --}}
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
    <x-ui.confirm-modal show="showCancelModal">
        <x-slot:icon>
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                <flux:icon name="exclamation-triangle" class="size-7" />
            </div>
        </x-slot:icon>
        <x-slot:title>Cancel this transfer?</x-slot:title>
        <x-slot:description>This action will cancel the transfer. Are you sure you want to proceed?</x-slot:description>
        <x-slot:actions>
            <button type="button" @click="showCancelModal = false" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">Keep Transfer</button>
            <a href="{{ route('inventory.transfers.index') }}" wire:navigate class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600">Cancel Transfer</a>
        </x-slot:actions>
    </x-ui.confirm-modal>
</div>
