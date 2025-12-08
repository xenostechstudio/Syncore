<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('inventory.items.index') }}" wire:navigate class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-normal text-zinc-900 dark:text-zinc-100">
                    {{ $editing ? 'Edit Item' : 'New Item' }}
                </h1>
                <p class="text-sm font-light text-zinc-500 dark:text-zinc-400">
                    {{ $editing ? 'Update inventory item details' : 'Create a new inventory item' }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <flux:button variant="ghost" href="{{ route('inventory.items.index') }}" wire:navigate>
                Cancel
            </flux:button>
            <flux:button wire:click="save" variant="primary">
                {{ $editing ? 'Update Item' : 'Create Item' }}
            </flux:button>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Left Column: Main Form --}}
        <div class="space-y-6 lg:col-span-8">
            {{-- Basic Information --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Basic Information</h2>
                </div>
                <div class="p-5">
                    <div class="grid gap-6 sm:grid-cols-2">
                        {{-- Name --}}
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Item Name <span class="text-red-500">*</span></label>
                            <flux:input 
                                wire:model="name" 
                                placeholder="e.g., MacBook Pro 16&quot;"
                                wire:blur="generateSku"
                            />
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- SKU --}}
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">SKU <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <flux:input 
                                    wire:model="sku" 
                                    placeholder="e.g., MBP-16-2024"
                                    class="flex-1"
                                />
                                <flux:button type="button" variant="ghost" wire:click="generateSku" icon="sparkles">
                                    Generate
                                </flux:button>
                            </div>
                            @error('sku') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Description --}}
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Description</label>
                            <flux:textarea 
                                wire:model="description" 
                                rows="3"
                                placeholder="Enter a detailed description of the item..."
                            />
                            @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Pricing</h2>
                </div>
                <div class="p-5">
                    <div class="grid gap-6 sm:grid-cols-2">
                        {{-- Cost Price --}}
                        <x-ui.currency-input
                            wire:model="cost_price"
                            label="Cost Price"
                            :error="$errors->first('cost_price')"
                        />

                        {{-- Selling Price --}}
                        <x-ui.currency-input
                            wire:model="selling_price"
                            label="Selling Price"
                            :error="$errors->first('selling_price')"
                        />
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

        {{-- Right Column: Status & Settings --}}
        <div class="space-y-6 lg:col-span-4">
            {{-- Inventory Status --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Inventory Status</h2>
                </div>
                <div class="p-5 space-y-6">
                    {{-- Quantity --}}
                    <div>
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Quantity <span class="text-red-500">*</span></label>
                        <flux:input 
                            wire:model="quantity" 
                            type="number"
                            min="0"
                            placeholder="0"
                        />
                        @error('quantity') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Status</label>
                        <flux:select wire:model="status">
                            <flux:select.option value="in_stock">In Stock</flux:select.option>
                            <flux:select.option value="low_stock">Low Stock</flux:select.option>
                            <flux:select.option value="out_of_stock">Out of Stock</flux:select.option>
                        </flux:select>
                        @error('status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Warehouse --}}
                    <div>
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Warehouse</label>
                        <flux:select wire:model="warehouse_id">
                            <flux:select.option value="">Select warehouse...</flux:select.option>
                            @foreach($warehouses as $warehouse)
                                <flux:select.option :value="$warehouse->id">{{ $warehouse->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
