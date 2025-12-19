<div>
    {{-- Flash Messages --}}
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
    </div>

    {{-- Header Bar (inside Livewire root div so wire:click works) --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group: New Button, Title, Gear --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.products.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">
                    Products
                </span>

                @if(isset($warehouses))
                    <select wire:model.live="warehouse_id" class="h-9 rounded-lg border border-zinc-200 bg-white px-2.5 text-sm text-zinc-700 shadow-sm focus:border-zinc-400 focus:outline-none focus:ring-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                @endif
                
                {{-- Actions Menu (Gear) --}}
                <flux:dropdown position="bottom" align="start">
                    <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="cog-6-tooth" class="size-5" />
                    </button>

                    <flux:menu class="w-48">
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>Import records</span>
                        </button>
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>Export All</span>
                        </button>
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Center Group: Search or Selection Toolbar --}}
            <div class="flex flex-1 items-center justify-center">
                @if(count($selected) > 0)
                    {{-- Selection Toolbar --}}
                    <div class="flex items-center gap-2 animate-in fade-in slide-in-from-top-2 duration-200">
                        {{-- Count Selected Button --}}
                        <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-zinc-100 px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-200 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-600">
                            <flux:icon name="x-mark" class="size-4" />
                            <span>{{ count($selected) }} Selected</span>
                        </button>

                        {{-- Print --}}
                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <flux:icon name="printer" class="size-4" />
                            <span>Print</span>
                        </button>

                        {{-- Actions Dropdown --}}
                        <flux:dropdown position="bottom" align="center">
                            <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                <flux:icon name="cog-6-tooth" class="size-4" />
                                <span>Actions</span>
                                <flux:icon name="chevron-down" class="size-3" />
                            </button>

                            <flux:menu class="w-56">
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="arrow-up-tray" class="size-4" />
                                    <span>Export</span>
                                </button>
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="document-duplicate" class="size-4" />
                                    <span>Duplicate</span>
                                </button>
                                <flux:menu.separator />
                                <button 
                                    type="button" 
                                    wire:click="deleteSelected"
                                    wire:confirm="Are you sure you want to delete {{ count($selected) }} selected items?"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                >
                                    <flux:icon name="trash" class="size-4" />
                                    <span>Delete</span>
                                </button>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @else
                    {{-- Search Input with Arrow Down Dropdown --}}
                    <flux:dropdown position="bottom" align="center" class="w-[480px]">
                        <div class="relative flex h-9 w-full items-center overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                            <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search"
                                placeholder="Search products..." 
                                class="h-full w-full border-0 bg-transparent pl-9 pr-10 text-sm outline-none focus:ring-0" 
                            />
                            <button type="button" class="absolute right-0 top-0 flex h-full items-center border-l border-zinc-200 px-2.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:border-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-300">
                                <flux:icon name="chevron-down" class="size-4" />
                            </button>
                        </div>

                        {{-- Horizontal Dropdown Content --}}
                        <flux:menu class="w-[480px]">
                                <div class="flex divide-x divide-zinc-200 dark:divide-zinc-700">
                                    {{-- Filters Section --}}
                                    <div class="flex-1 p-3">
                                        <div class="mb-2 flex items-center justify-between">
                                            <div class="flex items-center gap-1.5">
                                                <flux:icon name="funnel" class="size-4 text-zinc-400" />
                                                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Filters</span>
                                            </div>
                                            <button class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">+ Add Custom</button>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <input type="checkbox" wire:model.live="status" value="in_stock" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>In Stock</span>
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <input type="checkbox" wire:model.live="status" value="low_stock" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>Low Stock</span>
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <input type="checkbox" wire:model.live="status" value="out_of_stock" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>Out of Stock</span>
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <input type="checkbox" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>Favorites</span>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Group By Section --}}
                                    <div class="flex-1 p-3">
                                        <div class="mb-2 flex items-center justify-between">
                                            <div class="flex items-center gap-1.5">
                                                <flux:icon name="rectangle-group" class="size-4 text-zinc-400" />
                                                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Group By</span>
                                            </div>
                                            <button class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">+ Add Custom</button>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <input type="checkbox" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>Category</span>
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <input type="checkbox" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>Status</span>
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <input type="checkbox" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>Warehouse</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </flux:menu>
                    </flux:dropdown>
                @endif
            </div>

            {{-- Right Group: Pagination Info + View Toggle --}}
            <div class="flex items-center gap-3">
                {{-- Pagination Info & Navigation --}}
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $items->firstItem() ?? 0 }}-{{ $items->lastItem() ?? 0 }}/{{ $items->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button 
                            type="button"
                            wire:click="goToPreviousPage"
                            @disabled($items->onFirstPage())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button 
                            type="button"
                            wire:click="goToNextPage"
                            @disabled(!$items->hasMorePages())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                {{-- View Toggle --}}
                <x-ui.view-toggle :view="$view" :views="['list', 'grid', 'kanban']" />
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div>
        @if($view === 'list')
            {{-- List View --}}
            <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                        <tr>
                            <th scope="col" class="w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                                <input 
                                    type="checkbox" 
                                    wire:model.live="selectAll"
                                    class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600"
                                >
                            </th>
                            <th scope="col" class="w-10 py-3 pr-2">
                                {{-- Favorite --}}
                            </th>
                            @if($visibleColumns['name'])
                                <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Product</th>
                            @endif
                            @if($visibleColumns['sku'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">SKU</th>
                            @endif
                            @if($visibleColumns['category'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Category</th>
                            @endif
                            @if($visibleColumns['stock'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Stock</th>
                            @endif
                            @if($visibleColumns['price'])
                                <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Price</th>
                            @endif
                            @if($visibleColumns['status'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                            @endif
                            <th scope="col" class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8">
                                {{-- Column Config --}}
                                <flux:dropdown position="bottom" align="end">
                                    <button class="flex items-center justify-center rounded p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                        <flux:icon name="adjustments-horizontal" class="size-4" />
                                    </button>

                                    <flux:menu class="w-48">
                                        <div class="px-2 py-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">Toggle Columns</div>
                                        <flux:menu.separator />
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.name" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Product</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.sku" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>SKU</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.category" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Category</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.stock" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Stock</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.price" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Price</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.status" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Status</span>
                                        </label>
                                    </flux:menu>
                                </flux:dropdown>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse ($items as $item)
                            <tr 
                                onclick="window.location.href='{{ route('inventory.products.edit', $item->id) }}'"
                                class="cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            >
                                <td class="py-4 pl-4 pr-1 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selected"
                                        value="{{ $item->id }}"
                                        class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600"
                                    >
                                </td>
                                <td class="py-4 pr-2" onclick="event.stopPropagation()">
                                    <button 
                                        wire:click="toggleFavorite({{ $item->id }})"
                                        class="flex items-center justify-center rounded p-1 transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                    >
                                        @if($item->is_favorite)
                                            <flux:icon name="star" class="size-4 fill-amber-400 text-amber-400" variant="solid" />
                                        @else
                                            <flux:icon name="star" class="size-4 text-zinc-300 hover:text-amber-400 dark:text-zinc-600" />
                                        @endif
                                    </button>
                                </td>
                                @if($visibleColumns['name'])
                                    <td class="py-4 pl-2 pr-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                                                <flux:icon name="photo" class="size-5" />
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item->name }}</span>
                                                @if($item->description)
                                                    <p class="line-clamp-1 text-xs text-zinc-500 dark:text-zinc-400">{{ Str::limit($item->description, 40) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                @endif
                                @if($visibleColumns['sku'])
                                    <td class="px-4 py-4">
                                        <code class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ $item->sku }}
                                        </code>
                                    </td>
                                @endif
                                @if($visibleColumns['category'])
                                    <td class="px-4 py-4">
                                        @if($item->category)
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $item->category->color ? 'bg-' . $item->category->color . '-100 text-' . $item->category->color . '-700 dark:bg-' . $item->category->color . '-900/30 dark:text-' . $item->category->color . '-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                                {{ $item->category->name }}
                                            </span>
                                        @else
                                            <span class="text-sm text-zinc-400">-</span>
                                        @endif
                                    </td>
                                @endif
                                @if($visibleColumns['stock'])
                                    <td class="px-4 py-4">
                                        @php
                                            $onHand = $item->on_hand ?? null;
                                            $forecastIn = $item->forecast_in ?? null;
                                            $forecastOut = $item->forecast_out ?? null;
                                            $available = $item->available ?? null;
                                            $showForecast = $onHand !== null && $forecastIn !== null && $forecastOut !== null && $available !== null;
                                            $displayQty = $showForecast ? (int) $available : (int) $item->quantity;
                                        @endphp

                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 w-12 rounded-full bg-zinc-100 dark:bg-zinc-800">
                                                <div
                                                    class="h-full rounded-full {{ $displayQty < 10 ? 'bg-red-500' : ($displayQty < 30 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                    style="width: {{ min(100, max(5, ($displayQty / 100) * 100)) }}%"
                                                ></div>
                                            </div>
                                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ number_format($displayQty) }}</span>
                                        </div>

                                        @if($showForecast)
                                            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                On Hand: {{ number_format((int) $onHand) }}
                                                <span class="mx-1">|</span>
                                                In: {{ number_format((int) $forecastIn) }}
                                                <span class="mx-1">|</span>
                                                Out: {{ number_format((int) $forecastOut) }}
                                            </div>
                                        @endif
                                    </td>
                                @endif
                                @if($visibleColumns['price'])
                                    <td class="px-4 py-4 text-right">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($item->selling_price ?? 0, 0, ',', '.') }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['status'])
                                    <td class="px-4 py-4">
                                        @php
                                            $statusConfig = match($item->status) {
                                                'in_stock' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'label' => 'In Stock'],
                                                'low_stock' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'label' => 'Low Stock'],
                                                'out_of_stock' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'label' => 'Out of Stock'],
                                                default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => ucfirst($item->status)],
                                            };
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                            {{ $statusConfig['label'] }}
                                        </span>
                                    </td>
                                @endif
                                <td class="py-4 pr-4 sm:pr-6 lg:pr-8"></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                            <flux:icon name="archive-box" class="size-6 text-zinc-400" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No products found</p>
                                            <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Try adjusting your search or filters</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($view === 'grid')
            {{-- Grid/Thumbnail View --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                @forelse ($items as $item)
                    <a 
                        href="{{ route('inventory.products.edit', $item->id) }}"
                        wire:navigate
                        class="group relative flex flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700"
                    >
                        {{-- Image Area --}}
                        <div class="relative flex aspect-square w-full items-center justify-center bg-zinc-50 dark:bg-zinc-800/50">
                            <flux:icon name="photo" class="size-12 text-zinc-200 dark:text-zinc-700" />
                            
                            {{-- Favorite Badge --}}
                            @if($item->is_favorite)
                                <div class="absolute left-2 top-2">
                                    <flux:icon name="star" class="size-5 fill-amber-400 text-amber-400" variant="solid" />
                                </div>
                            @endif

                            {{-- Status Badge --}}
                            @php
                                $statusColor = match($item->status) {
                                    'in_stock' => 'bg-emerald-500',
                                    'low_stock' => 'bg-amber-500',
                                    'out_of_stock' => 'bg-red-500',
                                    default => 'bg-zinc-500',
                                };
                            @endphp
                            <div class="absolute right-2 top-2">
                                <span class="h-2.5 w-2.5 rounded-full {{ $statusColor }} block"></span>
                            </div>
                        </div>
                        
                        {{-- Content --}}
                        <div class="flex flex-1 flex-col p-3">
                            <h3 class="line-clamp-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item->name }}</h3>
                            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{{ $item->sku }}</p>
                            
                            <div class="mt-auto flex items-end justify-between pt-3">
                                <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($item->selling_price ?? 0, 0, ',', '.') }}</span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ number_format($item->quantity) }} pcs</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon name="archive-box" class="size-6 text-zinc-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">No products found</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Try adjusting your search or filters</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

        @elseif($view === 'kanban')
            {{-- Kanban View --}}
            @php
                $statuses = [
                    'in_stock' => ['label' => 'In Stock', 'color' => 'emerald', 'headerBg' => 'bg-emerald-50 dark:bg-emerald-900/20'],
                    'low_stock' => ['label' => 'Low Stock', 'color' => 'amber', 'headerBg' => 'bg-amber-50 dark:bg-amber-900/20'],
                    'out_of_stock' => ['label' => 'Out of Stock', 'color' => 'red', 'headerBg' => 'bg-red-50 dark:bg-red-900/20'],
                ];
            @endphp
            <div class="flex gap-4 overflow-x-auto pb-4 -mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                @foreach($statuses as $statusKey => $statusInfo)
                    <div class="flex w-80 flex-shrink-0 flex-col rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/50">
                        {{-- Column Header --}}
                        <div class="flex items-center justify-between rounded-t-lg {{ $statusInfo['headerBg'] }} px-3 py-2.5">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-{{ $statusInfo['color'] }}-500"></span>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $statusInfo['label'] }}</span>
                                <span class="rounded-full bg-white px-1.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                    {{ $itemsByStatus?->get($statusKey)?->count() ?? 0 }}
                                </span>
                            </div>
                            <a href="{{ route('inventory.products.create') }}" wire:navigate class="rounded p-1 text-zinc-400 hover:bg-white hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                <flux:icon name="plus" class="size-4" />
                            </a>
                        </div>

                        {{-- Column Cards --}}
                        <div class="flex flex-1 flex-col gap-2 p-2 max-h-[calc(100vh-280px)] overflow-y-auto">
                            @forelse($itemsByStatus?->get($statusKey, collect()) ?? collect() as $item)
                                <a 
                                    href="{{ route('inventory.products.edit', $item->id) }}"
                                    wire:navigate
                                    class="group rounded-lg border border-zinc-200 bg-white p-3 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                >
                                    <div class="mb-2 flex items-start justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-zinc-100 dark:bg-zinc-700">
                                                <flux:icon name="photo" class="size-4 text-zinc-400" />
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item->name }}</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->sku }}</p>
                                            </div>
                                        </div>
                                        @if($item->is_favorite)
                                            <flux:icon name="star" class="size-4 shrink-0 fill-amber-400 text-amber-400" variant="solid" />
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($item->selling_price ?? 0, 0, ',', '.') }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ number_format($item->quantity) }} pcs</span>
                                    </div>
                                </a>
                            @empty
                                <div class="flex flex-1 items-center justify-center py-8">
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500">No products</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Column Footer --}}
                        @if(($itemsByStatus?->get($statusKey)?->count() ?? 0) > 0)
                            <div class="border-t border-zinc-200 px-3 py-2 dark:border-zinc-700">
                                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                    Total: Rp {{ number_format($itemsByStatus->get($statusKey)->sum('selling_price'), 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
