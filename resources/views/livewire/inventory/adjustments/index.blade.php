<div>
    <x-ui.flash />

    @php
        $isIn = request()->routeIs('inventory.warehouse-in.*');
        $isOut = request()->routeIs('inventory.warehouse-out.*');
        $createRouteName = $isIn ? 'inventory.warehouse-in.create' : ($isOut ? 'inventory.warehouse-out.create' : 'inventory.adjustments.create');
        $headerTitle = $isIn ? 'Inbound' : ($isOut ? 'Outbound' : 'Stock Adjustment');
    @endphp

    <x-slot:header>
        <x-ui.index-header
            :bare="true"
            :title="$headerTitle"
            :createRoute="route($createRouteName)"
            :paginator="$adjustments"
            :selected="$selected"
            :views="['list', 'grid']"
            :view="$view"
            searchPlaceholder="Search..."
        >
            <x-slot:actions>
                <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                    <flux:icon name="arrow-down-tray" class="size-4" />
                    <span>Import records</span>
                </button>
                <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                    <flux:icon name="arrow-up-tray" class="size-4" />
                    <span>Export All</span>
                </button>
            </x-slot:actions>

            <x-slot:search>
                <x-ui.searchbox-dropdown
                    placeholder="Search adjustments..."
                    widthClass="{{ ($isIn || $isOut) ? 'w-[360px]' : 'w-[480px]' }}"
                    width="{{ ($isIn || $isOut) ? '360px' : '480px' }}"
                    :activeFilterCount="$this->getActiveFilterCount()"
                    clearAction="clearFilters"
                >
                    <div class="flex flex-col gap-4 p-3 md:flex-row">
                        <div class="flex-1 {{ ($isIn || $isOut) ? '' : 'border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:pr-3 dark:border-zinc-700' }}">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                <flux:icon name="funnel" class="size-3.5" />
                                <span>Status</span>
                            </div>
                            <div class="space-y-1">
                                @foreach([
                                    '' => 'All Adjustments',
                                    'draft' => 'Draft',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled',
                                ] as $value => $label)
                                    <button type="button" wire:click="$set('status', '{{ $value }}')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>{{ $label }}</span>
                                        @if((string) $status === (string) $value)<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        @if(! $isIn && ! $isOut)
                            <div class="flex-1 md:pl-3">
                                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="adjustments-vertical" class="size-3.5" />
                                    <span>Type</span>
                                </div>
                                <div class="space-y-1">
                                    @foreach([
                                        '' => 'All Types',
                                        'increase' => 'Increase',
                                        'decrease' => 'Decrease',
                                        'count' => 'Count',
                                    ] as $value => $label)
                                        <button type="button" wire:click="$set('adjustmentType', '{{ $value }}')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <span>{{ $label }}</span>
                                            @if((string) $adjustmentType === (string) $value)<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </x-ui.searchbox-dropdown>
            </x-slot:search>

            <x-slot:selectionActions>
                <button wire:click="exportSelected" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                    <flux:icon name="arrow-down-tray" class="size-4" />
                    <span>Export</span>
                </button>

                <flux:dropdown position="bottom" align="center">
                    <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-2 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                        <flux:icon name="ellipsis-horizontal" class="size-4" />
                    </button>

                    <flux:menu class="w-56">
                        <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            <flux:icon name="document-duplicate" class="size-4" />
                            <span>Duplicate</span>
                        </button>
                        <flux:menu.separator />
                        <button type="button" wire:click="confirmBulkDelete" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                            <flux:icon name="trash" class="size-4" />
                            <span>Delete</span>
                        </button>
                    </flux:menu>
                </flux:dropdown>
            </x-slot:selectionActions>
        </x-ui.index-header>
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
                            @if($visibleColumns['adjustment'])
                                <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Adjustment</th>
                            @endif
                            @if($visibleColumns['warehouse'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Warehouse</th>
                            @endif
                            @if($visibleColumns['type'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Type</th>
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
                                            <input type="checkbox" wire:model.live="visibleColumns.adjustment" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Adjustment</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.warehouse" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Warehouse</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.type" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Type</span>
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
                        @forelse ($adjustments as $adjustment)
                            @php $isSelected = in_array($adjustment->id, $selected); @endphp
                            <tr 
                                onclick="window.location.href='{{ route('inventory.adjustments.edit', $adjustment->id) }}'"
                                class="group cursor-pointer transition-all duration-150 {{ $isSelected 
                                    ? 'bg-zinc-900/[0.03] dark:bg-zinc-100/[0.03]' 
                                    : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}"
                            >
                                <td class="relative py-4 pl-4 pr-1 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                    <div class="absolute inset-y-0 left-0 w-0.5 transition-all duration-150 {{ $isSelected ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-transparent group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700' }}"></div>
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selected"
                                        value="{{ $adjustment->id }}"
                                        class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600 {{ $isSelected ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-100/20' : '' }}"
                                    >
                                </td>
                                @if($visibleColumns['adjustment'])
                                    <td class="py-4 pl-2 pr-4">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $adjustment->adjustment_number }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['warehouse'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $adjustment->warehouse->name ?? '-' }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['type'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ ucfirst($adjustment->adjustment_type) }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['date'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $adjustment->adjustment_date->format('M d, Y') }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['items'])
                                    <td class="px-4 py-4 text-center">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $adjustment->items->count() }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['status'])
                                    <td class="px-4 py-4">
                                        <x-ui.status-badge :status="$adjustment->state" class="px-2.5 py-0.5" />
                                    </td>
                                @endif
                                <td class="py-4 pr-4 sm:pr-6 lg:pr-8"></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                            <flux:icon name="clipboard-document-check" class="size-6 text-zinc-400" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No adjustments found</p>
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
                    'approved' => ['label' => 'Approved', 'color' => 'blue', 'headerBg' => 'bg-blue-50 dark:bg-blue-900/20'],
                    'completed' => ['label' => 'Completed', 'color' => 'emerald', 'headerBg' => 'bg-emerald-50 dark:bg-emerald-900/20'],
                    'cancelled' => ['label' => 'Cancelled', 'color' => 'red', 'headerBg' => 'bg-red-50 dark:bg-red-900/20'],
                ];
                $adjustmentsByStatus = $adjustments->groupBy('status');
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
                                    {{ $adjustmentsByStatus->get($statusKey)?->count() ?? 0 }}
                                </span>
                            </div>
                        </div>

                        {{-- Column Cards --}}
                        <div class="flex flex-1 flex-col gap-2 p-2">
                            @forelse($adjustmentsByStatus->get($statusKey, collect()) as $adjustment)
                                <a 
                                    href="{{ route('inventory.adjustments.edit', $adjustment->id) }}"
                                    wire:navigate
                                    class="group rounded-lg border border-zinc-200 bg-white p-3 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                >
                                    <div class="mb-2 flex items-start justify-between">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $adjustment->adjustment_number }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $adjustment->adjustment_date->format('M d') }}</span>
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $adjustment->warehouse->name ?? '-' }} · {{ ucfirst($adjustment->adjustment_type) }}
                                    </div>
                                    <div class="mt-2 text-xs text-zinc-400">{{ $adjustment->items->count() }} items</div>
                                </a>
                            @empty
                                <div class="py-4 text-center text-xs text-zinc-400">No adjustments</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @isset($showDeleteConfirm)
        <x-ui.delete-confirm-modal 
            wire:model="showDeleteConfirm"
            :validation="$deleteValidation ?? []"
            title="Confirm Delete"
            itemLabel="adjustments"
        />
    @endisset
</div>
