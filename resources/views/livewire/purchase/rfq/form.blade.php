<div x-data="{ activeTab: 'products', showSendMessage: false, showLogNote: false, showScheduleActivity: false, showCancelModal: false }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            {{-- Left Group: Back Button, Title, Gear Dropdown --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('purchase.rfq.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    {{-- Small module label --}}
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        Request for Quotation
                    </span>

                    {{-- RFQ number + gear dropdown inline --}}
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $rfqId ? $reference : 'New RFQ' }}
                        </span>

                        {{-- Header actions dropdown (Duplicate, Archive, Delete) --}}
                        <flux:dropdown position="bottom" align="start">
                            <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                <flux:icon name="cog-6-tooth" class="size-4" />
                            </button>

                            <flux:menu class="w-40">
                                @if($rfqId)
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
                                <button type="button" wire:click="delete" wire:confirm="Are you sure you want to delete this RFQ?" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
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

    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    @if(!$rfqId)
                        {{-- New RFQ: Show Save button (primary) --}}
                        <button 
                            type="button"
                            wire:click="save"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
                            <flux:icon name="document-check" class="size-4" />
                            Save
                        </button>
                    @elseif($status === 'rfq')
                        {{-- RFQ Status: Show Send RFQ button (primary) --}}
                        <button 
                            type="button"
                            wire:click="sendRfq"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
                            <flux:icon name="paper-airplane" class="size-4" />
                            Send RFQ
                        </button>
                        <button 
                            type="button"
                            wire:click="save"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        >
                            <flux:icon name="document-check" class="size-4" />
                            Save
                        </button>
                    @elseif($status === 'sent')
                        {{-- Sent Status: Show Confirm Order button (primary) --}}
                        <button 
                            type="button"
                            wire:click="confirmOrder"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
                            <flux:icon name="check" class="size-4" />
                            Confirm Order
                        </button>
                        <button 
                            type="button"
                            wire:click="save"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        >
                            <flux:icon name="document-check" class="size-4" />
                            Save
                        </button>
                    @endif
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
                    @if($rfqId && $status !== 'cancelled')
                        <button 
                            type="button"
                            @click="showCancelModal = true"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20"
                        >
                            <flux:icon name="x-mark" class="size-4" />
                            Cancel RFQ
                        </button>
                    @endif
                </div>

                {{-- Stepper (Right side of col-span-9, same line as buttons) --}}
                @php
                    $steps = [
                        ['key' => 'rfq', 'label' => 'RFQ'],
                        ['key' => 'sent', 'label' => 'RFQ Sent'],
                        ['key' => 'purchase_order', 'label' => 'Purchase Order'],
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

            <div class="col-span-3 flex items-center justify-end gap-1">
                <x-ui.chatter-buttons :showMessage="false" />
            </div>
        </div>
    </div>

    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="p-5">
                        <h1 class="mb-5 text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $rfqId ? $reference : 'New' }}
                        </h1>

                        <div class="grid gap-6 sm:grid-cols-2">
                            {{-- Left Column: Supplier & Supplier Reference --}}
                            <div class="space-y-3">
                                <div>
                                    <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Supplier <span class="text-red-500">*</span></label>
                                    <div class="relative" x-data="{ open: false, search: '' }">
                                        <button 
                                            type="button"
                                            @click="open = !open; $nextTick(() => { if(open) $refs.supplierSearch.focus() })"
                                            class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-left text-sm transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                        >
                                            @if($selectedSupplier)
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                        {{ strtoupper(substr($selectedSupplier->name ?? ($selectedSupplier['name'] ?? 'S'), 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $selectedSupplier->name ?? $selectedSupplier['name'] ?? '' }}</p>
                                                        @if($selectedSupplier->email ?? $selectedSupplier['email'] ?? null)
                                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $selectedSupplier->email ?? $selectedSupplier['email'] }}</p>
                                                        @elseif($selectedSupplier->contact_person ?? $selectedSupplier['contact_person'] ?? null)
                                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $selectedSupplier->contact_person ?? $selectedSupplier['contact_person'] }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-zinc-400">Select a supplier...</span>
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
                                                    x-ref="supplierSearch"
                                                    x-model="search"
                                                    placeholder="Search suppliers..."
                                                    class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                                    @keydown.escape="open = false; search = ''"
                                                />
                                            </div>
                                            <div class="max-h-60 overflow-auto py-1">
                                                @foreach($suppliers as $supplier)
                                                    <button 
                                                        type="button"
                                                        x-show="'{{ strtolower($supplier->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($supplier->email ?? '') }}'.includes(search.toLowerCase()) || '{{ strtolower($supplier->contact_person ?? '') }}'.includes(search.toLowerCase()) || search === ''"
                                                        wire:click="selectSupplier({{ $supplier->id }})"
                                                        @click="open = false; search = ''"
                                                        class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $supplier_id === $supplier->id ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}"
                                                    >
                                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                            {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                                        </div>
                                                        <div>
                                                            <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $supplier->name }}</p>
                                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                                {{ $supplier->contact_person ?? $supplier->email ?? '—' }}
                                                            </p>
                                                        </div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @error('supplier_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Supplier Reference (inline field below supplier) --}}
                                <div>
                                    <input 
                                        type="text"
                                        wire:model="supplier_reference"
                                        placeholder="Supplier reference..."
                                        class="w-full border-0 border-b border-transparent bg-transparent px-0 py-1.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:hover:border-zinc-700"
                                    />
                                </div>
                            </div>

                            {{-- Right Column: Order Deadline, Expected Arrival, Deliver To --}}
                            <div class="space-y-3">
                                {{-- Order Deadline --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-32 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Order Deadline</label>
                                    <div class="relative flex-1">
                                        <input 
                                            type="date" 
                                            wire:model="order_date"
                                            class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                        />
                                    </div>
                                </div>

                                {{-- Expected Arrival --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-32 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Expected Arrival</label>
                                    <div class="relative flex-1">
                                        <input 
                                            type="date" 
                                            wire:model="expected_arrival"
                                            class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                        />
                                    </div>
                                </div>

                                {{-- Deliver To --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-32 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Deliver To</label>
                                    <div class="relative flex-1">
                                        <input 
                                            type="text" 
                                            wire:model="deliver_to"
                                            placeholder="Delivery address..."
                                            class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Tab Headers: Products & Other Info --}}
                    <div class="flex items-center border-y border-zinc-100 dark:border-zinc-800">
                        <button 
                            type="button"
                            @click="activeTab = 'products'"
                            :class="activeTab === 'products' ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                            class="px-5 py-3 text-sm font-medium transition-colors"
                        >
                            Products
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
                    
                    {{-- Tab Content: Products --}}
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
                                discount: { label: 'Discount (%)', visible: false, required: false },
                                subtotal: { label: 'Subtotal', visible: true, required: true },
                            },
                            isColumnVisible(key) {
                                return this.columns[key] && this.columns[key].visible;
                            },
                        }"
                    >
                        {{-- Products Table --}}
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
                                                $wire.reorderLines(this.dragging, this.dragOver);
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
                                    @forelse($lines as $index => $line)
                                        <tr 
                                            class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30" 
                                            wire:key="line-{{ $index }}"
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

                                            {{-- Product Selection --}}
                                            <td x-show="isColumnVisible('product')" class="w-[32rem] px-3 py-2 overflow-visible">
                                                <div x-data="{ open: false, search: '' }" class="relative">
                                                    @if($line['product_id'])
                                                        <button 
                                                            type="button"
                                                            @click="open = true; $nextTick(() => $refs.productSearch{{ $index }}.focus())"
                                                            class="flex w-full items-center gap-2 text-left"
                                                            @disabled($status === 'purchase_order' || $status === 'cancelled')
                                                        >
                                                            <div>
                                                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $line['product_name'] }}</p>
                                                                <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $line['product_sku'] }}</p>
                                                            </div>
                                                        </button>
                                                    @else
                                                        <button 
                                                            type="button"
                                                            @click="open = true; $nextTick(() => $refs.productSearch{{ $index }}.focus())"
                                                            class="text-sm text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                                                            @disabled($status === 'purchase_order' || $status === 'cancelled')
                                                        >
                                                            Select a product...
                                                        </button>
                                                    @endif

                                                    @if($status !== 'purchase_order' && $status !== 'cancelled')
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
                                                            @foreach($products as $catalogProduct)
                                                                <button 
                                                                    type="button"
                                                                    x-show="'{{ strtolower($catalogProduct->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($catalogProduct->sku ?? '') }}'.includes(search.toLowerCase()) || search === ''"
                                                                    wire:click="selectProduct({{ $index }}, {{ $catalogProduct->id }})"
                                                                    @click="open = false; search = ''"
                                                                    class="flex w-full items-center gap-3 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                                                >
                                                                    <div class="flex-1">
                                                                        <p class="text-zinc-900 dark:text-zinc-100">{{ $catalogProduct->name }}</p>
                                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $catalogProduct->sku ?? 'No SKU' }} · Rp {{ number_format($catalogProduct->cost_price ?? 0, 0, ',', '.') }}</p>
                                                                    </div>
                                                                    <span class="text-xs text-zinc-400">{{ $catalogProduct->quantity ?? 0 }} in stock</span>
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
                                                    wire:model.live="lines.{{ $index }}.description"
                                                    placeholder="Add description..."
                                                    class="w-full bg-transparent text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none dark:text-zinc-100"
                                                    @disabled($status === 'purchase_order' || $status === 'cancelled')
                                                />
                                            </td>

                                            {{-- Discount --}}
                                            <td x-show="isColumnVisible('discount')" class="px-3 py-2">
                                                <input 
                                                    type="text"
                                                    wire:model.live="lines.{{ $index }}.discount"
                                                    class="w-full bg-transparent text-right text-sm text-zinc-900 focus:outline-none dark:text-zinc-100"
                                                    @disabled($status === 'purchase_order' || $status === 'cancelled')
                                                />
                                            </td>

                                            {{-- Quantity --}}
                                            <td x-show="isColumnVisible('qty')" class="w-16 px-3 py-2">
                                                <input 
                                                    type="text"
                                                    wire:model.live="lines.{{ $index }}.quantity"
                                                    class="w-full bg-transparent text-right text-sm text-zinc-900 focus:outline-none dark:text-zinc-100"
                                                    @disabled($status === 'purchase_order' || $status === 'cancelled')
                                                />
                                            </td>

                                            {{-- Unit Price --}}
                                            <td x-show="isColumnVisible('unit_price')" class="w-32 px-3 py-2">
                                                <input 
                                                    type="text"
                                                    wire:model.live="lines.{{ $index }}.unit_price"
                                                    class="w-full bg-transparent text-right text-sm text-zinc-900 focus:outline-none dark:text-zinc-100"
                                                    @disabled($status === 'purchase_order' || $status === 'cancelled')
                                                />
                                            </td>

                                            {{-- Subtotal --}}
                                            <td x-show="isColumnVisible('subtotal')" class="px-3 py-2 text-right">
                                                <span class="text-sm text-zinc-900 dark:text-zinc-100">Rp {{ number_format($line['total'], 0, ',', '.') }}</span>
                                            </td>

                                            {{-- Remove --}}
                                            <td class="pl-2 pr-2 py-2 text-right">
                                                @if(count($lines) > 1 && $status !== 'purchase_order' && $status !== 'cancelled')
                                                    <button 
                                                        type="button"
                                                        wire:click="removeLine({{ $index }})"
                                                        class="rounded p-1 text-zinc-300 opacity-0 transition-all hover:text-red-500 group-hover:opacity-100 dark:text-zinc-600 dark:hover:text-red-400"
                                                    >
                                                        <flux:icon name="trash" class="size-4" />
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="px-4 py-8 text-center text-sm text-zinc-400">
                                                No items added yet
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Add Line Button --}}
                        <div class="border-t border-zinc-100 px-4 py-3 dark:border-zinc-800">
                            <div class="flex items-center justify-between">
                                @if($status !== 'purchase_order' && $status !== 'cancelled')
                                    <button 
                                        type="button"
                                        wire:click="addLine"
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
                                @error('lines') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Terms & Totals Row --}}
                        <div class="border-t border-zinc-100 bg-zinc-50/50 p-5 dark:border-zinc-800 dark:bg-zinc-900/30">
                            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                                {{-- Terms & Conditions (Left Side) --}}
                                <div class="flex-1">
                                    <textarea 
                                        wire:model="notes"
                                        rows="3"
                                        placeholder="Terms & Conditions"
                                        class="w-full resize-none border-0 bg-transparent px-0 py-0 text-sm text-zinc-700 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-300 dark:placeholder-zinc-500"
                                    ></textarea>
                                </div>

                                {{-- Totals (Right Side) --}}
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
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Content: Other Info --}}
                    <div x-show="activeTab === 'other'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="p-6">
                            <div class="grid gap-8 lg:grid-cols-2">
                                {{-- Purchase Section --}}
                                <div class="space-y-4">
                                    <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Purchase</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Confirmation Date</label>
                                            <input type="date" wire:model="confirmation_date" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Receipt Date</label>
                                            <input type="date" wire:model="receipt_date" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                        </div>
                                    </div>
                                </div>

                                {{-- Notes Section --}}
                                <div class="space-y-4">
                                    <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Notes</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Internal Notes</label>
                                            <textarea wire:model="internal_notes" rows="4" placeholder="Add internal notes..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                {{-- Chatter Forms --}}
                <x-ui.chatter-forms />

                {{-- Activity Timeline --}}
                @if($rfqId)
                    <div class="flex items-center gap-3 py-2">
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Activity</span>
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    </div>

                    <div class="space-y-3">
                        @if(isset($activities) && $activities->isNotEmpty())
                            @foreach($activities as $item)
                                @if($item['type'] === 'note')
                                    {{-- Note Item - Compact --}}
                                    <x-ui.note-item :note="$item['data']" />
                                @else
                                    {{-- Activity Log Item --}}
                                    <x-ui.activity-item :activity="$item['data']" emptyMessage="RFQ created" />
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
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">RFQ created</p>
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
                        <p class="text-xs text-zinc-400 dark:text-zinc-500">Activity will appear here once you save</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-ui.confirm-modal show="showCancelModal">
        <x-slot:icon>
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                <flux:icon name="exclamation-triangle" class="size-7" />
            </div>
        </x-slot:icon>

        <x-slot:title>
            Cancel this RFQ?
        </x-slot:title>

        <x-slot:description>
            This action will cancel the RFQ and cannot be undone. Are you sure you want to proceed?
        </x-slot:description>

        <x-slot:actions>
            <button 
                type="button"
                @click="showCancelModal = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                Keep RFQ
            </button>

            <button 
                type="button"
                wire:click="cancel"
                @click="showCancelModal = false"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600"
            >
                Cancel RFQ
            </button>
        </x-slot:actions>
    </x-ui.confirm-modal>
</div>
