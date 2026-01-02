<div>
    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group --}}
            <div class="flex items-center gap-3">
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Inventory Reports</span>
            </div>

            {{-- Right Group: Filters --}}
            <div class="flex items-center gap-3">
                <select wire:model.live="warehouseId" class="rounded-lg border-zinc-200 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                    <option value="">All Warehouses</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-zinc-500">Low Stock:</label>
                    <input type="number" wire:model.live.debounce.500ms="lowStockThreshold" min="1" class="w-16 rounded-lg border-zinc-200 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="cube" class="size-4 text-zinc-500 dark:text-zinc-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Total Products</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['total_products']) }}</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="archive-box" class="size-4 text-blue-500 dark:text-blue-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Total Stock</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['total_stock']) }}</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="banknotes" class="size-4 text-emerald-500 dark:text-emerald-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Total Value</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($summary['total_value'] / 1000000, 1) }}M</p>
            </div>
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                <div class="flex items-center gap-2">
                    <flux:icon name="exclamation-triangle" class="size-4 text-amber-600 dark:text-amber-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">Low Stock</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-amber-700 dark:text-amber-400">{{ number_format($summary['low_stock_count']) }}</p>
            </div>
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                <div class="flex items-center gap-2">
                    <flux:icon name="x-circle" class="size-4 text-red-600 dark:text-red-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-red-600 dark:text-red-400">Out of Stock</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-red-700 dark:text-red-400">{{ number_format($summary['out_of_stock_count']) }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Stock by Warehouse --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Stock by Warehouse</h2>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($stockByWarehouse as $wh)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $wh['name'] }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $wh['product_count'] }} products</p>
                            </div>
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ number_format($wh['total_stock']) }} units</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-zinc-400">No warehouses</div>
                    @endforelse
                </div>
            </div>

            {{-- Low Stock Alert --}}
            <div class="rounded-lg border border-amber-200 bg-white dark:border-amber-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-amber-100 bg-amber-50 px-5 py-4 dark:border-amber-800 dark:bg-amber-900/20">
                    <h2 class="text-sm font-semibold text-amber-800 dark:text-amber-400">Low Stock Alert</h2>
                </div>
                <div class="max-h-64 divide-y divide-zinc-100 overflow-y-auto dark:divide-zinc-800">
                    @forelse($lowStockProducts as $product)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $product['name'] }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product['sku'] }}</p>
                            </div>
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">{{ $product['total_stock'] }} left</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-zinc-400">No low stock items</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Stock Valuation Table --}}
        <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Stock Valuation</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-800/50">
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">SKU</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Quantity</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Cost Price</th>
                            <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Stock Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse(array_slice($stockValuation['items'], 0, 20) as $item)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-5 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item['name'] }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $item['sku'] }}</td>
                                <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ number_format($item['quantity']) }}</td>
                                <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-400">Rp {{ number_format($item['cost_price'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($item['stock_value'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-zinc-500">No products</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                            <td colspan="4" class="px-5 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">Total Value</td>
                            <td class="px-5 py-3 text-right text-sm font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($stockValuation['total_value'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
