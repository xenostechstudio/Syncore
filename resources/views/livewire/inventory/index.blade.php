<div>
    <div class="space-y-6">
    {{-- Two Column Layout like Vercel --}}
    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Left Column: Usage/Stats --}}
        <div class="space-y-6 lg:col-span-4 lg:sticky lg:top-20 lg:h-fit">
            {{-- Recent Items (Simple List) --}}
            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Recent Items</h3>
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                {{-- Inner Header with Add Button --}}
                <div class="flex items-center justify-between px-4 py-3">
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Last 30 Days</span>
                    <button wire:click="create" class="rounded-md bg-zinc-900 px-2 py-1 text-xs font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        Add Item
                    </button>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($recentItems->take(5) as $item)
                        <a href="{{ route('inventory.items.edit', $item->id) }}" wire:navigate class="flex items-center justify-between px-4 py-2 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 odd:bg-white even:bg-zinc-50/50 dark:odd:bg-zinc-900 dark:even:bg-zinc-900/50">
                            <span class="text-sm font-light text-zinc-600 dark:text-zinc-300">{{ $item->name }}</span>
                            <span class="text-sm font-normal {{ $item->quantity < 10 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-100' }}">{{ number_format($item->quantity) }}</span>
                        </a>
                    @empty
                        <div class="px-5 py-6 text-center text-sm font-light text-zinc-400">No items yet</div>
                    @endforelse
                </div>
                <div class="border-t border-zinc-100 px-5 py-3 dark:border-zinc-800">
                    <a href="{{ route('inventory.items.index') }}" wire:navigate class="text-xs font-light text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                        View all items →
                    </a>
                </div>
            </div>

            {{-- Statistics Card (Merged Usage & Activity) --}}
            <h3 class="mb-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">Statistics</h3>
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                {{-- Card Heading --}}
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-800">
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Inventory Overview</span>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    {{-- Total Items --}}
                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Total Items</span>
                        <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ number_format($totalItems) }}</span>
                    </div>
                    {{-- In Stock --}}
                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">In Stock</span>
                        <span class="text-sm font-normal text-emerald-600 dark:text-emerald-400">{{ number_format($inStockItems) }}</span>
                    </div>
                    {{-- Low Stock --}}
                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Low Stock</span>
                        <span class="text-sm font-normal text-amber-600 dark:text-amber-400">{{ number_format($lowStockItems) }}</span>
                    </div>
                    {{-- Out of Stock --}}
                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Out of Stock</span>
                        <span class="text-sm font-normal text-red-600 dark:text-red-400">{{ number_format($outOfStockItems) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Items Grid/List --}}
        <div class="space-y-4 lg:col-span-8">
            {{-- Header with Search and Actions --}}
            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search items..."
                            class="h-9 w-full rounded-lg border border-zinc-200 bg-white py-0 pl-10 pr-4 text-sm font-light text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:focus:border-zinc-600"
                        />
                    </div>
                </div>
                <button type="button" class="flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-500 transition-colors hover:border-zinc-300 hover:text-zinc-900 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-zinc-700 dark:hover:text-zinc-100">
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                </button>
                <x-ui.view-toggle :view="$view" />
                
                <flux:dropdown position="bottom" align="end">
                    <flux:button variant="primary" class="h-9" icon-trailing="chevron-down">Add New</flux:button>
                    <flux:menu>
                        <flux:menu.item href="{{ route('inventory.items.create') }}" wire:navigate>
                            <span class="flex items-center gap-2">
                                <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                                New Item
                            </span>
                        </flux:menu.item>
                        <flux:menu.item href="#" wire:navigate>
                            <span class="flex items-center gap-2">
                                <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.593l6.248-2.083a2.25 2.25 0 00.593-2.607l-9.581-9.581a2.25 2.25 0 00-1.591-.659z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                                </svg>
                                New Category
                            </span>
                        </flux:menu.item>
                        <flux:menu.item href="#" wire:navigate>
                            <span class="flex items-center gap-2">
                                <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                                </svg>
                                New Warehouse
                            </span>
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>
            
            {{-- Items Display --}}
            @if($view === 'list')
                {{-- List View - Single Card --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden">
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($recentItems as $item)
                            <a href="{{ route('inventory.items.edit', $item->id) }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50 cursor-pointer">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                        <svg class="size-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $item->name }}</p>
                                        <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $item->sku }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-6">
                                    <div class="text-right">
                                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ number_format($item->quantity) }} units</p>
                                        <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">${{ number_format($item->selling_price ?? 0, 2) }}</p>
                                    </div>
                                    @php
                                        $statusConfig = match($item->status) {
                                            'in_stock' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'label' => 'In Stock'],
                                            'low_stock' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'label' => 'Low'],
                                            'out_of_stock' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'label' => 'Out'],
                                            default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => ucfirst($item->status)],
                                        };
                                    @endphp
                                    <span class="inline-flex w-16 items-center justify-center rounded-full px-2 py-0.5 text-xs font-light {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                        {{ $statusConfig['label'] }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <div class="py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <svg class="size-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No items found</p>
                                        <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Get started by adding your first item</p>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @else
                {{-- Grid/Card View --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @forelse($recentItems as $item)
                        <a href="{{ route('inventory.items.edit', $item->id) }}" wire:navigate class="block rounded-lg border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700 cursor-pointer">
                            {{-- Image/Icon --}}
                            <div class="mb-4 flex h-24 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                <svg class="size-10 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>
                            </div>

                            {{-- Content --}}
                            <div class="space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $item->name }}</h3>
                                    @php
                                        $statusConfig = match($item->status) {
                                            'in_stock' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'dot' => 'bg-emerald-500'],
                                            'low_stock' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'dot' => 'bg-amber-500'],
                                            'out_of_stock' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'dot' => 'bg-red-500'],
                                            default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'dot' => 'bg-zinc-500'],
                                        };
                                    @endphp
                                    <span class="h-2 w-2 shrink-0 rounded-full {{ $statusConfig['dot'] }}"></span>
                                </div>
                                <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $item->sku }}</p>
                                <div class="flex items-center justify-between pt-2">
                                    <span class="text-lg font-normal text-zinc-900 dark:text-zinc-100">${{ number_format($item->selling_price ?? 0, 2) }}</span>
                                    <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">{{ number_format($item->quantity) }} units</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full rounded-lg border border-dashed border-zinc-200 py-12 text-center dark:border-zinc-800">
                            <div class="flex flex-col items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <svg class="size-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No items found</p>
                                    <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Get started by adding your first item</p>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</div>
</div>
