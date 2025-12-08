<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-xl font-normal text-zinc-900 dark:text-zinc-100">Inventory Items</h1>
        <div class="flex items-center gap-2">
            <flux:button variant="primary" icon="plus" href="{{ route('inventory.items.create') }}" wire:navigate>
                New Item
            </flux:button>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {{-- Search & Filters --}}
        <div class="flex flex-1 flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative w-full sm:w-64">
                <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by name or SKU..."
                    class="w-full rounded-lg border border-zinc-200 bg-white py-2 pl-10 pr-4 text-sm font-light text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder-zinc-500 dark:focus:border-zinc-700"
                />
            </div>

            {{-- Status Filter --}}
            <x-ui.filter-dropdown label="Status" :value="$status ? ucfirst(str_replace('_', ' ', $status)) : null">
                <flux:menu.item wire:click="$set('status', '')" icon="{{ empty($status) ? 'check' : '' }}">All Status</flux:menu.item>
                <flux:menu.item wire:click="$set('status', 'in_stock')" icon="{{ $status === 'in_stock' ? 'check' : '' }}">In Stock</flux:menu.item>
                <flux:menu.item wire:click="$set('status', 'low_stock')" icon="{{ $status === 'low_stock' ? 'check' : '' }}">Low Stock</flux:menu.item>
                <flux:menu.item wire:click="$set('status', 'out_of_stock')" icon="{{ $status === 'out_of_stock' ? 'check' : '' }}">Out of Stock</flux:menu.item>
            </x-ui.filter-dropdown>

            {{-- Sort Filter --}}
            <x-ui.filter-dropdown label="Sort" :value="ucfirst(str_replace('_', ' ', $sort))">
                <flux:menu.item wire:click="$set('sort', 'latest')" icon="{{ $sort === 'latest' ? 'check' : '' }}">Latest</flux:menu.item>
                <flux:menu.item wire:click="$set('sort', 'oldest')" icon="{{ $sort === 'oldest' ? 'check' : '' }}">Oldest</flux:menu.item>
                <flux:menu.item wire:click="$set('sort', 'name')" icon="{{ $sort === 'name' ? 'check' : '' }}">Name A-Z</flux:menu.item>
                <flux:menu.separator />
                <flux:menu.item wire:click="$set('sort', 'price_high')" icon="{{ $sort === 'price_high' ? 'check' : '' }}">Price: High to Low</flux:menu.item>
                <flux:menu.item wire:click="$set('sort', 'price_low')" icon="{{ $sort === 'price_low' ? 'check' : '' }}">Price: Low to High</flux:menu.item>
                <flux:menu.separator />
                <flux:menu.item wire:click="$set('sort', 'stock_high')" icon="{{ $sort === 'stock_high' ? 'check' : '' }}">Stock: High to Low</flux:menu.item>
                <flux:menu.item wire:click="$set('sort', 'stock_low')" icon="{{ $sort === 'stock_low' ? 'check' : '' }}">Stock: Low to High</flux:menu.item>
            </x-ui.filter-dropdown>

            {{-- Clear Filters --}}
            @if($search || $status || $sort !== 'latest')
                <button 
                    wire:click="clearFilters"
                    class="text-sm font-light text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                >
                    Clear filters
                </button>
            @endif
        </div>

        {{-- Right: View Toggle & Per Page --}}
        <div class="flex items-center gap-3">
             <div class="hidden sm:block">
                <select 
                    wire:model.live="perPage"
                    class="h-9 rounded-lg border border-zinc-200 bg-white px-3 py-0 text-sm font-light text-zinc-600 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:focus:border-zinc-700"
                >
                    <option value="15">15 rows</option>
                    <option value="25">25 rows</option>
                    <option value="50">50 rows</option>
                    <option value="100">100 rows</option>
                </select>
            </div>
            <x-ui.view-toggle :view="$view" />
        </div>
    </div>

    {{-- Content --}}
    <div class="relative min-h-[500px]">
        {{-- Loading State --}}
        <div wire:loading.delay class="absolute inset-0 z-10 flex items-start justify-center bg-white/50 pt-20 backdrop-blur-sm dark:bg-zinc-950/50">
            <div class="flex items-center gap-2 rounded-full bg-white px-4 py-2 shadow-lg ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-800">
                <svg class="size-4 animate-spin text-zinc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Loading...</span>
            </div>
        </div>

        @if($view === 'list')
            {{-- List View --}}
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Name</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">SKU</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Stock</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Price</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Status</th>
                            <th class="px-6 py-3 text-right font-medium text-zinc-500 dark:text-zinc-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($items as $item)
                            <tr 
                                onclick="window.location='{{ route('inventory.items.edit', $item) }}'"
                                class="group cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                                            <flux:icon name="photo" class="size-5" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item->name }}</p>
                                            @if($item->description)
                                                <p class="line-clamp-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $item->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <code class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $item->sku }}
                                    </code>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-12 rounded-full bg-zinc-100 dark:bg-zinc-800">
                                            <div 
                                                class="h-full rounded-full {{ $item->quantity < 10 ? 'bg-red-500' : ($item->quantity < 30 ? 'bg-amber-500' : 'bg-emerald-500') }}" 
                                                style="width: {{ min(100, max(5, ($item->quantity / 100) * 100)) }}%"
                                            ></div>
                                        </div>
                                        <span class="text-zinc-600 dark:text-zinc-400">{{ number_format($item->quantity) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                        ${{ number_format($item->selling_price ?? 0, 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusConfig = match($item->status) {
                                            'in_stock' => ['bg' => 'bg-emerald-100 dark:bg-emerald-500/10', 'text' => 'text-emerald-700 dark:text-emerald-400', 'dot' => 'bg-emerald-500'],
                                            'low_stock' => ['bg' => 'bg-amber-100 dark:bg-amber-500/10', 'text' => 'text-amber-700 dark:text-amber-400', 'dot' => 'bg-amber-500'],
                                            'out_of_stock' => ['bg' => 'bg-red-100 dark:bg-red-500/10', 'text' => 'text-red-700 dark:text-red-400', 'dot' => 'bg-red-500'],
                                            default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-700 dark:text-zinc-300', 'dot' => 'bg-zinc-500'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                                        {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100" onclick="event.stopPropagation()">
                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="pencil" 
                                            href="{{ route('inventory.items.edit', $item) }}" 
                                            wire:navigate 
                                        />
                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="trash" 
                                            class="text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20" 
                                            wire:click="delete({{ $item->id }})"
                                            wire:confirm="Are you sure you want to delete this item?"
                                        />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                            <flux:icon name="archive-box" class="size-6 text-zinc-400" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">No items found</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Try adjusting your search or filters</p>
                                        </div>
                                        <flux:button variant="secondary" size="sm" wire:click="clearFilters">
                                            Clear Filters
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            {{-- Grid View --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($items as $item)
                    <div 
                        onclick="window.location='{{ route('inventory.items.edit', $item) }}'"
                        class="group relative flex cursor-pointer flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700"
                    >
                        {{-- Image/Icon Area --}}
                        <div class="flex h-40 w-full items-center justify-center bg-zinc-50 dark:bg-zinc-800/50">
                            <flux:icon name="photo" class="size-10 text-zinc-300 dark:text-zinc-600" />
                        </div>
                        
                        {{-- Content --}}
                        <div class="flex flex-1 flex-col p-4">
                            <div class="mb-2 flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item->name }}</h3>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->sku }}</p>
                                </div>
                                @php
                                    $statusColor = match($item->status) {
                                        'in_stock' => 'bg-emerald-500',
                                        'low_stock' => 'bg-amber-500',
                                        'out_of_stock' => 'bg-red-500',
                                        default => 'bg-zinc-500',
                                    };
                                @endphp
                                <span class="h-2 w-2 rounded-full {{ $statusColor }}"></span>
                            </div>
                            
                            <div class="mt-auto flex items-end justify-between border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                <div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Price</p>
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">${{ number_format($item->selling_price ?? 0, 2) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Stock</p>
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ number_format($item->quantity) }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Hover Actions --}}
                        <div class="absolute right-2 top-2 flex gap-1 opacity-0 transition-opacity group-hover:opacity-100" onclick="event.stopPropagation()">
                            <button 
                                wire:click="delete({{ $item->id }})"
                                wire:confirm="Are you sure you want to delete this item?"
                                class="rounded-lg bg-white/90 p-1.5 text-red-500 shadow-sm backdrop-blur hover:text-red-600 dark:bg-zinc-900/90"
                            >
                                <flux:icon name="trash" class="size-4" />
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon name="archive-box" class="size-6 text-zinc-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">No items found</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Try adjusting your search or filters</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- Pagination --}}
        @if($items->hasPages())
            <div class="mt-6">
                {{ $items->links('livewire.pagination') }}
            </div>
        @endif
    </div>
</div>
