<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" wire:navigate class="flex h-9 w-9 items-center justify-center rounded-lg text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200">
                <flux:icon name="arrow-left" class="size-5" />
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('modules.dashboard') }}</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('dashboard.welcome_message') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <flux:icon name="calendar" class="size-4" />
            <span>{{ now()->format('F j, Y') }}</span>
        </div>
    </div>

    {{-- Pending Actions Bar --}}
    @if(array_sum($pendingActions) > 0)
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-900/20">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                <flux:icon name="bell-alert" class="size-4" />
            </div>
            <span class="text-sm font-medium text-amber-800 dark:text-amber-200">{{ __('dashboard.pending_actions') }}:</span>
            <div class="flex flex-wrap items-center gap-2">
                @if($pendingActions['pending_quotations'] > 0)
                    <a href="{{ route('sales.orders.index') }}" wire:navigate class="inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-xs font-medium text-zinc-700 shadow-sm transition-colors hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        {{ $pendingActions['pending_quotations'] }} {{ __('sales.quotations') }}
                    </a>
                @endif
                @if($pendingActions['orders_to_invoice'] > 0)
                    <a href="{{ route('sales.invoices.pending') }}" wire:navigate class="inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-xs font-medium text-zinc-700 shadow-sm transition-colors hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        {{ $pendingActions['orders_to_invoice'] }} {{ __('sales.to_invoice') }}
                    </a>
                @endif
                @if($pendingActions['orders_to_deliver'] > 0)
                    <a href="{{ route('delivery.orders.index') }}" wire:navigate class="inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-xs font-medium text-zinc-700 shadow-sm transition-colors hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        {{ $pendingActions['orders_to_deliver'] }} {{ __('sales.to_deliver') }}
                    </a>
                @endif
                @if($pendingActions['overdue_invoices'] > 0)
                    <a href="{{ route('invoicing.invoices.index') }}?status=overdue" wire:navigate class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700 shadow-sm transition-colors hover:bg-red-200 dark:bg-red-900/50 dark:text-red-300 dark:hover:bg-red-900/70">
                        {{ $pendingActions['overdue_invoices'] }} {{ __('invoicing.status.overdue') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- KPI Cards --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Sales --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                    <flux:icon name="banknotes" class="size-5" />
                </div>
                @if($salesMetrics['sales_change'] != 0)
                    <span class="inline-flex items-center gap-1 text-xs font-medium {{ $salesMetrics['sales_change'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                        <flux:icon name="{{ $salesMetrics['sales_change'] > 0 ? 'arrow-trending-up' : 'arrow-trending-down' }}" class="size-3" />
                        {{ abs(round($salesMetrics['sales_change'], 1)) }}%
                    </span>
                @endif
            </div>
            <div class="mt-3">
                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($salesMetrics['total_sales'] / 1000000, 1) }}M</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('dashboard.sales_this_month') }}</p>
            </div>
        </div>

        {{-- Total Orders --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                    <flux:icon name="shopping-cart" class="size-5" />
                </div>
                @if($salesMetrics['orders_change'] != 0)
                    <span class="inline-flex items-center gap-1 text-xs font-medium {{ $salesMetrics['orders_change'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                        <flux:icon name="{{ $salesMetrics['orders_change'] > 0 ? 'arrow-trending-up' : 'arrow-trending-down' }}" class="size-3" />
                        {{ abs(round($salesMetrics['orders_change'], 1)) }}%
                    </span>
                @endif
            </div>
            <div class="mt-3">
                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($salesMetrics['total_orders']) }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('dashboard.orders_this_month') }}</p>
            </div>
        </div>

        {{-- Outstanding Invoices --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                    <flux:icon name="document-text" class="size-5" />
                </div>
                @if($invoiceMetrics['overdue_count'] > 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                        {{ $invoiceMetrics['overdue_count'] }} {{ __('dashboard.overdue') }}
                    </span>
                @endif
            </div>
            <div class="mt-3">
                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($invoiceMetrics['total_outstanding'] / 1000000, 1) }}M</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('dashboard.outstanding_invoices') }}</p>
            </div>
        </div>

        {{-- Inventory Value --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400">
                    <flux:icon name="cube" class="size-5" />
                </div>
                @if($inventoryMetrics['low_stock_count'] > 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                        {{ $inventoryMetrics['low_stock_count'] }} {{ __('dashboard.low_stock') }}
                    </span>
                @endif
            </div>
            <div class="mt-3">
                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($inventoryMetrics['total_inventory_value'] / 1000000, 1) }}M</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('dashboard.inventory_value') }}</p>
            </div>
        </div>
    </div>

    {{-- Cash Flow Summary --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-2">
                <flux:icon name="arrow-down-circle" class="size-4 text-emerald-500" />
                <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('dashboard.receivables') }}</span>
            </div>
            <p class="mt-2 text-xl font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($cashFlow['receivables'] / 1000000, 1) }}M</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-2">
                <flux:icon name="arrow-up-circle" class="size-4 text-red-500" />
                <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('dashboard.payables') }}</span>
            </div>
            <p class="mt-2 text-xl font-semibold text-red-600 dark:text-red-400">Rp {{ number_format($cashFlow['payables'] / 1000000, 1) }}M</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-2">
                <flux:icon name="check-circle" class="size-4 text-blue-500" />
                <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('dashboard.received') }}</span>
            </div>
            <p class="mt-2 text-xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($cashFlow['received_this_month'] / 1000000, 1) }}M</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-2">
                <flux:icon name="arrow-path" class="size-4 text-violet-500" />
                <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('dashboard.net_flow') }}</span>
            </div>
            <p class="mt-2 text-xl font-semibold {{ $cashFlow['net_cash_flow'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                {{ $cashFlow['net_cash_flow'] >= 0 ? '+' : '' }}Rp {{ number_format($cashFlow['net_cash_flow'] / 1000000, 1) }}M
            </p>
        </div>
    </div>

    {{-- Charts and Tables Row --}}
    <div class="mb-6 grid gap-6 lg:grid-cols-3">
        {{-- Sales Chart --}}
        <div class="lg:col-span-2 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <h3 class="mb-4 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('dashboard.sales_overview') }}</h3>
            <div class="h-64" x-data="{
                chartData: @js($salesChartData),
                init() {
                    const maxSales = Math.max(...this.chartData.map(d => d.sales));
                    this.chartData = this.chartData.map(d => ({
                        ...d,
                        height: maxSales > 0 ? (d.sales / maxSales) * 100 : 0
                    }));
                }
            }">
                <div class="flex h-full items-end justify-between gap-2">
                    <template x-for="(item, index) in chartData" :key="index">
                        <div class="flex flex-1 flex-col items-center gap-2">
                            <div class="relative w-full">
                                <div 
                                    class="w-full rounded-t-lg bg-violet-500 transition-all duration-500 dark:bg-violet-600"
                                    :style="'height: ' + item.height + '%'"
                                    :title="'Rp ' + new Intl.NumberFormat('id-ID').format(item.sales)"
                                ></div>
                            </div>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400" x-text="item.month.split(' ')[0]"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Top Customers --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <h3 class="mb-4 text-sm font-semibold text-zinc-900 dark:text-zinc-100">Top Customers</h3>
            <div class="space-y-3">
                @forelse($topCustomers as $customer)
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ strtoupper(substr($customer['name'], 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $customer['name'] }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $customer['order_count'] }} orders</p>
                        </div>
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            Rp {{ number_format($customer['total_sales'] / 1000, 0) }}K
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">No data available</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Orders and Invoices --}}
    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        {{-- Recent Orders --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent Orders</h3>
                <a href="{{ route('sales.orders.index') }}" wire:navigate class="text-xs text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">View all →</a>
            </div>
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($recentOrders as $order)
                    <a href="{{ route('sales.orders.edit', $order['id']) }}" wire:navigate class="flex items-center justify-between px-5 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $order['order_number'] }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $order['customer_name'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($order['total'], 0, ',', '.') }}</p>
                            @php
                                $statusColor = match($order['status']) {
                                    'draft', 'confirmed' => 'text-zinc-600 dark:text-zinc-400',
                                    'sales_order' => 'text-amber-600 dark:text-amber-400',
                                    'delivered' => 'text-emerald-600 dark:text-emerald-400',
                                    'cancelled' => 'text-red-600 dark:text-red-400',
                                    default => 'text-zinc-600 dark:text-zinc-400',
                                };
                            @endphp
                            <p class="text-xs {{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $order['status'])) }}</p>
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">No orders yet</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Invoices --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent Invoices</h3>
                <a href="{{ route('invoicing.invoices.index') }}" wire:navigate class="text-xs text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">View all →</a>
            </div>
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($recentInvoices as $invoice)
                    <a href="{{ route('invoicing.invoices.edit', $invoice['id']) }}" wire:navigate class="flex items-center justify-between px-5 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice['invoice_number'] }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $invoice['customer_name'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($invoice['total'], 0, ',', '.') }}</p>
                            @php
                                $statusColor = match($invoice['status']) {
                                    'draft' => 'text-zinc-600 dark:text-zinc-400',
                                    'sent' => 'text-blue-600 dark:text-blue-400',
                                    'partial' => 'text-amber-600 dark:text-amber-400',
                                    'paid' => 'text-emerald-600 dark:text-emerald-400',
                                    'overdue' => 'text-red-600 dark:text-red-400',
                                    default => 'text-zinc-600 dark:text-zinc-400',
                                };
                            @endphp
                            <p class="text-xs {{ $statusColor }}">{{ ucfirst($invoice['status']) }}</p>
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">No invoices yet</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Low Stock Products --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Low Stock Alert</h3>
                <a href="{{ route('inventory.products.index') }}" wire:navigate class="text-xs text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">View all →</a>
            </div>
            <div class="space-y-3">
                @forelse($lowStockProducts as $product)
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $product['name'] }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product['sku'] ?? 'No SKU' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $product['quantity'] <= 0 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                {{ $product['quantity'] }} left
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="py-4 text-center">
                        <flux:icon name="check-circle" class="mx-auto size-8 text-emerald-500" />
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">All products are well stocked!</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <h3 class="mb-4 text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent Activity</h3>
            <div class="space-y-3">
                @forelse($recentActivities as $activity)
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon name="clock" class="size-3 text-zinc-500" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-zinc-700 dark:text-zinc-300">
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $activity['causer_name'] }}</span>
                                {{ $activity['description'] }}
                            </p>
                            <p class="text-xs text-zinc-400 dark:text-zinc-500">
                                {{ $activity['created_at']->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">No recent activity</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mt-6 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="mb-4 text-sm font-semibold text-zinc-900 dark:text-zinc-100">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
            <a href="{{ route('sales.orders.create') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                <flux:icon name="plus-circle" class="size-6 text-emerald-500" />
                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">New Order</span>
            </a>
            <a href="{{ route('invoicing.invoices.create') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                <flux:icon name="document-plus" class="size-6 text-blue-500" />
                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">New Invoice</span>
            </a>
            <a href="{{ route('sales.customers.create') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                <flux:icon name="user-plus" class="size-6 text-violet-500" />
                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">New Customer</span>
            </a>
            <a href="{{ route('inventory.products.create') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                <flux:icon name="cube" class="size-6 text-amber-500" />
                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">New Product</span>
            </a>
            <a href="{{ route('purchase.rfq.create') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                <flux:icon name="shopping-bag" class="size-6 text-cyan-500" />
                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">New RFQ</span>
            </a>
            <a href="{{ route('reports.index') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                <flux:icon name="chart-bar" class="size-6 text-pink-500" />
                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Reports</span>
            </a>
        </div>
    </div>
</div>
