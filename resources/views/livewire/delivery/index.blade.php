<div>
    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group: Title, Gear --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('delivery.orders.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New Delivery
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Delivery Overview</span>
                
                {{-- Actions Menu (Gear) --}}
                <flux:dropdown position="bottom" align="start">
                    <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="cog-6-tooth" class="size-5" />
                    </button>

                    <flux:menu class="w-48">
                        <a href="{{ route('export.delivery-orders') }}" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>Export Report</span>
                        </a>
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
                {{-- Recent Deliveries --}}
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Recent Deliveries</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Last 5 Deliveries</span>
                        <a href="{{ route('delivery.orders.create') }}" wire:navigate class="rounded-md bg-zinc-900 px-2 py-1 text-xs font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            New
                        </a>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($recentDeliveries as $delivery)
                            <a href="{{ route('delivery.orders.edit', $delivery->id) }}" wire:navigate class="flex items-center justify-between px-4 py-2 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 odd:bg-white even:bg-zinc-50/50 dark:odd:bg-zinc-900 dark:even:bg-zinc-900/50">
                                <div>
                                    <span class="text-sm font-light text-zinc-600 dark:text-zinc-300">{{ $delivery->delivery_number }}</span>
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $delivery->salesOrder?->customer?->name ?? '-' }}</p>
                                </div>
                                @php
                                    $statusValue = $delivery->status instanceof \BackedEnum ? $delivery->status->value : $delivery->status;
                                    $statusColors = match($statusValue) {
                                        'pending' => 'text-zinc-600 dark:text-zinc-400',
                                        'picked' => 'text-blue-600 dark:text-blue-400',
                                        'in_transit' => 'text-violet-600 dark:text-violet-400',
                                        'delivered' => 'text-emerald-600 dark:text-emerald-400',
                                        'failed' => 'text-red-600 dark:text-red-400',
                                        'returned' => 'text-amber-600 dark:text-amber-400',
                                        default => 'text-zinc-600 dark:text-zinc-400',
                                    };
                                    $statusLabel = $delivery->status instanceof \BackedEnum && method_exists($delivery->status, 'label') 
                                        ? $delivery->status->label() 
                                        : ucfirst(str_replace('_', ' ', $statusValue));
                                @endphp
                                <span class="text-xs font-medium {{ $statusColors }}">{{ $statusLabel }}</span>
                            </a>
                        @empty
                            <div class="px-5 py-6 text-center text-sm font-light text-zinc-400">No deliveries yet</div>
                        @endforelse
                    </div>
                    <div class="border-t border-zinc-100 px-5 py-3 dark:border-zinc-800">
                        <a href="{{ route('delivery.orders.index') }}" wire:navigate class="text-xs font-light text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                            View all deliveries →
                        </a>
                    </div>
                </div>

                {{-- Statistics Card --}}
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Statistics</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Delivery Overview</span>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Total Deliveries</span>
                            <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ number_format($totalDeliveries) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Pending</span>
                            <span class="text-sm font-normal text-amber-600 dark:text-amber-400">{{ number_format($pendingDeliveries) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">In Transit</span>
                            <span class="text-sm font-normal text-violet-600 dark:text-violet-400">{{ number_format($inTransit) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Delivered</span>
                            <span class="text-sm font-normal text-emerald-600 dark:text-emerald-400">{{ number_format($delivered) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Failed</span>
                            <span class="text-sm font-normal {{ $failedDeliveries > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-500 dark:text-zinc-400' }}">{{ number_format($failedDeliveries) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Returned</span>
                            <span class="text-sm font-normal {{ $returnedDeliveries > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-500 dark:text-zinc-400' }}">{{ number_format($returnedDeliveries) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Top Couriers --}}
                @if($topCouriers->isNotEmpty())
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Top Couriers</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($topCouriers as $courier)
                            <div class="flex items-center justify-between px-4 py-2.5">
                                <span class="text-sm font-light text-zinc-600 dark:text-zinc-300">{{ $courier->courier }}</span>
                                <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ number_format($courier->count) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Right Column: Main Content --}}
            <div class="space-y-6 lg:col-span-8">
                {{-- Overview Stats (6 cards) --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="clock" class="size-4 text-amber-500 dark:text-amber-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Pending</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($pendingDeliveries) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Awaiting pickup</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="truck" class="size-4 text-violet-500 dark:text-violet-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">In Transit</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($inTransit) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">On the way</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="check-circle" class="size-4 text-emerald-500 dark:text-emerald-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Delivered</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($delivered) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $deliveredThisMonth }} this month</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="shopping-cart" class="size-4 text-blue-500 dark:text-blue-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Ready to Ship</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($pendingSalesOrders) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Orders pending delivery</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="arrow-path" class="size-4 text-cyan-500 dark:text-cyan-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Avg. Time</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $avgDeliveryTime }} days</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Average delivery time</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="exclamation-triangle" class="size-4 text-red-500 dark:text-red-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Issues</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold {{ ($failedDeliveries + $returnedDeliveries) > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-100' }}">{{ number_format($failedDeliveries + $returnedDeliveries) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Failed & returned</p>
                    </div>
                </div>

                {{-- Delivery Chart --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Delivery Overview</h2>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">Last 6 months</span>
                    </div>
                    <div class="p-5">
                        <div class="mb-4 grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Deliveries</p>
                                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($totalDeliveries) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">This Month</p>
                                <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($deliveriesThisMonth) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Last Month</p>
                                <p class="mt-1 text-2xl font-bold text-zinc-600 dark:text-zinc-400">{{ number_format($deliveriesLastMonth) }}</p>
                            </div>
                        </div>
                        
                        {{-- Simple Bar Chart --}}
                        <div class="mt-6">
                            <div class="flex items-end justify-between gap-2 h-32">
                                @php
                                    $maxDeliveries = $monthlyDeliveries->max('deliveries') ?: 1;
                                @endphp
                                @forelse($monthlyDeliveries as $data)
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-t relative" style="height: {{ max(($data['deliveries'] / $maxDeliveries) * 100, 5) }}%">
                                            <div class="absolute inset-0 bg-violet-500 dark:bg-violet-400 rounded-t opacity-80"></div>
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
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">New Deliveries</p>
                            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($deliveriesThisMonth) }}</p>
                            @if($deliveriesLastMonth > 0)
                                @php $deliveryChange = (($deliveriesThisMonth - $deliveriesLastMonth) / $deliveriesLastMonth) * 100; @endphp
                                <p class="mt-1 text-xs {{ $deliveryChange >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $deliveryChange >= 0 ? '+' : '' }}{{ number_format($deliveryChange, 0) }}% vs last month
                                </p>
                            @endif
                        </div>
                        <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Completed</p>
                            <p class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($deliveredThisMonth) }}</p>
                            @if($deliveredLastMonth > 0)
                                @php $completedChange = (($deliveredThisMonth - $deliveredLastMonth) / $deliveredLastMonth) * 100; @endphp
                                <p class="mt-1 text-xs {{ $completedChange >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $completedChange >= 0 ? '+' : '' }}{{ number_format($completedChange, 0) }}% vs last month
                                </p>
                            @endif
                        </div>
                        <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Success Rate</p>
                            @php
                                $totalCompleted = $delivered + $failedDeliveries + $returnedDeliveries;
                                $successRate = $totalCompleted > 0 ? ($delivered / $totalCompleted) * 100 : 100;
                            @endphp
                            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($successRate, 0) }}%</p>
                        </div>
                        <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Avg. Time</p>
                            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $avgDeliveryTime }} days</p>
                        </div>
                    </div>
                </div>

                {{-- Status Breakdown --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Status Breakdown</h2>
                    </div>
                    <div class="grid grid-cols-2 gap-2 p-4 sm:grid-cols-3 lg:grid-cols-6">
                        @php
                            $statuses = [
                                'pending' => ['label' => 'Pending', 'color' => 'zinc', 'icon' => 'clock'],
                                'picked' => ['label' => 'Picked', 'color' => 'blue', 'icon' => 'hand-raised'],
                                'in_transit' => ['label' => 'In Transit', 'color' => 'violet', 'icon' => 'truck'],
                                'delivered' => ['label' => 'Delivered', 'color' => 'emerald', 'icon' => 'check-circle'],
                                'failed' => ['label' => 'Failed', 'color' => 'red', 'icon' => 'x-circle'],
                                'returned' => ['label' => 'Returned', 'color' => 'amber', 'icon' => 'arrow-uturn-left'],
                            ];
                        @endphp
                        @foreach($statuses as $key => $status)
                            <div class="rounded-lg border border-zinc-200 p-3 text-center dark:border-zinc-800">
                                <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-full bg-{{ $status['color'] }}-100 dark:bg-{{ $status['color'] }}-900/30">
                                    <flux:icon name="{{ $status['icon'] }}" class="size-4 text-{{ $status['color'] }}-600 dark:text-{{ $status['color'] }}-400" />
                                </div>
                                <p class="mt-2 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $deliveriesByStatus[$key] ?? 0 }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $status['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Quick Actions</h2>
                    </div>
                    <div class="grid grid-cols-2 gap-2 p-4 sm:grid-cols-4">
                        <a href="{{ route('delivery.orders.index') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                            <flux:icon name="truck" class="size-6 text-zinc-400" />
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">All Deliveries</span>
                        </a>
                        <a href="{{ route('delivery.orders.create') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                            <flux:icon name="plus-circle" class="size-6 text-zinc-400" />
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">New Delivery</span>
                        </a>
                        <a href="{{ route('sales.orders.all') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                            <flux:icon name="shopping-cart" class="size-6 text-zinc-400" />
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Sales Orders</span>
                        </a>
                        <a href="{{ route('inventory.warehouses.index') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                            <flux:icon name="building-storefront" class="size-6 text-zinc-400" />
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Warehouses</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
