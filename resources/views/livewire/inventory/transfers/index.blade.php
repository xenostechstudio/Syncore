<div>
    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">
                {{ session('success') }}
            </x-ui.alert>
        @endif
    </div>

    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            {{-- Left Group: New Button, Title, Gear --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.transfers.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Transfers</span>
                
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
                        <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-zinc-100 px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-200 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-600">
                            <flux:icon name="x-mark" class="size-4" />
                            <span>{{ count($selected) }} Selected</span>
                        </button>

                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <flux:icon name="printer" class="size-4" />
                            <span>Print</span>
                        </button>

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
                                <button type="button" wire:click="deleteSelected" wire:confirm="Are you sure you want to delete selected transfers?" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <flux:icon name="trash" class="size-4" />
                                    <span>Delete</span>
                                </button>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @else
                    {{-- Search Input with Arrow Down Dropdown --}}
                    <div class="relative flex h-9 w-[480px] items-center overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search..." 
                            class="h-full w-full border-0 bg-transparent pl-9 pr-10 text-sm outline-none focus:ring-0" 
                        />
                        {{-- Separator + Dropdown Button --}}
                        <flux:dropdown position="bottom" align="end">
                            <button class="absolute right-0 top-0 flex h-full items-center border-l border-zinc-200 px-2.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:border-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-300">
                                <flux:icon name="chevron-down" class="size-4" />
                            </button>

                            {{-- Horizontal Dropdown Content --}}
                            <flux:menu class="w-[480px]">
                                <div class="flex divide-x divide-zinc-200 dark:divide-zinc-700">
                                    {{-- Filters Section --}}
                                    <div class="flex-1 p-3">
                                        <div class="mb-2 flex items-center justify-between">
                                            <div class="flex items-center gap-1.5">
                                                <flux:icon name="funnel" class="size-4 text-zinc-400" />
                                                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Filters</span>
                                            </div>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <input type="radio" wire:model.live="status" value="" name="status_filter" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>All Transfers</span>
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <input type="radio" wire:model.live="status" value="draft" name="status_filter" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>Draft</span>
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <input type="radio" wire:model.live="status" value="pending" name="status_filter" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>Pending</span>
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <input type="radio" wire:model.live="status" value="in_transit" name="status_filter" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>In Transit</span>
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <input type="radio" wire:model.live="status" value="completed" name="status_filter" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>Completed</span>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Warehouse Filter Section --}}
                                    <div class="flex-1 p-3">
                                        <div class="mb-2 flex items-center justify-between">
                                            <div class="flex items-center gap-1.5">
                                                <flux:icon name="building-storefront" class="size-4 text-zinc-400" />
                                                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Warehouse</span>
                                            </div>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <input type="radio" wire:model.live="sourceWarehouse" value="" name="source_filter" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span>All Sources</span>
                                            </label>
                                            @foreach($warehouses as $warehouse)
                                                <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <input type="radio" wire:model.live="sourceWarehouse" value="{{ $warehouse->id }}" name="source_filter" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                    <span>{{ $warehouse->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @endif
            </div>

            {{-- Right Group: View Toggle, Pagination --}}
            <div class="flex items-center gap-3">
                <x-ui.view-toggle :view="$view" />
                
                @if($transfers->hasPages())
                    <div class="flex items-center gap-1">
                        <button 
                            wire:click="previousPage" 
                            @disabled($transfers->onFirstPage())
                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 transition-colors hover:border-zinc-300 hover:text-zinc-900 disabled:opacity-50 disabled:cursor-not-allowed dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-100"
                        >
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button 
                            wire:click="nextPage" 
                            @disabled(!$transfers->hasMorePages())
                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 transition-colors hover:border-zinc-300 hover:text-zinc-900 disabled:opacity-50 disabled:cursor-not-allowed dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-100"
                        >
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </x-slot:header>

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
                            @if($visibleColumns['transfer'])
                                <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Transfer</th>
                            @endif
                            @if($visibleColumns['source'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Source</th>
                            @endif
                            @if($visibleColumns['destination'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Destination</th>
                            @endif
                            @if($visibleColumns['date'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Date</th>
                            @endif
                            @if($visibleColumns['items'])
                                <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Items</th>
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
                                            <input type="checkbox" wire:model.live="visibleColumns.transfer" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Transfer</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.source" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Source</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.destination" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Destination</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.date" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Date</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.items" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Items</span>
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
                        @forelse ($transfers as $transfer)
                            <tr 
                                onclick="window.location.href='{{ route('inventory.transfers.edit', $transfer->id) }}'"
                                class="cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            >
                                <td class="py-4 pl-4 pr-1 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selected"
                                        value="{{ $transfer->id }}"
                                        class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600"
                                    >
                                </td>
                                @if($visibleColumns['transfer'])
                                    <td class="py-4 pl-2 pr-4">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $transfer->transfer_number }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['source'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $transfer->sourceWarehouse->name ?? '-' }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['destination'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $transfer->destinationWarehouse->name ?? '-' }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['date'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $transfer->transfer_date->format('M d, Y') }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['items'])
                                    <td class="px-4 py-4 text-center">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $transfer->items->count() }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['status'])
                                    <td class="px-4 py-4">
                                        @php
                                            $statusConfig = match($transfer->status) {
                                                'draft' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => 'Draft'],
                                                'pending' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'label' => 'Pending'],
                                                'in_transit' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400', 'label' => 'In Transit'],
                                                'completed' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'label' => 'Completed'],
                                                'cancelled' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'label' => 'Cancelled'],
                                                default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => ucfirst($transfer->status)],
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
                                            <flux:icon name="arrows-right-left" class="size-6 text-zinc-400" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No transfers found</p>
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
            {{-- Grid/Kanban View --}}
            @php
                $statuses = [
                    'draft' => ['label' => 'Draft', 'color' => 'zinc', 'headerBg' => 'bg-zinc-100 dark:bg-zinc-800'],
                    'pending' => ['label' => 'Pending', 'color' => 'amber', 'headerBg' => 'bg-amber-50 dark:bg-amber-900/20'],
                    'in_transit' => ['label' => 'In Transit', 'color' => 'blue', 'headerBg' => 'bg-blue-50 dark:bg-blue-900/20'],
                    'completed' => ['label' => 'Completed', 'color' => 'emerald', 'headerBg' => 'bg-emerald-50 dark:bg-emerald-900/20'],
                    'cancelled' => ['label' => 'Cancelled', 'color' => 'red', 'headerBg' => 'bg-red-50 dark:bg-red-900/20'],
                ];
                $transfersByStatus = $transfers->groupBy('status');
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
                                    {{ $transfersByStatus->get($statusKey)?->count() ?? 0 }}
                                </span>
                            </div>
                        </div>

                        {{-- Column Cards --}}
                        <div class="flex flex-1 flex-col gap-2 p-2">
                            @forelse($transfersByStatus->get($statusKey, collect()) as $transfer)
                                <a 
                                    href="{{ route('inventory.transfers.edit', $transfer->id) }}"
                                    wire:navigate
                                    class="group rounded-lg border border-zinc-200 bg-white p-3 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                >
                                    <div class="mb-2 flex items-start justify-between">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $transfer->transfer_number }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $transfer->transfer_date->format('M d') }}</span>
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $transfer->sourceWarehouse->name ?? '-' }} → {{ $transfer->destinationWarehouse->name ?? '-' }}
                                    </div>
                                    <div class="mt-2 text-xs text-zinc-400">{{ $transfer->items->count() }} items</div>
                                </a>
                            @empty
                                <div class="py-4 text-center text-xs text-zinc-400">No transfers</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
