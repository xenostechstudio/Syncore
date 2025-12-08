<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-xl font-normal text-zinc-900 dark:text-zinc-100">Overview</h1>
        <p class="text-sm font-light text-zinc-500 dark:text-zinc-400">Monitor your sales performance and orders</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        {{-- Total Orders --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Total Orders</span>
                <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
            </div>
            <p class="mt-2 text-2xl font-light text-zinc-900 dark:text-zinc-100">{{ number_format($totalOrders) }}</p>
            <p class="mt-1 text-xs font-light text-zinc-400 dark:text-zinc-500">{{ $ordersThisMonth }} this month</p>
        </div>

        {{-- Total Revenue --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Total Revenue</span>
                <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="mt-2 text-2xl font-light text-zinc-900 dark:text-zinc-100">${{ number_format($totalRevenue, 0) }}</p>
            <p class="mt-1 text-xs font-light text-emerald-600 dark:text-emerald-400">${{ number_format($revenueThisMonth, 0) }} this month</p>
        </div>

        {{-- Pending Orders --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Pending Orders</span>
                <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="mt-2 text-2xl font-light text-zinc-900 dark:text-zinc-100">{{ number_format($pendingOrders) }}</p>
            <p class="mt-1 text-xs font-light text-amber-600 dark:text-amber-400">Needs attention</p>
        </div>

        {{-- Customers --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Customers</span>
                <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </div>
            <p class="mt-2 text-2xl font-light text-zinc-900 dark:text-zinc-100">{{ number_format($totalCustomers) }}</p>
            <p class="mt-1 text-xs font-light text-zinc-400 dark:text-zinc-500">Active customers</p>
        </div>
    </div>

    {{-- Status Breakdown --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-6">
        @php
            $statuses = [
                'draft' => ['label' => 'Draft', 'color' => 'zinc'],
                'confirmed' => ['label' => 'Confirmed', 'color' => 'blue'],
                'processing' => ['label' => 'Processing', 'color' => 'amber'],
                'shipped' => ['label' => 'Shipped', 'color' => 'violet'],
                'delivered' => ['label' => 'Delivered', 'color' => 'emerald'],
                'cancelled' => ['label' => 'Cancelled', 'color' => 'red'],
            ];
        @endphp
        @foreach($statuses as $key => $status)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-{{ $status['color'] }}-500"></span>
                    <span class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $status['label'] }}</span>
                </div>
                <p class="mt-1 text-lg font-light text-zinc-900 dark:text-zinc-100">{{ $ordersByStatus[$key] ?? 0 }}</p>
            </div>
        @endforeach
    </div>

    {{-- Recent Orders --}}
    <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
            <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Recent Orders</h2>
            <a href="{{ route('sales.orders.index') }}" wire:navigate class="text-xs font-light text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                View all →
            </a>
        </div>
        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse($recentOrders as $order)
                <div class="flex items-center justify-between px-5 py-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                            <svg class="size-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $order->order_number }}</p>
                            <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $order->customer->name }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-light text-zinc-900 dark:text-zinc-100">${{ number_format($order->total, 2) }}</p>
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
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-light {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <p class="text-sm font-light text-zinc-500 dark:text-zinc-400">No orders yet</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
