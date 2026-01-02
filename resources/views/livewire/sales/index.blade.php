<div>
    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group: Title, Gear --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.orders.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New Order
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Sales Overview</span>
                
                {{-- Actions Menu (Gear) --}}
                <flux:dropdown position="bottom" align="start">
                    <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="cog-6-tooth" class="size-5" />
                    </button>

                    <flux:menu class="w-48">
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>Export Report</span>
                        </button>
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Right Group: Date --}}
            <div class="flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                <flux:icon name="calendar" class="size-4" />
                <span>{{ now()->format('F Y') }}</span>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Two Column Layout --}}
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Stats --}}
            <div class="space-y-6 lg:col-span-4 lg:sticky lg:top-20 lg:h-fit">
                {{-- Recent Orders (Simple List) --}}
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Recent Orders</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Last 5 Orders</span>
                        <a href="{{ route('sales.orders.create') }}" wire:navigate class="rounded-md bg-zinc-900 px-2 py-1 text-xs font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            New Order
                        </a>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($recentOrders->take(5) as $order)
                            <a href="{{ route('sales.orders.edit', $order->id) }}" wire:navigate class="flex items-center justify-between px-4 py-2 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 odd:bg-white even:bg-zinc-50/50 dark:odd:bg-zinc-900 dark:even:bg-zinc-900/50">
                                <div>
                                    <span class="text-sm font-light text-zinc-600 dark:text-zinc-300">{{ $order->order_number }}</span>
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $order->customer->name ?? '-' }}</p>
                                </div>
                                <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                            </a>
                        @empty
                            <div class="px-5 py-6 text-center text-sm font-light text-zinc-400">No orders yet</div>
                        @endforelse
                    </div>
                    <div class="border-t border-zinc-100 px-5 py-3 dark:border-zinc-800">
                        <a href="{{ route('sales.orders.index') }}" wire:navigate class="text-xs font-light text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                            View all orders →
                        </a>
                    </div>
                </div>

                {{-- Statistics Card --}}
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Statistics</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Sales Overview</span>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Total Orders</span>
                            <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ number_format($totalOrders) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Quotations</span>
                            <span class="text-sm font-normal text-zinc-600 dark:text-zinc-400">{{ number_format($quotations) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Sales Orders</span>
                            <span class="text-sm font-normal text-amber-600 dark:text-amber-400">{{ number_format($salesOrders) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">To Invoice</span>
                            <span class="text-sm font-normal text-blue-600 dark:text-blue-400">{{ number_format($toInvoice) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">To Deliver</span>
                            <span class="text-sm font-normal text-violet-600 dark:text-violet-400">{{ number_format($toDeliver) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Completed</span>
                            <span class="text-sm font-normal text-emerald-600 dark:text-emerald-400">{{ number_format($completedOrders) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Cancelled</span>
                            <span class="text-sm font-normal text-red-600 dark:text-red-400">{{ number_format($cancelledOrders) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Invoice Statistics Card --}}
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Invoices</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Invoice Status</span>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Draft</span>
                            <span class="text-sm font-normal text-zinc-500 dark:text-zinc-400">{{ number_format($draftInvoices) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Sent</span>
                            <span class="text-sm font-normal text-blue-600 dark:text-blue-400">{{ number_format($sentInvoices) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Partial</span>
                            <span class="text-sm font-normal text-amber-600 dark:text-amber-400">{{ number_format($partialInvoices) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Paid</span>
                            <span class="text-sm font-normal text-emerald-600 dark:text-emerald-400">{{ number_format($paidInvoices) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Overdue</span>
                            <span class="text-sm font-normal {{ $overdueInvoices > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-500 dark:text-zinc-400' }}">{{ number_format($overdueInvoices) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Main Content --}}
            <div class="space-y-6 lg:col-span-8">
                {{-- Overview Stats (6 cards) --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="exclamation-triangle" class="size-4 text-red-500 dark:text-red-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Overdue</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold {{ $overdueInvoices > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-100' }}">{{ number_format($overdueInvoices) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Overdue invoices</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="clock" class="size-4 text-emerald-500 dark:text-emerald-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Awaiting</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($awaitingPayment / 1000000, 1) }}M</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Awaiting payment</p>
                    </div>
                </div>

                {{-- Revenue Card with Chart --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Revenue Overview</h2>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">Last 6 months</span>
                    </div>
                    <div class="p-5">
                        <div class="mb-4 grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Revenue</p>
                                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($totalRevenue / 1000000, 1) }}M</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">This Month</p>
                                <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($revenueThisMonth / 1000000, 1) }}M</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Last Month</p>
                                <p class="mt-1 text-2xl font-bold text-zinc-600 dark:text-zinc-400">Rp {{ number_format($revenueLastMonth / 1000000, 1) }}M</p>
                            </div>
                        </div>
                        
                        {{-- Simple Bar Chart --}}
                        <div class="mt-6">
                            <div class="flex items-end justify-between gap-2 h-32">
                                @php
                                    $maxRevenue = $monthlyRevenue->max('revenue') ?: 1;
                                @endphp
                                @forelse($monthlyRevenue as $data)
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-t relative" style="height: {{ max(($data['revenue'] / $maxRevenue) * 100, 5) }}%">
                                            <div class="absolute inset-0 bg-emerald-500 dark:bg-emerald-400 rounded-t opacity-80"></div>
                                        </div>
                                        <span class="text-[10px] text-zinc-500 dark:text-zinc-400">{{ $data['month'] }}</span>
                                    </div>
                                @empty
                                    @for($i = 0; $i < 6; $i++)
                                        <div class="flex-1 flex flex-col items-center gap-1">
                                            <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-t h-4"></div>
                                            <span class="text-[10px] text-zinc-500 dark:text-zinc-400">-</span>
                                        </div>
                                    @endfor
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Monthly Performance --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Monthly Performance</h2>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ now()->format('F Y') }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 p-5 sm:grid-cols-4">
                        <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">New Orders</p>
                            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($ordersThisMonth) }}</p>
                            @if($ordersLastMonth > 0)
                                @php $orderChange = (($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100; @endphp
                                <p class="mt-1 text-xs {{ $orderChange >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $orderChange >= 0 ? '+' : '' }}{{ number_format($orderChange, 0) }}% vs last month
                                </p>
                            @endif
                        </div>
                        <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Revenue</p>
                            <p class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($revenueThisMonth / 1000000, 1) }}M</p>
                            @if($revenueLastMonth > 0)
                                @php $revenueChange = (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100; @endphp
                                <p class="mt-1 text-xs {{ $revenueChange >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $revenueChange >= 0 ? '+' : '' }}{{ number_format($revenueChange, 0) }}% vs last month
                                </p>
                            @endif
                        </div>
                        <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Avg Order Value</p>
                            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($avgOrderValueThisMonth / 1000, 0) }}K</p>
                        </div>
                        <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Conversion Rate</p>
                            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ ($quotations + $salesOrders) > 0 ? number_format(($salesOrders / ($quotations + $salesOrders)) * 100, 0) : 0 }}%</p>
                        </div>
                    </div>
                </div>

                {{-- Top Customers --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
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

                {{-- Quick Actions --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Quick Actions</h2>
                    </div>
                    <div class="grid grid-cols-2 gap-2 p-4 sm:grid-cols-4">
                        <a href="{{ route('sales.orders.index') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                            <flux:icon name="document-text" class="size-6 text-zinc-400" />
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">My Quotations</span>
                        </a>
                        <a href="{{ route('sales.orders.all') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                            <flux:icon name="shopping-cart" class="size-6 text-zinc-400" />
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Sales Orders</span>
                        </a>
                        <a href="{{ route('sales.customers.index') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                            <flux:icon name="users" class="size-6 text-zinc-400" />
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Customers</span>
                        </a>
                        <a href="{{ route('invoicing.invoices.index') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                            <flux:icon name="banknotes" class="size-6 text-zinc-400" />
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Invoices</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
