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

    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
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
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>Export all</span>
                        </button>
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Center: Search with Status dropdown --}}
            <div class="flex flex-1 items-center justify-center">
                <div class="relative flex h-9 w-[400px] items-center overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search products..." 
                        class="h-full w-full border-0 bg-transparent pl-9 pr-10 text-sm outline-none focus:ring-0" 
                    />

                    {{-- Status dropdown inside search box --}}
                    <flux:dropdown position="bottom" align="end">
                        <button class="absolute right-0 top-0 flex h-full items-center border-l border-zinc-200 px-2.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:border-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-300">
                            <flux:icon name="chevron-down" class="size-4" />
                        </button>

                        <flux:menu class="w-56">
                            <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</div>
                            <flux:menu.separator />
                            <button type="button" wire:click="$set('status', '')" class="flex w-full items-center justify-between px-3 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                <span>All</span>
                                @if(empty($status))<flux:icon name="check" class="size-4 text-zinc-500" />@endif
                            </button>
                            <button type="button" wire:click="$set('status', 'in_stock')" class="flex w-full items-center justify-between px-3 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                <span>In Stock</span>
                                @if($status === 'in_stock')<flux:icon name="check" class="size-4 text-zinc-500" />@endif
                            </button>
                            <button type="button" wire:click="$set('status', 'low_stock')" class="flex w-full items-center justify-between px-3 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                <span>Low Stock</span>
                                @if($status === 'low_stock')<flux:icon name="check" class="size-4 text-zinc-500" />@endif
                            </button>
                            <button type="button" wire:click="$set('status', 'out_of_stock')" class="flex w-full items-center justify-between px-3 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                <span>Out of Stock</span>
                                @if($status === 'out_of_stock')<flux:icon name="check" class="size-4 text-zinc-500" />@endif
                            </button>
                        </flux:dropdown>
                    </flux:dropdown>
                </div>
            </div>

            {{-- Right Group: View Toggle & Sort --}}
            <div class="flex items-center gap-3">
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

                <x-ui.view-toggle :view="$view" />
            </div>
        </div>
    </x-slot:header>

    {{-- Content --}}
    <div>
        @if($view === 'list')
            <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
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
                            <tr 
                                onclick="window.location.href='{{ route('sales.products.edit', $product->id) }}'"
                                class="cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            >
                                {{-- Row selection --}}
                                <td class="py-3 pl-4 pr-2 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selected"
                                        value="{{ $product->id }}"
                                        class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600"
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
                                <td colspan="6" class="px-6 py-12 text-center">
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
            </div>
        @else
            {{-- Grid View --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse($products as $product)
                    <a 
                        href="{{ route('sales.products.edit', $product->id) }}"
                        wire:navigate
                        class="group relative flex flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700"
                    >
                        <div class="flex h-36 w-full items-center justify-center bg-zinc-50 dark:bg-zinc-800/50">
                            <flux:icon name="cube" class="size-10 text-zinc-300 dark:text-zinc-600" />
                        </div>
                        <div class="flex flex-1 flex-col p-4">
                            <div class="mb-2 flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</h3>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product->sku }}</p>
                                </div>
                                @php
                                    $statusColor = match($product->status) {
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
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($product->selling_price ?? 0, 0, ',', '.') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Stock</p>
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ number_format($product->quantity) }}</p>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon name="cube" class="size-6 text-zinc-400" />
                            </div>
                            <div>
                                <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No products found</p>
                                <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Try adjusting your search or filters</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- Pagination --}}
        @if($products->hasPages())
            <div class="mt-6">
                {{ $products->links('livewire.pagination') }}
            </div>
        @endif
    </div>
</div>
