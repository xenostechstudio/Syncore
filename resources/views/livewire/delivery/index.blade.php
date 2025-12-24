<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-xl font-normal text-zinc-900 dark:text-zinc-100">Overview</h1>
        <p class="text-sm font-light text-zinc-500 dark:text-zinc-400">Track and manage your deliveries</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        {{-- Total Deliveries --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Total Deliveries</span>
                <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
            </div>
            <p class="mt-2 text-2xl font-light text-zinc-900 dark:text-zinc-100">{{ number_format($totalDeliveries) }}</p>
            <p class="mt-1 text-xs font-light text-zinc-400 dark:text-zinc-500">{{ $deliveriesThisMonth }} this month</p>
        </div>

        {{-- Pending --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Pending</span>
                <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="mt-2 text-2xl font-light text-zinc-900 dark:text-zinc-100">{{ number_format($pendingDeliveries) }}</p>
            <p class="mt-1 text-xs font-light text-amber-600 dark:text-amber-400">Awaiting pickup</p>
        </div>

        {{-- In Transit --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">In Transit</span>
                <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
            </div>
            <p class="mt-2 text-2xl font-light text-zinc-900 dark:text-zinc-100">{{ number_format($inTransit) }}</p>
            <p class="mt-1 text-xs font-light text-blue-600 dark:text-blue-400">On the way</p>
        </div>

        {{-- Delivered --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Delivered</span>
                <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="mt-2 text-2xl font-light text-zinc-900 dark:text-zinc-100">{{ number_format($delivered) }}</p>
            <p class="mt-1 text-xs font-light text-emerald-600 dark:text-emerald-400">{{ $deliveredThisMonth }} this month</p>
        </div>
    </div>

    {{-- Status Breakdown --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-6">
        @php
            $statuses = [
                'pending' => ['label' => 'Pending', 'color' => 'zinc'],
                'picked' => ['label' => 'Picked', 'color' => 'blue'],
                'in_transit' => ['label' => 'In Transit', 'color' => 'violet'],
                'delivered' => ['label' => 'Delivered', 'color' => 'emerald'],
                'failed' => ['label' => 'Failed', 'color' => 'red'],
                'returned' => ['label' => 'Returned', 'color' => 'amber'],
            ];
        @endphp
        @foreach($statuses as $key => $status)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-{{ $status['color'] }}-500"></span>
                    <span class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $status['label'] }}</span>
                </div>
                <p class="mt-1 text-lg font-light text-zinc-900 dark:text-zinc-100">{{ $deliveriesByStatus[$key] ?? 0 }}</p>
            </div>
        @endforeach
    </div>

    {{-- Recent Deliveries --}}
    <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
            <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Recent Deliveries</h2>
            <a href="{{ route('delivery.orders.index') }}" wire:navigate class="text-xs font-light text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                View all →
            </a>
        </div>
        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse($recentDeliveries as $delivery)
                <div class="flex items-center justify-between px-5 py-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                            <svg class="size-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $delivery->delivery_number }}</p>
                            <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $delivery->salesOrder->customer->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-light text-zinc-600 dark:text-zinc-300">{{ $delivery->courier }}</p>
                        @php
                            $statusConfig = match($delivery->status->value) {
                                'pending' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                'picked' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400'],
                                'in_transit' => ['bg' => 'bg-violet-100 dark:bg-violet-900/30', 'text' => 'text-violet-700 dark:text-violet-400'],
                                'delivered' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400'],
                                'failed' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400'],
                                'returned' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400'],
                                'cancelled' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400'],
                                default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-light {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                            {{ ucfirst(str_replace('_', ' ', $delivery->status->value)) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <p class="text-sm font-light text-zinc-500 dark:text-zinc-400">No deliveries yet</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
