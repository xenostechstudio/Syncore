<div>
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">
                {{ session('error') }}
            </x-ui.alert>
        @endif
    </div>

    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group: New Button, Title --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('delivery.orders.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Delivery Orders</span>

                <flux:dropdown position="bottom" align="start">
                    <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="cog-6-tooth" class="size-5" />
                    </button>

                    <flux:menu class="w-48">
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>Import records</span>
                        </button>
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>Export All</span>
                        </button>
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Center Group: Search or Selection Toolbar --}}
            <div class="flex flex-1 items-center justify-center">
                @if(count($selected) > 0)
                    <div class="flex items-center gap-2 animate-in fade-in slide-in-from-top-2 duration-200">
                        <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-zinc-100 px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-200 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-600">
                            <flux:icon name="x-mark" class="size-4" />
                            <span>{{ count($selected) }} Selected</span>
                        </button>

                        <button type="button" wire:click="deleteSelected" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-red-600 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-zinc-700">
                            <flux:icon name="trash" class="size-4" />
                            <span>Delete</span>
                        </button>
                    </div>
                @else
                    <x-ui.searchbox-dropdown placeholder="Search delivery orders..." wireModel="search">
                        <div class="flex flex-col gap-4 p-3 md:flex-row">
                            <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:pr-3 dark:border-zinc-700">
                                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="funnel" class="size-3.5" />
                                    <span>Filters</span>
                                </div>
                                <div class="space-y-1">
                                    <button type="button" wire:click="$set('status', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>All Status</span>
                                        @if(empty($status))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'pending')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-zinc-500"></span>
                                            <span>Pending</span>
                                        </div>
                                        @if($status === 'pending')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'picked')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                            <span>Picked</span>
                                        </div>
                                        @if($status === 'picked')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'in_transit')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
                                            <span>In Transit</span>
                                        </div>
                                        @if($status === 'in_transit')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'delivered')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Delivered</span>
                                        </div>
                                        @if($status === 'delivered')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'failed')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                            <span>Failed</span>
                                        </div>
                                        @if($status === 'failed')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'returned')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            <span>Returned</span>
                                        </div>
                                        @if($status === 'returned')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                </div>
                            </div>

                            <div class="flex-1 md:pl-3">
                                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="arrows-up-down" class="size-3.5" />
                                    <span>Sort By</span>
                                </div>
                                <div class="space-y-1">
                                    <button type="button" wire:click="$set('sort', 'latest')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>Latest</span>
                                        @if($sort === 'latest')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('sort', 'oldest')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>Oldest</span>
                                        @if($sort === 'oldest')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('sort', 'delivery_date')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>Delivery Date</span>
                                        @if($sort === 'delivery_date')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                </div>
                            </div>
                        </div>
                    </x-ui.searchbox-dropdown>

                    @if($search || $status || $sort !== 'latest')
                        <button wire:click="clearFilters" class="ml-3 text-sm font-light text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                            Clear filters
                        </button>
                    @endif
                @endif
            </div>

            {{-- Right Group: View Toggle + Pagination --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $deliveries->firstItem() ?? 0 }}-{{ $deliveries->lastItem() ?? 0 }}/{{ $deliveries->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button 
                            type="button"
                            wire:click="goToPreviousPage"
                            @disabled($deliveries->onFirstPage())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button 
                            type="button"
                            wire:click="goToNextPage"
                            @disabled(!$deliveries->hasMorePages())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                <x-ui.view-toggle :view="$view" :views="['list', 'grid']" />
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div>
        @if($view === 'list')
            <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                        <tr>
                            <th scope="col" class="w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                                <input 
                                    type="checkbox" 
                                    wire:model.live="selectAll"
                                    class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600"
                                >
                            </th>
                            @if($visibleColumns['delivery_number'])
                                <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Delivery</th>
                            @endif
                            @if($visibleColumns['sales_order'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Sales Order</th>
                            @endif
                            @if($visibleColumns['recipient'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Recipient</th>
                            @endif
                            @if($visibleColumns['courier'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Courier</th>
                            @endif
                            @if($visibleColumns['delivery_date'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Delivery Date</th>
                            @endif
                            @if($visibleColumns['status'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                            @endif
                            <th scope="col" class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8">
                                <flux:dropdown position="bottom" align="end">
                                    <button class="flex items-center justify-center rounded p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                        <flux:icon name="adjustments-horizontal" class="size-4" />
                                    </button>

                                    <flux:menu class="w-48">
                                        <div class="px-2 py-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">Toggle Columns</div>
                                        <flux:menu.separator />
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.delivery_number" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Delivery</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.sales_order" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Sales Order</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.recipient" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Recipient</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.courier" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Courier</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.delivery_date" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Delivery Date</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.status" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Status</span>
                                        </label>
                                    </flux:menu>
                                </flux:dropdown>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse ($deliveries as $delivery)
                            <tr 
                                onclick="window.location.href='{{ route('delivery.orders.edit', $delivery->id) }}'"
                                class="cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            >
                                <td class="py-4 pl-4 pr-1 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selected"
                                        value="{{ $delivery->id }}"
                                        class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600"
                                    >
                                </td>
                                @if($visibleColumns['delivery_number'])
                                    <td class="py-4 pl-2 pr-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $delivery->delivery_number }}</span>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $delivery->tracking_number ?: '—' }}</span>
                                        </div>
                                    </td>
                                @endif
                                @if($visibleColumns['sales_order'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $delivery->salesOrder?->order_number ?? '-' }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['recipient'])
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $delivery->recipient_name ?? $delivery->salesOrder?->customer?->name ?? '-' }}</span>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $delivery->recipient_phone ?: '—' }}</span>
                                        </div>
                                    </td>
                                @endif
                                @if($visibleColumns['courier'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $delivery->courier ?: '—' }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['delivery_date'])
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $delivery->delivery_date?->format('M d, Y') ?? '-' }}</span>
                                            @if($delivery->actual_delivery_date)
                                                <span class="text-xs text-emerald-600 dark:text-emerald-400">Delivered {{ $delivery->actual_delivery_date->format('M d') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                                @if($visibleColumns['status'])
                                    <td class="px-4 py-4">
                                        @php
                                            $statusConfig = match($delivery->status) {
                                                'pending' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => 'Pending'],
                                                'picked' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400', 'label' => 'Picked'],
                                                'in_transit' => ['bg' => 'bg-violet-100 dark:bg-violet-900/30', 'text' => 'text-violet-700 dark:text-violet-400', 'label' => 'In Transit'],
                                                'delivered' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'label' => 'Delivered'],
                                                'failed' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'label' => 'Failed'],
                                                'returned' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'label' => 'Returned'],
                                                default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => ucfirst($delivery->status)],
                                            };
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                            {{ $statusConfig['label'] }}
                                        </span>
                                    </td>
                                @endif
                                <td class="py-4 pr-4 sm:pr-6 lg:pr-8"></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                            <svg class="size-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No delivery orders found</p>
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
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @forelse($deliveries as $delivery)
                    @php
                        $statusConfig = match($delivery->status) {
                            'pending' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => 'Pending'],
                            'picked' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400', 'label' => 'Picked'],
                            'in_transit' => ['bg' => 'bg-violet-100 dark:bg-violet-900/30', 'text' => 'text-violet-700 dark:text-violet-400', 'label' => 'In Transit'],
                            'delivered' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'label' => 'Delivered'],
                            'failed' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'label' => 'Failed'],
                            'returned' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'label' => 'Returned'],
                            default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => ucfirst($delivery->status)],
                        };
                    @endphp
                    <a 
                        href="{{ route('delivery.orders.edit', $delivery->id) }}"
                        wire:navigate
                        class="group rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-zinc-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">{{ $delivery->delivery_number }}</p>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                {{ $statusConfig['label'] }}
                            </span>
                        </div>

                        <div class="mt-3 space-y-1">
                            <p class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $delivery->recipient_name ?? $delivery->salesOrder?->customer?->name ?? '-' }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $delivery->courier ?: '—' }}</p>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                            <div class="flex items-center gap-1">
                                <flux:icon name="calendar" class="size-4" />
                                <span>{{ $delivery->delivery_date?->format('d M Y') ?? '-' }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <flux:icon name="truck" class="size-4" />
                                <span>{{ $delivery->tracking_number ?: 'No tracking' }}</span>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-zinc-800">
                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $delivery->salesOrder?->order_number ?? '-' }}</p>
                            <div class="flex items-center gap-2 text-xs text-zinc-400 transition-colors group-hover:text-zinc-600 dark:text-zinc-500 dark:group-hover:text-zinc-300">
                                View details
                                <flux:icon name="arrow-up-right" class="size-4" />
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-zinc-200 p-10 text-center dark:border-zinc-800">
                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">No delivery orders to display</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Adjust your filters or create a new delivery order.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
