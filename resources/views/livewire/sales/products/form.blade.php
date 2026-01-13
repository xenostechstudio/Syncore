<div x-data="{ 
    activeTab: 'general',
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false,
    salesToggle: true,
    purchaseToggle: false,
    showCancelModal: false,
}">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.products.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    {{-- Small module label --}}
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        Product
                    </span>

                    {{-- Product name + gear dropdown inline --}}
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $editing && $item ? $item->name : 'New Product' }}
                        </span>

                        {{-- Header actions dropdown (Duplicate, Archive, Delete) --}}
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

    {{-- Action Bar --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
            {{-- Left: Actions --}}
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button 
                        type="button"
                        wire:click="save"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        <flux:icon name="document-check" class="size-4" />
                        Save
                    </button>

                    <button 
                        type="button"
                        @click="showCancelModal = true"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        <flux:icon name="x-mark" class="size-4" />
                        Cancel
                    </button>
                </div>
            </div>

            {{-- Right: Chatter Icons --}}
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
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Main Form --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="p-5">
                        {{-- Header with label, favorite, name field and image placeholder --}}
                        <div class="mb-4 flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Product</p>

                                <div class="mt-2 flex items-center gap-2">
                                    {{-- Favorite toggle --}}
                                    <button 
                                        type="button"
                                        wire:click="$toggle('is_favorite')"
                                        class="inline-flex items-center justify-center rounded-full p-1.5"
                                    >
                                        <flux:icon 
                                            name="star" 
                                            class="size-5 {{ $is_favorite ? 'text-amber-400' : 'text-zinc-300 hover:text-amber-300' }}"
                                        />
                                    </button>

                                    {{-- Product name inline input --}}
                                    <input 
                                        type="text"
                                        wire:model="name"
                                        placeholder="Product name..."
                                        class="flex-1 rounded-md border border-transparent bg-transparent px-2 py-1 text-lg text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                    />
                                </div>
                                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                                {{-- Sales / Purchase toggles --}}
                                <div class="mt-2 flex items-center gap-2 text-xs">
                                    <button 
                                        type="button"
                                        @click="salesToggle = !salesToggle"
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1"
                                        :class="salesToggle
                                            ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                                            : 'border border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-500'"
                                    >
                                        <span>Sales</span>
                                    </button>
                                    <button 
                                        type="button"
                                        @click="purchaseToggle = !purchaseToggle"
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1"
                                        :class="purchaseToggle
                                            ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                                            : 'border border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-500'"
                                    >
                                        <span>Purchase</span>
                                    </button>
                                </div>
                            </div>
                            <div class="flex h-20 w-20 items-center justify-center rounded-lg border border-dashed border-zinc-300 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                                <flux:icon name="photo" class="size-8 text-zinc-300 dark:text-zinc-600" />
                            </div>
                        </div>

                        {{-- Tabs --}}
                        <div class="mb-4 border-b border-zinc-200 dark:border-zinc-800">
                            <nav class="-mb-px flex space-x-4 text-sm">
                                <button 
                                    type="button"
                                    @click="activeTab = 'general'"
                                    class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                    :class="activeTab === 'general' 
                                        ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                        : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                                >
                                    General Information
                                </button>
                                <button 
                                    type="button"
                                    @click="activeTab = 'sales'"
                                    class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                    :class="activeTab === 'sales' 
                                        ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                        : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                                >
                                    Sales
                                </button>
                                <button 
                                    type="button"
                                    @click="activeTab = 'prices'"
                                    class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                    :class="activeTab === 'prices' 
                                        ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                        : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                                >
                                    Prices
                                </button>
                            </nav>
                        </div>

                        {{-- Tab: General Information --}}
                        <div x-show="activeTab === 'general'">
                            <div class="grid grid-cols-2 gap-x-8">
                                {{-- Left column: Barcode, Product Type & Invoicing Policy --}}
                                <div class="space-y-4">
                                    {{-- Barcode --}}
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Barcode</label>
                                        <div class="flex-1">
                                            <input 
                                                type="text"
                                                wire:model="barcode"
                                                placeholder="Scan or type barcode"
                                                class="w-full rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                            />
                                        </div>
                                    </div>
                                    @error('barcode') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                                    {{-- Product Type (pill toggles like Sales/Purchase, single choice) --}}
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Product Type</label>
                                        <div class="flex-1 flex items-center gap-2 text-xs">
                                            <button 
                                                type="button"
                                                wire:click="$set('product_type', 'goods')"
                                                class="inline-flex items-center gap-1 rounded-full px-3 py-1 {{ $product_type === 'goods'
                                                    ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                                                    : 'border border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-500' }}"
                                            >
                                                <span>Goods</span>
                                            </button>
                                            <button 
                                                type="button"
                                                wire:click="$set('product_type', 'service')"
                                                class="inline-flex items-center gap-1 rounded-full px-3 py-1 {{ $product_type === 'service'
                                                    ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                                                    : 'border border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-500' }}"
                                            >
                                                <span>Service</span>
                                            </button>
                                        </div>
                                    </div>
                                    @error('product_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                                    {{-- Invoicing Policy (select column, border only on hover/edit) --}}
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Invoicing Policy</label>
                                        <div class="flex-1 relative">
                                            <select
                                                wire:model="invoicing_policy"
                                                class="w-full appearance-none rounded-lg border border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                            >
                                                <option value="ordered">Ordered</option>
                                                <option value="delivered">Delivered</option>
                                            </select>
                                            <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                        </div>
                                    </div>
                                    @error('invoicing_policy') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Right column: Pricing & Classification --}}
                                <div class="space-y-2">
                                    {{-- Sales Price --}}
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Sales Price</label>
                                        <div class="flex-1 flex items-center gap-2">
                                            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Rp</span>
                                            <input 
                                                type="text"
                                                wire:model="selling_price"
                                                placeholder="0"
                                                class="w-full rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                            />
                                        </div>
                                    </div>
                                    @error('selling_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                                    {{-- Sales Taxes (select column, border only on hover/edit) --}}
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Sales Taxes</label>
                                        <div class="flex-1 relative">
                                            <select
                                                wire:model="sales_tax_id"
                                                class="w-full appearance-none rounded-lg border border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                            >
                                                <option value="">No tax</option>
                                                @foreach($taxes as $tax)
                                                    <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->formatted_rate }})</option>
                                                @endforeach
                                            </select>
                                            <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                        </div>
                                    </div>

                                    {{-- Cost --}}
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Cost</label>
                                        <div class="flex-1 flex items-center gap-2">
                                            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Rp</span>
                                            <input 
                                                type="text"
                                                wire:model="cost_price"
                                                placeholder="0"
                                                class="w-full rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                            />
                                        </div>
                                    </div>
                                    @error('cost_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                                    {{-- Category --}}
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Category</label>
                                        <div class="flex-1">
                                            <input 
                                                type="text"
                                                wire:model="category"
                                                placeholder="e.g., All / Sales / Accessories"
                                                class="w-full rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                            />
                                        </div>
                                    </div>
                                    @error('category') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                                    {{-- Reference --}}
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Reference</label>
                                        <div class="flex-1">
                                            <input 
                                                type="text"
                                                wire:model="reference"
                                                placeholder="Internal reference"
                                                class="w-full rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                            />
                                        </div>
                                    </div>
                                    @error('reference') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    @error('barcode') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Divider before Internal Notes --}}
                            <div class="mt-6 border-t border-zinc-200 dark:border-zinc-800"></div>

                            {{-- Internal Notes --}}
                            <div class="mt-4">
                                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Internal Notes</label>
                                <textarea 
                                    wire:model="internal_notes"
                                    rows="2"
                                    placeholder="Internal notes..."
                                    class="w-full resize-none border-none bg-transparent px-0 py-1 text-sm text-zinc-900 placeholder-zinc-400 focus:ring-0 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500"
                                ></textarea>
                                @error('internal_notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Tab: Sales --}}
                        <div x-show="activeTab === 'sales'" x-cloak>
                            <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                                {{-- Quantity --}}
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Quantity <span class="text-red-500">*</span></label>
                                    <input 
                                        type="number"
                                        wire:model="quantity"
                                        min="0"
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    />
                                    @error('quantity') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Warehouse --}}
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Warehouse</label>
                                    <flux:select wire:model="warehouse_id">
                                        <flux:select.option value="">Select warehouse...</flux:select.option>
                                        @foreach($warehouses as $warehouse)
                                            <flux:select.option :value="$warehouse->id">{{ $warehouse->name }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </div>
                            </div>
                        </div>

                        {{-- Tab: Prices --}}
                        <div x-show="activeTab === 'prices'" x-cloak>
                            <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                                {{-- Cost Price --}}
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Cost Price</label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Rp</span>
                                        <input 
                                            type="text"
                                            wire:model="cost_price"
                                            placeholder="0"
                                            class="w-full rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                        />
                                    </div>
                                    @error('cost_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Selling Price --}}
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Selling Price</label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Rp</span>
                                        <input 
                                            type="text"
                                            wire:model="selling_price"
                                            placeholder="0"
                                            class="w-full rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                        />
                                    </div>
                                    @error('selling_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            @if($cost_price && $selling_price && $selling_price > $cost_price)
                                <div class="mt-6 rounded-lg bg-emerald-50 p-4 dark:bg-emerald-900/20">
                                    <div class="flex items-center gap-3">
                                        <flux:icon.chart-bar class="size-5 text-emerald-600" />
                                        <div>
                                            <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">
                                                Profit Margin: {{ number_format((($selling_price - $cost_price) / $cost_price) * 100, 1) }}%
                                            </p>
                                            <p class="text-xs text-emerald-600 dark:text-emerald-400">
                                                Profit per unit: {{ Number::currency($selling_price - $cost_price) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Activity Timeline (No Card) --}}
            <div class="lg:col-span-3">
                {{-- Chatter Forms --}}
                <x-ui.chatter-forms />

                {{-- Activity Timeline --}}
                @if($editing && $item)
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
                                    <x-ui.activity-item :activity="$item['data']" emptyMessage="Product created" />
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
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Product created</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    {{-- Empty State for New Product --}}
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

    {{-- Cancel Confirmation Modal (reusable component) --}}
    <x-ui.confirm-modal show="showCancelModal">
        <x-slot:icon>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                <flux:icon name="exclamation-triangle" class="size-6" />
            </div>
        </x-slot:icon>

        <x-slot:title>
            Discard changes?
        </x-slot:title>

        <x-slot:description>
            If you leave this page, any unsaved changes to this product will be lost.
        </x-slot:description>

        <x-slot:actions>
            <button 
                type="button"
                @click="showCancelModal = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                Keep editing
            </button>

            <a 
                href="{{ route('sales.products.index') }}"
                wire:navigate
                @click="showCancelModal = false"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600"
            >
                Discard & leave
            </a>
        </x-slot:actions>
    </x-ui.confirm-modal>
</div>
