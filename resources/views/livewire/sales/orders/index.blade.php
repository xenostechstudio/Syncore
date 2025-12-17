<div>
    {{-- Flash Messages --}}
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

        @if(session('warning'))
            <x-ui.alert type="warning" :duration="6000">
                {{ session('warning') }}
            </x-ui.alert>
        @endif

        @if(session('info'))
            <x-ui.alert type="info" :duration="5000">
                {{ session('info') }}
            </x-ui.alert>
        @endif
    </div>

    @php
        $isOrdersToInvoicePage = request()->routeIs('sales.invoices.pending');
    @endphp

    {{-- Header Bar (inside Livewire root div so wire:click works) --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group: Title, Gear (no New button for Orders to Invoice) --}}
            <div class="flex items-center gap-3">
                @unless($isOrdersToInvoicePage)
                    <a href="{{ route('sales.orders.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        New
                    </a>
                @endunless
                <span class="text-md font-ligth text-zinc-600 dark:text-zinc-400">
                    @if($isOrdersToInvoicePage)
                        Orders to Invoice
                    @else
                        {{ $mode === 'orders' ? 'Orders' : 'Quotations' }}
                    @endif
                </span>
                
                {{-- Actions Menu (Gear) --}}
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
                    {{-- Selection Toolbar --}}
                    <div class="flex items-center gap-2 animate-in fade-in slide-in-from-top-2 duration-200">
                        {{-- Count Selected Button --}}
                        <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-zinc-100 px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-200 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-600">
                            <flux:icon name="x-mark" class="size-4" />
                            <span>{{ count($selected) }} Selected</span>
                        </button>

                        {{-- Create Invoice --}}
                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            Create Invoice
                        </button>

                        {{-- Print --}}
                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <flux:icon name="printer" class="size-4" />
                            <span>Print</span>
                        </button>

                        {{-- Actions Dropdown --}}
                        <flux:dropdown position="bottom" align="center">
                            <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                <flux:icon name="cog-6-tooth" class="size-4" />
                                <span>Actions</span>
                                <flux:icon name="chevron-down" class="size-3" />
                            </button>

                            <flux:menu class="w-56">
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="arrow-up-tray" class="size-4" />
                                    <span>Export</span>
                                </button>
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="document-duplicate" class="size-4" />
                                    <span>Duplicate</span>
                                </button>
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <flux:icon name="trash" class="size-4" />
                                    <span>Delete</span>
                                </button>
                                <flux:menu.separator />
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="x-circle" class="size-4" />
                                    <span>Cancel</span>
                                </button>
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="document-text" class="size-4" />
                                    <span>Create Invoice(s)</span>
                                </button>
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="envelope" class="size-4" />
                                    <span>Send an Email</span>
                                </button>
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="check-circle" class="size-4" />
                                    <span>Mark Quotation as Sent</span>
                                </button>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @else
                    {{-- Search Input with Arrow Down Dropdown --}}
                    <x-ui.searchbox-dropdown placeholder="Search orders...">
                        <div class="flex flex-col gap-4 p-3 md:flex-row">
                                {{-- Filters column --}}
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
                                        <button type="button" wire:click="$set('status', 'draft')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <div class="flex items-center gap-2">
                                                <span class="h-1.5 w-1.5 rounded-full bg-zinc-500"></span>
                                                <span>Quotation</span>
                                            </div>
                                            @if($status === 'draft')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                        <button type="button" wire:click="$set('status', 'confirmed')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <div class="flex items-center gap-2">
                                                <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                                <span>Quotation Sent</span>
                                            </div>
                                            @if($status === 'confirmed')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                        <button type="button" wire:click="$set('status', 'processing')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <div class="flex items-center gap-2">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                <span>Sales Order</span>
                                            </div>
                                            @if($status === 'processing')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                        <button type="button" wire:click="$set('status', 'shipped')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <div class="flex items-center gap-2">
                                                <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
                                                <span>Shipped</span>
                                            </div>
                                            @if($status === 'shipped')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                        <button type="button" wire:click="$set('status', 'delivered')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <div class="flex items-center gap-2">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                <span>Done</span>
                                            </div>
                                            @if($status === 'delivered')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                        <button type="button" wire:click="$set('status', 'cancelled')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <div class="flex items-center gap-2">
                                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                                <span>Cancelled</span>
                                            </div>
                                            @if($status === 'cancelled')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                    </div>
                                </div>

                                {{-- Sort column --}}
                                <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:px-3 dark:border-zinc-700">
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
                                        <button type="button" wire:click="$set('sort', 'total_high')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <span>Total: High to Low</span>
                                            @if($sort === 'total_high')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                        <button type="button" wire:click="$set('sort', 'total_low')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <span>Total: Low to High</span>
                                            @if($sort === 'total_low')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                    </div>
                                </div>

                                {{-- Group column --}}
                                <div class="flex-1 md:pl-3">
                                    <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                        <flux:icon name="rectangle-group" class="size-3.5" />
                                        <span>Group By</span>
                                    </div>
                                    <div class="space-y-1">
                                        <button type="button" wire:click="$set('groupBy', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <span>None</span>
                                            @if(empty($groupBy))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                        <button type="button" wire:click="$set('groupBy', 'salesperson')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <span>Salesperson</span>
                                            @if($groupBy === 'salesperson')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                        <button type="button" wire:click="$set('groupBy', 'customer')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <span>Customer</span>
                                            @if($groupBy === 'customer')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                        <button type="button" wire:click="$set('groupBy', 'date')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <span>Order Date</span>
                                            @if($groupBy === 'date')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                        <button type="button" wire:click="$set('groupBy', 'status')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <span>Status</span>
                                            @if($groupBy === 'status')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                    </div>
                                </div>
                            </div>
                    </x-ui.searchbox-dropdown>
                @endif
            </div>

            {{-- Right Group: Pagination Info + View Toggle --}}
            <div class="flex items-center gap-3">
                {{-- Pagination Info & Navigation --}}
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $orders->firstItem() ?? 0 }}-{{ $orders->lastItem() ?? 0 }}/{{ $orders->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button 
                            type="button"
                            wire:click="goToPreviousPage"
                            @disabled($orders->onFirstPage())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button 
                            type="button"
                            wire:click="goToNextPage"
                            @disabled(!$orders->hasMorePages())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                {{-- View Toggle --}}
                <x-ui.view-toggle :view="$view" :views="['list', 'grid', 'kanban']" />
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
                            @if($visibleColumns['order'])
                                <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Order</th>
                            @endif
                            @if($visibleColumns['customer'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Customer</th>
                            @endif
                            @if($visibleColumns['salesperson'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Salesperson</th>
                            @endif
                            @if($visibleColumns['date'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Date</th>
                            @endif
                            @if($visibleColumns['total'])
                                <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total</th>
                            @endif
                            @if($visibleColumns['status'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                            @endif
                            <th scope="col" class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8">
                                {{-- Column Config --}}
                                <flux:dropdown position="bottom" align="end">
                                    <button class="flex items-center justify-center rounded p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                        <flux:icon name="adjustments-horizontal" class="size-4" />
                                    </button>

                                    <flux:menu class="w-48">
                                        <div class="px-2 py-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">Toggle Columns</div>
                                        <flux:menu.separator />
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.order" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Order</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.customer" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Customer</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.salesperson" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Salesperson</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.date" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Date</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.total" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Total</span>
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
                        @forelse ($orders as $order)
                            <tr 
                                onclick="window.location.href='{{ route('sales.orders.edit', $order->id) }}'"
                                class="cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            >
                                <td class="py-4 pl-4 pr-1 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selected"
                                        value="{{ $order->id }}"
                                        class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600"
                                    >
                                </td>
                                @if($visibleColumns['order'])
                                    <td class="py-4 pl-2 pr-4">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $order->order_number }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['customer'])
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $order->customer->name }}</span>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $order->customer->city }}</span>
                                        </div>
                                    </td>
                                @endif
                                @if($visibleColumns['salesperson'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $order->user->name ?? '-' }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['date'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $order->order_date->format('M d, Y') }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['total'])
                                    <td class="px-4 py-4 text-right">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['status'])
                                    <td class="px-4 py-4">
                                        @php
                                            $statusConfig = match($order->status) {
                                                'draft' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => 'Quotation'],
                                                'confirmed' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400', 'label' => 'Quotation Sent'],
                                                'processing' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'label' => 'Sales Order'],
                                                'shipped' => ['bg' => 'bg-violet-100 dark:bg-violet-900/30', 'text' => 'text-violet-700 dark:text-violet-400', 'label' => 'Shipped'],
                                                'delivered' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'label' => 'Done'],
                                                'cancelled' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'label' => 'Cancelled'],
                                                default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => ucfirst($order->status)],
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
                    @php
                        $visibleCount = 1 + ($visibleColumns['order'] ? 1 : 0) + ($visibleColumns['customer'] ? 1 : 0) + ($visibleColumns['salesperson'] ? 1 : 0) + ($visibleColumns['date'] ? 1 : 0);
                        $afterTotalCount = 1 + ($visibleColumns['status'] ? 1 : 0);
                    @endphp
                    <tfoot class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                        <tr>
                            <td colspan="{{ $visibleCount }}" class="py-3 pl-4 pr-4 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pl-6 lg:pl-8 dark:text-zinc-400">Total</td>
                            @if($visibleColumns['total'])
                                <td class="px-4 py-3 text-right text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                    Rp {{ number_format($orders->sum('total'), 0, ',', '.') }}
                                </td>
                            @endif
                            <td colspan="{{ $afterTotalCount }}"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @elseif($view === 'grid')
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @forelse($orders as $order)
                    @php
                        $statusConfig = match($order->status) {
                            'draft' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => 'Quotation'],
                            'confirmed' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400', 'label' => 'Quotation Sent'],
                            'processing' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'label' => 'Sales Order'],
                            'shipped' => ['bg' => 'bg-violet-100 dark:bg-violet-900/30', 'text' => 'text-violet-700 dark:text-violet-400', 'label' => 'Shipped'],
                            'delivered' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'label' => 'Done'],
                            'cancelled' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'label' => 'Cancelled'],
                            default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => ucfirst($order->status)],
                        };
                    @endphp
                    <a 
                        href="{{ route('sales.orders.edit', $order->id) }}"
                        wire:navigate
                        class="group rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-zinc-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">{{ $order->order_number }}</p>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                {{ $statusConfig['label'] }}
                            </span>
                        </div>
                        <div class="mt-3 space-y-1">
                            <p class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $order->customer->name }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $order->customer->city ?? '—' }}</p>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                            <div class="flex items-center gap-1">
                                <flux:icon name="calendar" class="size-4" />
                                <span>{{ $order->order_date->format('d M Y') }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <flux:icon name="user" class="size-4" />
                                <span>{{ $order->user->name ?? 'Unassigned' }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <flux:icon name="cube" class="size-4" />
                                <span>{{ $order->items_count ?? $order->items()->count() }} items</span>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-zinc-800">
                            <p class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                            <div class="flex items-center gap-2 text-xs text-zinc-400 transition-colors group-hover:text-zinc-600 dark:text-zinc-500 dark:group-hover:text-zinc-300">
                                View details
                                <flux:icon name="arrow-up-right" class="size-4" />
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-zinc-200 p-10 text-center dark:border-zinc-800">
                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">No orders to display</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Adjust your filters or create a new order.</p>
                    </div>
                @endforelse
            </div>
        @elseif($view === 'kanban')
            {{-- Grid/Thumbnail View (Kanban Board) --}}
            @php
                $statuses = [
                    'draft' => ['label' => 'Quotation', 'color' => 'zinc', 'headerBg' => 'bg-zinc-100 dark:bg-zinc-800'],
                    'confirmed' => ['label' => 'Quotation Sent', 'color' => 'blue', 'headerBg' => 'bg-blue-50 dark:bg-blue-900/20'],
                    'processing' => ['label' => 'Sales Order', 'color' => 'amber', 'headerBg' => 'bg-amber-50 dark:bg-amber-900/20'],
                    'shipped' => ['label' => 'Shipped', 'color' => 'violet', 'headerBg' => 'bg-violet-50 dark:bg-violet-900/20'],
                    'delivered' => ['label' => 'Done', 'color' => 'emerald', 'headerBg' => 'bg-emerald-50 dark:bg-emerald-900/20'],
                    'cancelled' => ['label' => 'Cancelled', 'color' => 'red', 'headerBg' => 'bg-red-50 dark:bg-red-900/20'],
                ];
                $ordersByStatus = $orders->groupBy('status');
            @endphp
            <div class="flex gap-4 overflow-x-auto pb-4">
                @foreach($statuses as $statusKey => $statusInfo)
                    <div class="flex w-72 flex-shrink-0 flex-col rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/50">
                        {{-- Column Header --}}
                        <div class="flex items-center justify-between rounded-t-lg {{ $statusInfo['headerBg'] }} px-3 py-2.5">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-{{ $statusInfo['color'] }}-500"></span>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $statusInfo['label'] }}</span>
                                <span class="rounded-full bg-white px-1.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                    {{ $ordersByStatus->get($statusKey)?->count() ?? 0 }}
                                </span>
                            </div>
                        </div>

                        {{-- Column Cards --}}
                        <div class="flex flex-1 flex-col gap-2 p-2">
                            @forelse($ordersByStatus->get($statusKey, collect()) as $order)
                                <a 
                                    href="{{ route('sales.orders.edit', $order->id) }}"
                                    wire:navigate
                                    class="group rounded-lg border border-zinc-200 bg-white p-3 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                >
                                    <div class="mb-2 flex items-start justify-between">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $order->order_number }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $order->order_date->format('M d') }}</span>
                                    </div>
                                    <div class="mb-3 flex items-center gap-2">
                                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-700">
                                            <svg class="size-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                            </svg>
                                        </div>
                                        <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ $order->customer->name }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                                        @if($order->user)
                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-{{ $statusInfo['color'] }}-100 text-xs font-medium text-{{ $statusInfo['color'] }}-700 dark:bg-{{ $statusInfo['color'] }}-900/30 dark:text-{{ $statusInfo['color'] }}-400">
                                                {{ strtoupper(substr($order->user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="flex flex-1 items-center justify-center py-8">
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500">No orders</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Column Footer --}}
                        @if($ordersByStatus->get($statusKey)?->count() > 0)
                            <div class="border-t border-zinc-200 px-3 py-2 dark:border-zinc-700">
                                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                    Total: Rp {{ number_format($ordersByStatus->get($statusKey)->sum('total'), 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
