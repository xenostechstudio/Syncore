<div class="space-y-6">
    {{-- Header --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.orders.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New Order
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Sales Overview</span>
            </div>

            <div class="flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                <flux:icon name="calendar" class="size-4" />
                <span>{{ now()->format('F Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Overview Stats --}}
    <div class="-mx-4 -mt-6 border-b border-zinc-200 bg-white px-4 py-4 sm:-mx-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="document-text" class="size-4 text-zinc-400 dark:text-zinc-500" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Quotations</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($quotations) }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Pending confirmation</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="shopping-cart" class="size-4 text-amber-500 dark:text-amber-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Sales Orders</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($salesOrders) }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">In progress</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="banknotes" class="size-4 text-blue-500 dark:text-blue-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">To Invoice</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($toInvoice) }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Orders pending invoice</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="truck" class="size-4 text-violet-500 dark:text-violet-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">To Deliver</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($toDeliver) }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Orders pending delivery</p>
            </div>
        </div>
    </div>

    {{-- Two Column Layout --}}
    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Left Column: Stats & Quick Actions --}}
        <div class="space-y-6 lg:col-span-4">
            {{-- Revenue Card --}}
            <div class="rounded-2xl border border-zinc-200 bg-gradient-to-b from-white to-zinc-50 dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-950">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Revenue</h2>
                </div>
                <div class="p-5">
                    <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Total revenue (delivered orders)</p>
                    <div class="mt-4 flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2 dark:bg-emerald-900/20">
                        <flux:icon name="arrow-trending-up" class="size-4 text-emerald-600 dark:text-emerald-400" />
                        <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</span>
                        <span class="text-xs text-emerald-600 dark:text-emerald-400">this month</span>
                    </div>
                </div>
            </div>

            {{-- System Stats Card --}}
            <div class="rounded-2xl border border-zinc-200 bg-gradient-to-b from-white to-zinc-50 dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-950">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Statistics</h2>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400">Total Orders</span>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ number_format($totalOrders) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400">Orders This Month</span>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ number_format($ordersThisMonth) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400">Total Customers</span>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ number_format($totalCustomers) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400">Overdue Invoices</span>
                        <span class="font-medium {{ $overdueInvoices > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-100' }}">{{ number_format($overdueInvoices) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400">Awaiting Payment</span>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($awaitingPayment, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions Card --}}
            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Quick Actions</h2>
                </div>
                <div class="p-3">
                    <div class="space-y-1">
                        <a href="{{ route('sales.orders.index') }}" wire:navigate class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-zinc-600 transition-colors hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="document-text" class="size-4" />
                            My Quotations
                        </a>
                        <a href="{{ route('sales.orders.all') }}" wire:navigate class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-zinc-600 transition-colors hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="shopping-cart" class="size-4" />
                            Sales Orders
                        </a>
                        <a href="{{ route('sales.invoices.pending') }}" wire:navigate class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-zinc-600 transition-colors hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="banknotes" class="size-4" />
                            Orders to Invoice
                        </a>
                        <a href="{{ route('sales.customers.index') }}" wire:navigate class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-zinc-600 transition-colors hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="users" class="size-4" />
                            Customers
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Tables --}}
        <div class="space-y-6 lg:col-span-8">
            {{-- Monthly Performance --}}
            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Monthly Performance</h2>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ now()->format('F Y') }}</span>
                </div>
                <div class="grid grid-cols-2 gap-4 p-5 sm:grid-cols-4">
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">New Orders</p>
                        <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($ordersThisMonth) }}</p>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Revenue</p>
                        <p class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($revenueThisMonth / 1000000, 1) }}M</p>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Avg Order Value</p>
                        <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ $ordersThisMonth > 0 ? number_format($revenueThisMonth / $ordersThisMonth / 1000, 0) . 'K' : '0' }}</p>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Conversion Rate</p>
                        <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $quotations > 0 ? number_format(($salesOrders / ($quotations + $salesOrders)) * 100, 0) : 0 }}%</p>
                    </div>
                </div>
            </div>

            {{-- Recent Orders --}}
            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent Orders</h2>
                    <a href="{{ route('sales.orders.index') }}" wire:navigate class="text-xs text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300">
                        View all →
                    </a>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($recentOrders as $order)
                        <a href="{{ route('sales.orders.edit', $order->id) }}" wire:navigate class="flex items-center gap-4 px-5 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $order->order_number }}</p>
                                    @php
                                        $statusConfig = match($order->status) {
                                            'draft' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                            'confirmed' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400'],
                                            'processing' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400'],
                                            'shipped' => ['bg' => 'bg-violet-100 dark:bg-violet-900/30', 'text' => 'text-violet-700 dark:text-violet-400'],
                                            'delivered' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400'],
                                            'cancelled' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400'],
                                            default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $order->customer->name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                                <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $order->created_at->diffForHumans() }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-zinc-400">
                            No orders found
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Top Customers --}}
            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Top Customers</h2>
                    <a href="{{ route('sales.customers.index') }}" wire:navigate class="text-xs text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300">
                        View all →
                    </a>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($topCustomers as $customer)
                        <div class="flex items-center gap-4 px-5 py-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-sm font-normal text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ strtoupper(substr($customer->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $customer->name }}</p>
                                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $customer->sales_orders_count }} orders</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($customer->sales_orders_sum_total ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-zinc-400">
                            No customers found
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
