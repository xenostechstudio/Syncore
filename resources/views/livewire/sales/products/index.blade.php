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
                <a href="{{ route('sales.products.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">
                    Products
                </span>

                {{-- Actions Menu (Gear) --}}
                <flux:dropdown position="bottom" align="start">
                    <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="cog-6-tooth" class="size-5" />
                    </button>

                    <flux:menu class="w-48">
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>Import products</span>
                        </button>
                        <a href="{{ route('export.products') }}" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>Export all</span>
                        </a>
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Center: Search with single horizontal dropdown (Filters / Sort / Group) --}}
            <div class="flex flex-1 items-center justify-center">
                @if(count($selected) > 0)
                    {{-- Selection Toolbar --}}
                    <div class="flex items-center gap-2">
                        <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-zinc-100 px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-200 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200">
                            <flux:icon name="x-mark" class="size-4" />
                            <span>{{ count($selected) }} Selected</span>
                        </button>
                        <div class="h-5 w-px bg-zinc-300 dark:bg-zinc-600"></div>
                        <button wire:click="bulkActivate" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-sm font-medium text-emerald-600 hover:bg-emerald-50 dark:border-emerald-800 dark:bg-zinc-800 dark:text-emerald-400 dark:hover:bg-emerald-900/20">
                            <flux:icon name="check-circle" class="size-4" />
                            Activate
                        </button>
                        <button wire:click="bulkDeactivate" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700">
                            <flux:icon name="pause-circle" class="size-4" />
                            Deactivate
                        </button>
                        <button wire:click="exportSelected" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            Export
                        </button>
                        <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete {{ count($selected) }} product(s)?" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
                            <flux:icon name="trash" class="size-4" />
                            Delete
                        </button>
                    </div>
                @else
                {{-- Wrapper for searchbox + dropdown to center dropdown on searchbox --}}
                <x-ui.searchbox-dropdown placeholder="Search products..." widthClass="w-[520px]" width="520px" align="center">
                    <div class="flex flex-col gap-4 p-3 md:flex-row">
                        {{-- Filters column --}}
                        <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:pr-3 dark:border-zinc-700">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                <flux:icon name="funnel" class="size-3.5" />
                                <span>Filters</span>
                            </div>
                            <div class="space-y-1">
                                <button type="button" wire:click="$set('status', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>All Status</span>
                                    @if(empty($status))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('status', 'in_stock')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <div class="flex items-center gap-2">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        <span>In Stock</span>
                                    </div>
                                    @if($status === 'in_stock')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('status', 'low_stock')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <div class="flex items-center gap-2">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        <span>Low Stock</span>
                                    </div>
                                    @if($status === 'low_stock')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('status', 'out_of_stock')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <div class="flex items-center gap-2">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        <span>Out of Stock</span>
                                    </div>
                                    @if($status === 'out_of_stock')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                            </div>
                        </div>

                        {{-- Sort column --}}
                        <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:px-3 dark:border-zinc-700">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                <flux:icon name="arrows-up-down" class="size-3.5" />
                                <span>Sort By</span>
                            </div>
                            <div class="space-y-1">
                                <button type="button" wire:click="$set('sort', 'latest')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Latest</span>
                                    @if($sort === 'latest')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('sort', 'oldest')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Oldest</span>
                                    @if($sort === 'oldest')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('sort', 'name')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Name A-Z</span>
                                    @if($sort === 'name')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('sort', 'price_high')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Price: High to Low</span>
                                    @if($sort === 'price_high')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('sort', 'price_low')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Price: Low to High</span>
                                    @if($sort === 'price_low')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('sort', 'stock_high')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Stock: High to Low</span>
                                    @if($sort === 'stock_high')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('sort', 'stock_low')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Stock: Low to High</span>
                                    @if($sort === 'stock_low')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                            </div>
                        </div>

                        {{-- Group column --}}
                        <div class="flex-1 md:pl-3">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                <flux:icon name="rectangle-group" class="size-3.5" />
                                <span>Group By</span>
                            </div>
                            <div class="space-y-1">
                                <button type="button" wire:click="$set('groupBy', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>None</span>
                                    @if(empty($groupBy))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('groupBy', 'status')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Status</span>
                                    @if($groupBy === 'status')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('groupBy', 'category')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Category</span>
                                    @if($groupBy === 'category')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                            </div>
                        </div>
                    </div>
                </x-ui.searchbox-dropdown>
                @endif
            </div>

            {{-- Right Group: Pagination Info + View Toggle --}}
            <div class="flex items-center gap-3">
                {{-- Pagination Info & Navigation --}}
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}/{{ $products->currentPage() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button 
                            type="button"
                            wire:click="goToPreviousPage"
                            @disabled($products->onFirstPage())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button 
                            type="button"
                            wire:click="goToNextPage"
                            @disabled(!$products->hasMorePages())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                {{-- View Toggle (List, Grid, Kanban) --}}
                <x-ui.view-toggle :view="$view" :views="['list', 'grid', 'kanban']" />
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div>
        @if($view === 'kanban')
            {{-- Kanban View --}}
            <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-zinc-100 p-6 sm:-mx-6 lg:-mx-8 dark:bg-zinc-950">
                <div class="flex gap-4" style="min-width: max-content;">
                    {{-- In Stock Column --}}
                    <div class="w-80 flex-shrink-0">
                        <div class="mb-3 flex items-center justify-between rounded-lg bg-emerald-100 px-4 py-2.5 dark:bg-emerald-900/30">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">In Stock</span>
                            </div>
                            <span class="rounded-full bg-emerald-200 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-800 dark:text-emerald-300">
                                {{ $groupedProducts['in_stock']->count() ?? 0 }}
                            </span>
                        </div>
                        <div class="space-y-3 max-h-[calc(100vh-220px)] overflow-y-auto pr-1">
                            @forelse($groupedProducts['in_stock'] ?? [] as $product)
                                <a href="{{ route('sales.products.edit', $product->id) }}" wire:navigate
                                   class="block rounded-lg border border-zinc-200 bg-white p-4 shadow-sm transition-all hover:border-emerald-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-emerald-600">
                                    <div class="mb-3 flex items-start justify-between gap-2">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-medium text-zinc-900 truncate dark:text-zinc-100">{{ $product->name }}</h4>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product->sku }}</p>
                                        </div>
                                        <button type="button" wire:click.prevent="toggleFavorite({{ $product->id }})" class="flex-shrink-0">
                                            <flux:icon name="star" class="size-4 {{ $product->is_favorite ? 'text-amber-400' : 'text-zinc-300 hover:text-amber-300' }}" />
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between border-t border-zinc-100 pt-3 dark:border-zinc-700">
                                        <div>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Price</p>
                                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($product->selling_price ?? 0, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Stock</p>
                                            <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ number_format($product->quantity) }}</p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="rounded-lg border-2 border-dashed border-zinc-300 bg-white/50 p-6 text-center dark:border-zinc-700 dark:bg-zinc-800/30">
                                    <flux:icon name="cube" class="mx-auto size-8 text-zinc-300 dark:text-zinc-600" />
                                    <p class="mt-2 text-sm text-zinc-400 dark:text-zinc-500">No products</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Low Stock Column --}}
                    <div class="w-80 flex-shrink-0">
                        <div class="mb-3 flex items-center justify-between rounded-lg bg-amber-100 px-4 py-2.5 dark:bg-amber-900/30">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                                <span class="text-sm font-semibold text-amber-700 dark:text-amber-400">Low Stock</span>
                            </div>
                            <span class="rounded-full bg-amber-200 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-800 dark:text-amber-300">
                                {{ $groupedProducts['low_stock']->count() ?? 0 }}
                            </span>
                        </div>
                        <div class="space-y-3 max-h-[calc(100vh-220px)] overflow-y-auto pr-1">
                            @forelse($groupedProducts['low_stock'] ?? [] as $product)
                                <a href="{{ route('sales.products.edit', $product->id) }}" wire:navigate
                                   class="block rounded-lg border border-zinc-200 bg-white p-4 shadow-sm transition-all hover:border-amber-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-amber-600">
                                    <div class="mb-3 flex items-start justify-between gap-2">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-medium text-zinc-900 truncate dark:text-zinc-100">{{ $product->name }}</h4>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product->sku }}</p>
                                        </div>
                                        <button type="button" wire:click.prevent="toggleFavorite({{ $product->id }})" class="flex-shrink-0">
                                            <flux:icon name="star" class="size-4 {{ $product->is_favorite ? 'text-amber-400' : 'text-zinc-300 hover:text-amber-300' }}" />
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between border-t border-zinc-100 pt-3 dark:border-zinc-700">
                                        <div>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Price</p>
                                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($product->selling_price ?? 0, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Stock</p>
                                            <p class="text-sm font-semibold text-amber-600 dark:text-amber-400">{{ number_format($product->quantity) }}</p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="rounded-lg border-2 border-dashed border-zinc-300 bg-white/50 p-6 text-center dark:border-zinc-700 dark:bg-zinc-800/30">
                                    <flux:icon name="cube" class="mx-auto size-8 text-zinc-300 dark:text-zinc-600" />
                                    <p class="mt-2 text-sm text-zinc-400 dark:text-zinc-500">No products</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Out of Stock Column --}}
                    <div class="w-80 flex-shrink-0">
                        <div class="mb-3 flex items-center justify-between rounded-lg bg-red-100 px-4 py-2.5 dark:bg-red-900/30">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                                <span class="text-sm font-semibold text-red-700 dark:text-red-400">Out of Stock</span>
                            </div>
                            <span class="rounded-full bg-red-200 px-2.5 py-0.5 text-xs font-bold text-red-700 dark:bg-red-800 dark:text-red-300">
                                {{ $groupedProducts['out_of_stock']->count() ?? 0 }}
                            </span>
                        </div>
                        <div class="space-y-3 max-h-[calc(100vh-220px)] overflow-y-auto pr-1">
                            @forelse($groupedProducts['out_of_stock'] ?? [] as $product)
                                <a href="{{ route('sales.products.edit', $product->id) }}" wire:navigate
                                   class="block rounded-lg border border-zinc-200 bg-white p-4 shadow-sm transition-all hover:border-red-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-red-600">
                                    <div class="mb-3 flex items-start justify-between gap-2">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-medium text-zinc-900 truncate dark:text-zinc-100">{{ $product->name }}</h4>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product->sku }}</p>
                                        </div>
                                        <button type="button" wire:click.prevent="toggleFavorite({{ $product->id }})" class="flex-shrink-0">
                                            <flux:icon name="star" class="size-4 {{ $product->is_favorite ? 'text-amber-400' : 'text-zinc-300 hover:text-amber-300' }}" />
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between border-t border-zinc-100 pt-3 dark:border-zinc-700">
                                        <div>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Price</p>
                                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($product->selling_price ?? 0, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Stock</p>
                                            <p class="text-sm font-semibold text-red-600 dark:text-red-400">{{ number_format($product->quantity) }}</p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="rounded-lg border-2 border-dashed border-zinc-300 bg-white/50 p-6 text-center dark:border-zinc-700 dark:bg-zinc-800/30">
                                    <flux:icon name="cube" class="mx-auto size-8 text-zinc-300 dark:text-zinc-600" />
                                    <p class="mt-2 text-sm text-zinc-400 dark:text-zinc-500">No products</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @elseif($view === 'list')
            @php
                $isGroupedList = !empty($groupBy);
                $columnsCount = 3;
                if($visibleColumns['name']) $columnsCount++;
                if($visibleColumns['sku']) $columnsCount++;
                if($visibleColumns['price']) $columnsCount++;
                if($visibleColumns['stock']) $columnsCount++;
                if($visibleColumns['status']) $columnsCount++;
            @endphp

            <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                @if($isGroupedList)
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($groupedListProducts as $group)
                            @php
                                $isOpen = in_array($group['id'], $openGroups ?? [], true);
                            @endphp

                            <div class="bg-white dark:bg-zinc-900">
                                <button
                                    type="button"
                                    wire:click="toggleGroup('{{ $group['id'] }}')"
                                    class="flex w-full items-center justify-between gap-4 px-4 py-3 text-left sm:px-6 lg:px-8 hover:bg-zinc-50 dark:hover:bg-zinc-800/40"
                                >
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $group['label'] }}</span>
                                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                {{ count($group['items']) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="chevron-down" class="size-4 text-zinc-400 transition-transform {{ $isOpen ? 'rotate-180' : '' }}" />
                                    </div>
                                </button>

                                @if($isOpen)
                                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                                        <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                                            <tr>
                                                {{-- Select All --}}
                                                <th class="w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                                                    <input 
                                                        type="checkbox" 
                                                        wire:model.live="selectAll"
                                                        class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600"
                                                    >
                                                </th>
                                                {{-- Favorite column (no header label) --}}
                                                <th class="w-10 px-2 py-3"></th>

                                                @if($visibleColumns['name'])
                                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Name</th>
                                                @endif
                                                @if($visibleColumns['sku'])
                                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">SKU</th>
                                                @endif
                                                @if($visibleColumns['price'])
                                                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Price</th>
                                                @endif
                                                @if($visibleColumns['stock'])
                                                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Stock</th>
                                                @endif
                                                @if($visibleColumns['status'])
                                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                                                @endif

                                                {{-- Column visibility toggle --}}
                                                <th class="w-10 py-3 pr-4 text-right sm:pr-6 lg:pr-8">
                                                    <flux:dropdown position="bottom" align="end">
                                                        <button class="flex items-center justify-center rounded p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                                            <flux:icon name="adjustments-horizontal" class="size-4" />
                                                        </button>

                                                        <flux:menu class="w-48">
                                                            <div class="px-2 py-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">Toggle Columns</div>
                                                            <flux:menu.separator />
                                                            <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                                                <input type="checkbox" wire:model.live="visibleColumns.name" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                                <span>Name</span>
                                                            </label>
                                                            <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                                                <input type="checkbox" wire:model.live="visibleColumns.sku" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                                <span>SKU</span>
                                                            </label>
                                                            <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                                                <input type="checkbox" wire:model.live="visibleColumns.price" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                                <span>Price</span>
                                                            </label>
                                                            <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                                                <input type="checkbox" wire:model.live="visibleColumns.stock" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                                <span>Stock</span>
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
                                            @forelse($group['items'] as $product)
                                                @php $isSelected = in_array($product->id, $selected); @endphp
                                                <tr 
                                                    onclick="window.location.href='{{ route('sales.products.edit', $product->id) }}'"
                                                    class="group cursor-pointer transition-all duration-150 {{ $isSelected ? 'bg-zinc-900/[0.03] dark:bg-zinc-100/[0.03]' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}"
                                                >
                                                    {{-- Row selection --}}
                                                    <td class="relative py-3 pl-4 pr-2 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                                        <div class="absolute inset-y-0 left-0 w-0.5 transition-all duration-150 {{ $isSelected ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-transparent group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700' }}"></div>
                                                        <input 
                                                            type="checkbox" 
                                                            wire:model.live="selected"
                                                            value="{{ $product->id }}"
                                                            class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600 {{ $isSelected ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-100/20' : '' }}"
                                                        >
                                                    </td>

                                                    {{-- Favorite star --}}
                                                    <td class="px-2 py-3 text-center" onclick="event.stopPropagation()">
                                                        <button 
                                                            type="button"
                                                            wire:click="toggleFavorite({{ $product->id }})"
                                                            class="inline-flex items-center justify-center"
                                                        >
                                                            <flux:icon 
                                                                name="star" 
                                                                class="size-4 {{ $product->is_favorite ? 'text-amber-400' : 'text-zinc-300 hover:text-amber-300' }}"
                                                            />
                                                        </button>
                                                    </td>

                                                    {{-- Name --}}
                                                    @if($visibleColumns['name'])
                                                        <td class="px-4 py-3">
                                                            <div class="flex flex-col">
                                                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</span>
                                                                @if($product->description)
                                                                    <span class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-1">{{ $product->description }}</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    @endif

                                                    {{-- SKU --}}
                                                    @if($visibleColumns['sku'])
                                                        <td class="px-4 py-3">
                                                            <code class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                                                {{ $product->sku }}
                                                            </code>
                                                        </td>
                                                    @endif

                                                    {{-- Price --}}
                                                    @if($visibleColumns['price'])
                                                        <td class="px-4 py-3 text-right">
                                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($product->selling_price ?? 0, 0, ',', '.') }}</span>
                                                        </td>
                                                    @endif

                                                    {{-- Stock --}}
                                                    @if($visibleColumns['stock'])
                                                        <td class="px-4 py-3 text-right">
                                                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ number_format($product->quantity) }}</span>
                                                        </td>
                                                    @endif

                                                    {{-- Status --}}
                                                    @if($visibleColumns['status'])
                                                        <td class="px-4 py-3">
                                                            @php
                                                                $statusConfig = match($product->status) {
                                                                    'in_stock' => ['bg' => 'bg-emerald-100 dark:bg-emerald-500/10', 'text' => 'text-emerald-700 dark:text-emerald-400', 'dot' => 'bg-emerald-500'],
                                                                    'low_stock' => ['bg' => 'bg-amber-100 dark:bg-amber-500/10', 'text' => 'text-amber-700 dark:text-amber-400', 'dot' => 'bg-amber-500'],
                                                                    'out_of_stock' => ['bg' => 'bg-red-100 dark:bg-red-500/10', 'text' => 'text-red-700 dark:text-red-400', 'dot' => 'bg-red-500'],
                                                                    default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-700 dark:text-zinc-300', 'dot' => 'bg-zinc-500'],
                                                                };
                                                            @endphp
                                                            <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                                                <span class="h-1.5 w-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                                                                {{ ucfirst(str_replace('_', ' ', $product->status)) }}
                                                            </span>
                                                        </td>
                                                    @endif

                                                    {{-- Empty cell for alignment with header actions --}}
                                                    <td class="py-3 pr-4 sm:pr-6 lg:pr-8"></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $columnsCount }}" class="px-6 py-10 text-center">
                                                        <div class="flex flex-col items-center gap-3">
                                                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                                                <flux:icon name="cube" class="size-6 text-zinc-400" />
                                                            </div>
                                                            <div>
                                                                <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No products found</p>
                                                                <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Try adjusting your search or filters</p>
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
                                @endif
                            </div>
                        @empty
                            <div class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <flux:icon name="cube" class="size-6 text-zinc-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No products found</p>
                                        <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Try adjusting your search or filters</p>
                                    </div>
                                    <flux:button variant="secondary" size="sm" wire:click="clearFilters">
                                        Clear Filters
                                    </flux:button>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @else
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                            <tr>
                                {{-- Select All --}}
                                <th class="w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selectAll"
                                        class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600"
                                    >
                                </th>
                                {{-- Favorite column (no header label) --}}
                                <th class="w-10 px-2 py-3"></th>

                                @if($visibleColumns['name'])
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Name</th>
                                @endif
                                @if($visibleColumns['sku'])
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">SKU</th>
                                @endif
                                @if($visibleColumns['price'])
                                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Price</th>
                                @endif
                                @if($visibleColumns['stock'])
                                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Stock</th>
                                @endif
                                @if($visibleColumns['status'])
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                                @endif

                                {{-- Column visibility toggle --}}
                                <th class="w-10 py-3 pr-4 text-right sm:pr-6 lg:pr-8">
                                    <flux:dropdown position="bottom" align="end">
                                        <button class="flex items-center justify-center rounded p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                            <flux:icon name="adjustments-horizontal" class="size-4" />
                                        </button>

                                        <flux:menu class="w-48">
                                            <div class="px-2 py-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">Toggle Columns</div>
                                            <flux:menu.separator />
                                            <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                                <input type="checkbox" wire:model.live="visibleColumns.name" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>Name</span>
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                                <input type="checkbox" wire:model.live="visibleColumns.sku" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>SKU</span>
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                                <input type="checkbox" wire:model.live="visibleColumns.price" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>Price</span>
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                                <input type="checkbox" wire:model.live="visibleColumns.stock" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>Stock</span>
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
                            @forelse($products as $product)
                                @php $isSelected = in_array($product->id, $selected); @endphp
                                <tr 
                                    onclick="window.location.href='{{ route('sales.products.edit', $product->id) }}'"
                                    class="group cursor-pointer transition-all duration-150 {{ $isSelected ? 'bg-zinc-900/[0.03] dark:bg-zinc-100/[0.03]' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}"
                                >
                                    {{-- Row selection --}}
                                    <td class="relative py-3 pl-4 pr-2 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                        <div class="absolute inset-y-0 left-0 w-0.5 transition-all duration-150 {{ $isSelected ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-transparent group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700' }}"></div>
                                        <input 
                                            type="checkbox" 
                                            wire:model.live="selected"
                                            value="{{ $product->id }}"
                                            class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600 {{ $isSelected ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-100/20' : '' }}"
                                        >
                                    </td>

                                    {{-- Favorite star --}}
                                    <td class="px-2 py-3 text-center" onclick="event.stopPropagation()">
                                        <button 
                                            type="button"
                                            wire:click="toggleFavorite({{ $product->id }})"
                                            class="inline-flex items-center justify-center"
                                        >
                                            <flux:icon 
                                                name="star" 
                                                class="size-4 {{ $product->is_favorite ? 'text-amber-400' : 'text-zinc-300 hover:text-amber-300' }}"
                                            />
                                        </button>
                                    </td>

                                    {{-- Name --}}
                                    @if($visibleColumns['name'])
                                        <td class="px-4 py-3">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</span>
                                                @if($product->description)
                                                    <span class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-1">{{ $product->description }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    @endif

                                    {{-- SKU --}}
                                    @if($visibleColumns['sku'])
                                        <td class="px-4 py-3">
                                            <code class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                                {{ $product->sku }}
                                            </code>
                                        </td>
                                    @endif

                                    {{-- Price --}}
                                    @if($visibleColumns['price'])
                                        <td class="px-4 py-3 text-right">
                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($product->selling_price ?? 0, 0, ',', '.') }}</span>
                                        </td>
                                    @endif

                                    {{-- Stock --}}
                                    @if($visibleColumns['stock'])
                                        <td class="px-4 py-3 text-right">
                                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ number_format($product->quantity) }}</span>
                                        </td>
                                    @endif

                                    {{-- Status --}}
                                    @if($visibleColumns['status'])
                                        <td class="px-4 py-3">
                                            @php
                                                $statusConfig = match($product->status) {
                                                    'in_stock' => ['bg' => 'bg-emerald-100 dark:bg-emerald-500/10', 'text' => 'text-emerald-700 dark:text-emerald-400', 'dot' => 'bg-emerald-500'],
                                                    'low_stock' => ['bg' => 'bg-amber-100 dark:bg-amber-500/10', 'text' => 'text-amber-700 dark:text-amber-400', 'dot' => 'bg-amber-500'],
                                                    'out_of_stock' => ['bg' => 'bg-red-100 dark:bg-red-500/10', 'text' => 'text-red-700 dark:text-red-400', 'dot' => 'bg-red-500'],
                                                    default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-700 dark:text-zinc-300', 'dot' => 'bg-zinc-500'],
                                                };
                                            @endphp
                                            <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                                                {{ ucfirst(str_replace('_', ' ', $product->status)) }}
                                            </span>
                                        </td>
                                    @endif

                                    {{-- Empty cell for alignment with header actions --}}
                                    <td class="py-3 pr-4 sm:pr-6 lg:pr-8"></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $columnsCount }}" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                                <flux:icon name="cube" class="size-6 text-zinc-400" />
                                            </div>
                                            <div>
                                                <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No products found</p>
                                                <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Try adjusting your search or filters</p>
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
                @endif
            </div>
        @elseif($view === 'grid')
            {{-- Grid View --}}
            <div class="-mx-4 -mt-6 -mb-6 bg-zinc-50 p-6 sm:-mx-6 lg:-mx-8 dark:bg-zinc-950">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                    @forelse($products as $product)
                        @php
                            $statusConfig = match($product->status) {
                                'in_stock' => ['color' => 'emerald', 'label' => 'In Stock'],
                                'low_stock' => ['color' => 'amber', 'label' => 'Low Stock'],
                                'out_of_stock' => ['color' => 'red', 'label' => 'Out of Stock'],
                                default => ['color' => 'zinc', 'label' => 'Unknown'],
                            };
                        @endphp
                        <a 
                            href="{{ route('sales.products.edit', $product->id) }}"
                            wire:navigate
                            class="group relative flex flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                        >
                            {{-- Product Image Placeholder --}}
                            <div class="relative flex h-40 w-full items-center justify-center bg-gradient-to-br from-zinc-50 to-zinc-100 dark:from-zinc-800 dark:to-zinc-900">
                                <flux:icon name="cube" class="size-12 text-zinc-300 dark:text-zinc-600" />
                                {{-- Favorite Button --}}
                                <button 
                                    type="button"
                                    wire:click.prevent="toggleFavorite({{ $product->id }})"
                                    class="absolute right-3 top-3 rounded-full bg-white/80 p-1.5 shadow-sm transition-all hover:bg-white dark:bg-zinc-800/80 dark:hover:bg-zinc-800"
                                >
                                    <flux:icon name="star" class="size-4 {{ $product->is_favorite ? 'text-amber-400' : 'text-zinc-400 hover:text-amber-400' }}" />
                                </button>
                                {{-- Status Badge --}}
                                <div class="absolute left-3 top-3">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-{{ $statusConfig['color'] }}-100 px-2 py-0.5 text-xs font-medium text-{{ $statusConfig['color'] }}-700 dark:bg-{{ $statusConfig['color'] }}-900/50 dark:text-{{ $statusConfig['color'] }}-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-{{ $statusConfig['color'] }}-500"></span>
                                        {{ $statusConfig['label'] }}
                                    </span>
                                </div>
                            </div>
                            {{-- Product Info --}}
                            <div class="flex flex-1 flex-col p-4">
                                <div class="mb-3">
                                    <h3 class="font-semibold text-zinc-900 line-clamp-1 group-hover:text-violet-600 dark:text-zinc-100 dark:group-hover:text-violet-400">{{ $product->name }}</h3>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product->sku }}</p>
                                </div>
                                <div class="mt-auto flex items-end justify-between border-t border-zinc-100 pt-3 dark:border-zinc-700">
                                    <div>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Price</p>
                                        <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($product->selling_price ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Stock</p>
                                        <p class="text-sm font-bold text-{{ $statusConfig['color'] }}-600 dark:text-{{ $statusConfig['color'] }}-400">{{ number_format($product->quantity) }}</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <flux:icon name="cube" class="size-8 text-zinc-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">No products found</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Try adjusting your search or filters</p>
                                </div>
                                <button type="button" wire:click="clearFilters" class="mt-2 rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>
