<div x-data="{ 
    activeTab: 'general',
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false,
    salesToggle: true,
    purchaseToggle: false,
    showCancelModal: false,
    showPriceModal: @entangle('showPriceModal'),
}">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.products.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
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
                            {{ $editing && $product ? $product->name : 'New Product' }}
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
                                        <span>Inventory</span>
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
                                    @click="activeTab = 'inventory'"
                                    class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                    :class="activeTab === 'inventory' 
                                        ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                        : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                                >
                                    Inventory
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

                        {{-- Tab: Inventory --}}
                        <div x-show="activeTab === 'inventory'" x-cloak>
                            <div class="mb-6 grid gap-x-8 gap-y-6 lg:grid-cols-2">
                                {{-- Logistics Section --}}
                                <div>
                                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Logistics</h3>
                                    <div class="space-y-2">
                                        {{-- Responsible --}}
                                        <div class="flex items-center gap-3">
                                            <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Responsible</label>
                                            <div class="flex-1 relative">
                                                <select
                                                    wire:model="responsible_id"
                                                    class="w-full appearance-none rounded-lg border border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                                >
                                                    <option value="">Select user...</option>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                    @endforeach
                                                </select>
                                                <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                            </div>
                                        </div>

                                        {{-- Weight --}}
                                        <div class="flex items-center gap-3">
                                            <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Weight</label>
                                            <div class="flex-1 flex items-center gap-2">
                                                <input 
                                                    type="number"
                                                    step="0.001"
                                                    wire:model="weight"
                                                    placeholder="0.000"
                                                    class="w-32 rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none sm:w-40 dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                                />
                                                <span class="shrink-0 text-xs font-medium text-zinc-500 dark:text-zinc-400">kg</span>
                                            </div>
                                        </div>

                                        {{-- Volume --}}
                                        <div class="flex items-center gap-3">
                                            <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Volume</label>
                                            <div class="flex-1 flex items-center gap-2">
                                                <input 
                                                    type="number"
                                                    step="0.001"
                                                    wire:model="volume"
                                                    placeholder="0.000"
                                                    class="w-32 rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none sm:w-40 dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                                />
                                                <span class="shrink-0 text-xs font-medium text-zinc-500 dark:text-zinc-400">m³</span>
                                            </div>
                                        </div>

                                        {{-- Customer Lead Time --}}
                                        <div class="flex items-center gap-3">
                                            <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Lead Time</label>
                                            <div class="flex-1 flex items-center gap-2">
                                                <input 
                                                    type="number"
                                                    min="0"
                                                    wire:model="customer_lead_time"
                                                    placeholder="0"
                                                    class="w-24 rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none sm:w-32 dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                                />
                                                <span class="shrink-0 text-xs font-medium text-zinc-500 dark:text-zinc-400">days</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Operations Section --}}
                                <div>
                                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Operations</h3>
                                    <div class="space-y-2">
                                        {{-- Warehouse --}}
                                        <div class="flex items-center gap-3">
                                            <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Warehouse</label>
                                            <div class="flex-1 relative">
                                                <select
                                                    wire:model="warehouse_id"
                                                    class="w-full appearance-none rounded-lg border border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                                >
                                                    <option value="">Select warehouse...</option>
                                                    @foreach($warehouses as $warehouse)
                                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                    @endforeach
                                                </select>
                                                <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                            </div>
                                        </div>

                                        {{-- Quantity --}}
                                        <div class="flex items-center gap-3">
                                            <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">On Hand</label>
                                            <div class="flex-1 flex items-center gap-2">
                                                <input 
                                                    type="number"
                                                    min="0"
                                                    wire:model="quantity"
                                                    placeholder="0"
                                                    class="w-24 rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none sm:w-32 dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                                />
                                                <span class="shrink-0 text-xs font-medium text-zinc-500 dark:text-zinc-400">units</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Description for Receipts --}}
                            <div class="mb-6 border-t border-zinc-200 pt-6 dark:border-zinc-800">
                                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Description for Receipts</h3>
                                <textarea 
                                    wire:model="receipt_note"
                                    rows="3"
                                    placeholder="This note will appear on receipt orders..."
                                    class="w-full resize-none rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                ></textarea>
                            </div>

                            {{-- Description for Delivery Orders --}}
                            <div>
                                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Description for Delivery Orders</h3>
                                <textarea 
                                    wire:model="delivery_note"
                                    rows="3"
                                    placeholder="This note will appear on delivery orders..."
                                    class="w-full resize-none rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                ></textarea>
                            </div>
                        </div>

                        {{-- Tab: Prices --}}
                        <div x-show="activeTab === 'prices'" x-cloak>
                            <div x-data="{ showColumnMenu: false }">
                                @if(! $productId)
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Save the product first to set different prices per pricelist.</p>
                                @else
                                    <div class="-mx-5 -mt-5 -mb-5">
                                        <div class="overflow-visible">
                                            <table class="w-full">
                                                <thead>
                                                    <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                                        <th class="w-10 px-2 py-2.5"></th>
                                                        <th class="w-56 px-3 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pricelist</th>
                                                        <th class="w-28 px-3 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Type</th>
                                                        <th class="px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Value</th>
                                                        <th class="w-24 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Qty</th>
                                                        <th class="px-3 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 whitespace-nowrap dark:text-zinc-400">Validity</th>
                                                        <th class="w-10 pl-2 pr-2 py-2.5 text-right">
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
                                                                    <a 
                                                                        href="{{ route('inventory.products.pricelists.index') }}"
                                                                        wire:navigate
                                                                        class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                                                    >
                                                                        <flux:icon name="cog-6-tooth" class="size-4 text-zinc-400" />
                                                                        <span>Configure Pricelists</span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </th>
                                                    </tr>
                                                </thead>

                                            <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                                                @forelse($pricelistRules as $rule)
                                                    <tr class="group cursor-pointer hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30" wire:click="openPriceModal({{ $rule->id }})">
                                                        <td class="px-2 py-2">
                                                            <div class="flex cursor-grab items-center justify-center text-zinc-300 transition-opacity hover:text-zinc-500 dark:text-zinc-600 dark:hover:text-zinc-400">
                                                                <svg class="size-4" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                                                                </svg>
                                                            </div>
                                                        </td>

                                                        <td class="w-56 px-3 py-2 overflow-visible">
                                                            <div>
                                                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $rule->pricelist?->name ?? '-' }}</p>
                                                                <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $rule->pricelist?->code ?? '' }}</p>
                                                            </div>
                                                        </td>

                                                        <td class="px-3 py-2">
                                                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $rule->price_type === 'discount' ? 'Discount' : 'Fixed' }}</span>
                                                        </td>

                                                        <td class="px-3 py-2 text-right">
                                                            @if($rule->price_type === 'discount')
                                                                <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ number_format((float) $rule->discount_percentage, 2) }}%</span>
                                                            @else
                                                                <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ Number::currency($rule->fixed_price ?? 0) }}</span>
                                                            @endif
                                                        </td>

                                                        <td class="px-3 py-2 text-right">
                                                            <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $rule->min_quantity }}</span>
                                                        </td>

                                                        <td class="px-3 py-2">
                                                            <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                                                {{ $rule->date_start?->format('Y-m-d') ?? '-' }} – {{ $rule->date_end?->format('Y-m-d') ?? '-' }}
                                                            </span>
                                                        </td>

                                                        <td class="pl-2 pr-2 py-2 text-right">
                                                            <div class="flex items-center justify-end">
                                                                <button
                                                                    type="button"
                                                                    wire:click.stop="deletePriceRule({{ $rule->id }})"
                                                                    wire:confirm="Delete this pricelist price?"
                                                                    class="rounded p-1 text-zinc-300 opacity-0 transition-all hover:text-red-500 group-hover:opacity-100 dark:text-zinc-600 dark:hover:text-red-400"
                                                                    title="Delete"
                                                                >
                                                                    <flux:icon name="trash" class="size-4" />
                                                                </button>
                                                            </div>
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

                                        <div class="border-t border-zinc-100 px-4 py-3 dark:border-zinc-800">
                                            <div class="flex items-center justify-between">
                                                <button
                                                    type="button"
                                                    wire:click="openPriceModal"
                                                    class="inline-flex items-center gap-1.5 text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                                                >
                                                    <flux:icon name="plus" class="size-4" />
                                                    Add a line
                                                </button>
                                                @error('rule_pricelist_id') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
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
                @if($editing && $product)
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
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name ?? 'User' }}</span>
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ optional($product->created_at)->format('H:i') ?? now()->format('H:i') }}</span>
                                </div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">Product created</p>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Empty State for New Product --}}
                    <div class="py-8 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon name="chat-bubble-left-right" class="size-6 text-zinc-400" />
                        </div>
                        <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No activity yet</p>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500">Activity will appear here once the product is saved</p>
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
                href="{{ route('inventory.products.index') }}"
                wire:navigate
                @click="showCancelModal = false"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600"
            >
                Discard & leave
            </a>
        </x-slot:actions>
    </x-ui.confirm-modal>

    <div
        x-show="showPriceModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showPriceModal = false"></div>

        <div
            class="relative z-10 w-full max-w-2xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-zinc-900"
            @click.outside="showPriceModal = false"
        >
            <div class="border-b border-zinc-100 px-6 py-4 dark:border-zinc-800">
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $editingRuleId ? 'Edit Pricelist Price' : 'Add Pricelist Price' }}
                </h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Set a different price for a specific pricelist.</p>
            </div>

            <div class="px-6 py-5">
                <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                    <div class="col-span-2">
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Pricelist</label>
                        <div class="relative">
                            <select
                                wire:model="rule_pricelist_id"
                                class="w-full appearance-none rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-8 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                <option value="">Select pricelist...</option>
                                @foreach($pricelists as $pl)
                                    <option value="{{ $pl->id }}">{{ $pl->name }} ({{ $pl->code }})</option>
                                @endforeach
                            </select>
                            <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                        </div>
                        @error('rule_pricelist_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Price Type</label>
                        <select
                            wire:model.live="rule_price_type"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                            <option value="fixed">Fixed</option>
                            <option value="discount">Discount</option>
                        </select>
                        @error('rule_price_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Minimum Quantity</label>
                        <input
                            type="number"
                            min="1"
                            wire:model="rule_min_quantity"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('rule_min_quantity') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="$wire.rule_price_type === 'fixed'" x-cloak>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Fixed Price</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            wire:model="rule_fixed_price"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('rule_fixed_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="$wire.rule_price_type === 'discount'" x-cloak>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Discount Percentage</label>
                        <div class="flex items-center gap-2">
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                wire:model="rule_discount_percentage"
                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                            <span class="shrink-0 text-xs font-medium text-zinc-500 dark:text-zinc-400">%</span>
                        </div>
                        @error('rule_discount_percentage') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Start Date</label>
                        <input
                            type="date"
                            wire:model="rule_date_start"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('rule_date_start') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">End Date</label>
                        <input
                            type="date"
                            wire:model="rule_date_end"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('rule_date_end') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/50">
                <button
                    type="button"
                    wire:click="$set('showPriceModal', false)"
                    class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    wire:click="savePriceRule"
                    class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Save
                </button>
            </div>
        </div>
    </div>
</div>
