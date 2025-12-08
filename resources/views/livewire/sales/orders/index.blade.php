<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-normal text-zinc-900 dark:text-zinc-100">Sales Orders</h1>
        <flux:button variant="primary" icon="plus" href="{{ route('sales.orders.create') }}" wire:navigate>
            New Order
        </flux:button>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col gap-4 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search orders..."
                    class="w-64 rounded-lg border border-zinc-200 bg-white py-2 pl-10 pr-4 text-sm font-light text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:focus:border-zinc-600"
                />
            </div>

            {{-- Status Filter --}}
            <select 
                wire:model.live="status"
                class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-light text-zinc-600 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
            >
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="confirmed">Confirmed</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>

            {{-- Sort --}}
            <select 
                wire:model.live="sort"
                class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-light text-zinc-600 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
            >
                <option value="latest">Latest</option>
                <option value="oldest">Oldest</option>
                <option value="total_high">Total: High to Low</option>
                <option value="total_low">Total: Low to High</option>
            </select>

            @if($search || $status || $sort !== 'latest')
                <button wire:click="clearFilters" class="text-sm font-light text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                    Clear filters
                </button>
            @endif
        </div>

        <x-ui.view-toggle :view="$view" />
    </div>

    {{-- Results Count --}}
    <div class="text-sm font-light text-zinc-500 dark:text-zinc-400">
        {{ $orders->total() }} {{ Str::plural('order', $orders->total()) }}
    </div>

    {{-- Content --}}
    @if($view === 'list')
        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900">
                        <th class="px-5 py-3 text-left text-xs font-normal text-zinc-500 dark:text-zinc-400">Order</th>
                        <th class="px-5 py-3 text-left text-xs font-normal text-zinc-500 dark:text-zinc-400">Customer</th>
                        <th class="px-5 py-3 text-left text-xs font-normal text-zinc-500 dark:text-zinc-400">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-normal text-zinc-500 dark:text-zinc-400">Total</th>
                        <th class="px-5 py-3 text-left text-xs font-normal text-zinc-500 dark:text-zinc-400">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-normal text-zinc-500 dark:text-zinc-400"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($orders as $order)
                        <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                        <svg class="size-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $order->order_number }}</p>
                                        <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $order->items->count() ?? 0 }} items</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $order->customer->name }}</p>
                                <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $order->customer->city }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm font-light text-zinc-600 dark:text-zinc-300">{{ $order->order_date->format('M d, Y') }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">${{ number_format($order->total, 2) }}</p>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $statusConfig = match($order->status) {
                                        'draft' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => 'Draft'],
                                        'confirmed' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400', 'label' => 'Confirmed'],
                                        'processing' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'label' => 'Processing'],
                                        'shipped' => ['bg' => 'bg-violet-100 dark:bg-violet-900/30', 'text' => 'text-violet-700 dark:text-violet-400', 'label' => 'Shipped'],
                                        'delivered' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'label' => 'Delivered'],
                                        'cancelled' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'label' => 'Cancelled'],
                                        default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => ucfirst($order->status)],
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-light {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button class="rounded p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="View">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                    <button class="rounded p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Create Delivery">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <svg class="size-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No orders found</p>
                                        <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Try adjusting your search or filters</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        {{-- Grid View --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($orders as $order)
                <div class="rounded-lg border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="mb-4 flex items-start justify-between">
                        <div>
                            <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $order->order_number }}</p>
                            <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $order->order_date->format('M d, Y') }}</p>
                        </div>
                        @php
                            $statusConfig = match($order->status) {
                                'draft' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => 'Draft'],
                                'confirmed' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400', 'label' => 'Confirmed'],
                                'processing' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'label' => 'Processing'],
                                'shipped' => ['bg' => 'bg-violet-100 dark:bg-violet-900/30', 'text' => 'text-violet-700 dark:text-violet-400', 'label' => 'Shipped'],
                                'delivered' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'label' => 'Delivered'],
                                'cancelled' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'label' => 'Cancelled'],
                                default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => ucfirst($order->status)],
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-light {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                            {{ $statusConfig['label'] }}
                        </span>
                    </div>
                    
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $order->customer->name }}</p>
                            <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $order->customer->city }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-zinc-800">
                        <span class="text-lg font-normal text-zinc-900 dark:text-zinc-100">${{ number_format($order->total, 2) }}</span>
                        <button class="rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-light text-zinc-600 transition-colors hover:border-zinc-300 hover:text-zinc-900 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-100">
                            View Details
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center">
                    <p class="text-sm font-light text-zinc-500 dark:text-zinc-400">No orders found</p>
                </div>
            @endforelse
        </div>
    @endif

    {{-- Pagination --}}
    @if($orders->hasPages())
        <div class="flex items-center justify-between border-t border-zinc-200 pt-4 dark:border-zinc-800">
            <p class="text-sm font-light text-zinc-500 dark:text-zinc-400">
                Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} results
            </p>
            {{ $orders->links('livewire.pagination') }}
        </div>
    @endif
</div>
