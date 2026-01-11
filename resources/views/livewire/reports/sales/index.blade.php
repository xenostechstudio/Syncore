<div>
    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group --}}
            <div class="flex items-center gap-3">
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Sales Reports</span>
            </div>

            {{-- Right Group: Filters --}}
            <div class="flex items-center gap-3">
                <select wire:model.live="period" class="rounded-lg border-zinc-200 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                    <option value="today">Today</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_quarter">This Quarter</option>
                    <option value="this_year">This Year</option>
                    <option value="custom">Custom Range</option>
                </select>
                @if($period === 'custom')
                    <input type="date" wire:model.live="startDate" class="rounded-lg border-zinc-200 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                    <span class="text-zinc-500">to</span>
                    <input type="date" wire:model.live="endDate" class="rounded-lg border-zinc-200 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                @endif
                <select wire:model.live="groupBy" class="rounded-lg border-zinc-200 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                    <option value="day">By Day</option>
                    <option value="week">By Week</option>
                    <option value="month">By Month</option>
                </select>
                
                {{-- Export Button --}}
                <button 
                    wire:click="exportPdf"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                >
                    <flux:icon name="arrow-down-tray" class="size-4" wire:loading.remove wire:target="exportPdf" />
                    <flux:icon name="arrow-path" class="size-4 animate-spin" wire:loading wire:target="exportPdf" />
                    <span wire:loading.remove wire:target="exportPdf">Export PDF</span>
                    <span wire:loading wire:target="exportPdf">Exporting...</span>
                </button>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="banknotes" class="size-4 text-emerald-500 dark:text-emerald-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Total Sales</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($summary['total_sales'] / 1000000, 1) }}M</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="shopping-cart" class="size-4 text-blue-500 dark:text-blue-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Total Orders</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['total_orders']) }}</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="calculator" class="size-4 text-violet-500 dark:text-violet-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Avg Order Value</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($summary['avg_order_value'] / 1000, 0) }}K</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="user-plus" class="size-4 text-amber-500 dark:text-amber-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">New Customers</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['new_customers']) }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Sales by Customer --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Top Customers</h2>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($salesByCustomer as $customer)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $customer['customer']['name'] ?? 'Unknown' }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $customer['order_count'] }} orders</p>
                            </div>
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($customer['total_sales'], 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-zinc-400">No data</div>
                    @endforelse
                </div>
            </div>

            {{-- Sales by Product --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Top Products</h2>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($salesByProduct as $product)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product->total_quantity }} units</p>
                            </div>
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($product->total_sales, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-zinc-400">No data</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Salesperson Performance --}}
        <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Salesperson Performance</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-800/50">
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Salesperson</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Orders</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Total Sales</th>
                            <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Avg Order</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($salespersonPerformance as $sp)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-5 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $sp['user']['name'] ?? 'Unknown' }}</td>
                                <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ $sp['order_count'] }}</td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($sp['total_sales'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right text-sm text-zinc-600 dark:text-zinc-400">Rp {{ number_format($sp['avg_order_value'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-sm text-zinc-500">No data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
