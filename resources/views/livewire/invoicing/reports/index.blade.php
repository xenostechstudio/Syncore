<div class="flex flex-col gap-6">
    <x-slot:header>
        <div class="flex items-center justify-between">
            <h1 class="text-base font-light text-zinc-600 dark:text-zinc-400">Reports</h1>
            <div class="flex items-center gap-3">
                <select wire:model.live="period" class="rounded-lg border-zinc-200 bg-white text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    <option value="today">Today</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_quarter">This Quarter</option>
                    <option value="this_year">This Year</option>
                </select>
            </div>
        </div>
    </x-slot:header>

    <!-- Period Info -->
    <div class="text-sm text-zinc-500 dark:text-zinc-400">
        Showing data from {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
    </div>

    <!-- Revenue Stats -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Revenue</p>
                    <p class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Invoiced</p>
                    <p class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Rp{{ number_format($totalInvoiced, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                    <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Paid</p>
                    <p class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Rp{{ number_format($totalPaid, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                    <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Outstanding</p>
                    <p class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Rp{{ number_format($totalOutstanding, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice & Order Stats -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Invoice Breakdown -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <h3 class="mb-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">Invoice Status</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded-full bg-emerald-500"></div>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Paid</span>
                    </div>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $paidInvoices }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded-full bg-blue-500"></div>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Pending</span>
                    </div>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $pendingInvoices }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded-full bg-red-500"></div>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Overdue</span>
                    </div>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $overdueInvoices }}</span>
                </div>
                <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Invoices</span>
                        <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $invoiceCount }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <h3 class="mb-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">Sales Orders</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Total Orders</span>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $orderCount }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Order Value</span>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp{{ number_format($orderTotal, 0, ',', '.') }}</span>
                </div>
                @if($orderCount > 0)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Average Order</span>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp{{ number_format($orderTotal / $orderCount, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($invoiceCount > 0 && $orderCount > 0)
                <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Invoice Rate</span>
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ number_format(($invoiceCount / $orderCount) * 100, 1) }}%</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <h3 class="mb-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">Top Customers by Revenue</h3>
        @if($topCustomers->count() > 0)
        <div class="space-y-3">
            @foreach($topCustomers as $index => $item)
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">{{ $index + 1 }}</span>
                    <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $item->customer?->name ?? 'Unknown' }}</span>
                </div>
                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp{{ number_format($item->total_revenue, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-zinc-500 dark:text-zinc-400">No customer data available for this period.</p>
        @endif
    </div>

    <!-- Monthly Revenue Chart (Simple Bar) -->
    @if(count($monthlyData) > 1)
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <h3 class="mb-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">Monthly Revenue</h3>
        <div class="flex items-end gap-2" style="height: 200px;">
            @php
                $maxRevenue = max(array_column($monthlyData, 'revenue')) ?: 1;
            @endphp
            @foreach($monthlyData as $data)
            <div class="flex flex-1 flex-col items-center gap-2">
                <div class="w-full rounded-t bg-emerald-500 dark:bg-emerald-600" style="height: {{ ($data['revenue'] / $maxRevenue) * 160 }}px; min-height: 4px;"></div>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $data['month'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
