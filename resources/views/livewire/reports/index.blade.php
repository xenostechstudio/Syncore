<div>
    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group --}}
            <div class="flex items-center gap-3">
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">{{ __('reports.overview') }}</span>
            </div>

            {{-- Right Group: Period Filter --}}
            <div class="flex items-center gap-3">
                <select wire:model.live="period" class="rounded-lg border-zinc-200 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this_week">This Week</option>
                    <option value="last_week">Last Week</option>
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
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Two Column Layout --}}
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column --}}
            <div class="space-y-6 lg:col-span-4 lg:sticky lg:top-20 lg:h-fit">
                {{-- Quick Links --}}
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Report Categories</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <a href="{{ route('reports.sales') }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <flux:icon name="shopping-cart" class="size-4 text-emerald-500" />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">Sales Reports</span>
                            </div>
                            <flux:icon name="chevron-right" class="size-4 text-zinc-400" />
                        </a>
                        <a href="{{ route('reports.inventory') }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <flux:icon name="archive-box" class="size-4 text-blue-500" />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">Inventory Reports</span>
                            </div>
                            <flux:icon name="chevron-right" class="size-4 text-zinc-400" />
                        </a>
                        <a href="{{ route('reports.financial') }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <flux:icon name="banknotes" class="size-4 text-violet-500" />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">Financial Reports</span>
                            </div>
                            <flux:icon name="chevron-right" class="size-4 text-zinc-400" />
                        </a>
                    </div>
                </div>

                {{-- Invoice Summary --}}
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Invoice Summary</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Collected</span>
                            <span class="text-sm font-normal text-emerald-600 dark:text-emerald-400">Rp {{ number_format($invoiceSummary['total_collected'] / 1000000, 1) }}M</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Outstanding</span>
                            <span class="text-sm font-normal text-amber-600 dark:text-amber-400">Rp {{ number_format($invoiceSummary['outstanding'] / 1000000, 1) }}M</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Overdue</span>
                            <span class="text-sm font-normal text-red-600 dark:text-red-400">Rp {{ number_format($invoiceSummary['overdue'] / 1000000, 1) }}M</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Collection Rate</span>
                            <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ number_format($invoiceSummary['collection_rate'], 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6 lg:col-span-8">
                {{-- Summary Cards --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="banknotes" class="size-4 text-emerald-500 dark:text-emerald-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Total Sales</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($salesSummary['total_sales'] / 1000000, 1) }}M</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $salesSummary['total_orders'] }} orders</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="credit-card" class="size-4 text-blue-500 dark:text-blue-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Revenue Collected</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($invoiceSummary['total_collected'] / 1000000, 1) }}M</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ number_format($invoiceSummary['collection_rate'], 1) }}% collection rate</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="clock" class="size-4 text-amber-500 dark:text-amber-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Outstanding</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($invoiceSummary['outstanding'] / 1000000, 1) }}M</p>
                        <p class="text-xs text-red-500 dark:text-red-400">Rp {{ number_format($invoiceSummary['overdue'] / 1000000, 1) }}M overdue</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="archive-box" class="size-4 text-violet-500 dark:text-violet-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Inventory Value</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($inventorySummary['total_value'] / 1000000, 1) }}M</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $inventorySummary['total_products'] }} products</p>
                    </div>
                </div>

                {{-- Top Products --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Top Products</h2>
                        <a href="{{ route('reports.sales') }}" wire:navigate class="text-xs text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300">
                            View all →
                        </a>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($topProducts as $product)
                            <div class="flex items-center justify-between px-5 py-3">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product->total_quantity }} units sold</p>
                                </div>
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($product->total_sales, 0, ',', '.') }}</span>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center text-sm text-zinc-400">No data available</div>
                        @endforelse
                    </div>
                </div>

                {{-- Top Customers --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Top Customers</h2>
                        <a href="{{ route('reports.sales') }}" wire:navigate class="text-xs text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300">
                            View all →
                        </a>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($topCustomers as $customer)
                            <div class="flex items-center justify-between px-5 py-3">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $customer['customer']['name'] ?? 'Unknown' }}</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $customer['order_count'] }} orders</p>
                                </div>
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($customer['total_sales'], 0, ',', '.') }}</span>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center text-sm text-zinc-400">No data available</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
