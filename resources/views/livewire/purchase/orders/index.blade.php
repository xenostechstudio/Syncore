<div>
    <x-ui.flash />    <x-ui.index-header
        title="Purchase Orders"
        :createRoute="route('purchase.orders.create')"
        :paginator="$orders"
        :view="$view"
        :views="['list']"
    >
        <x-slot:actions>
            <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                        <flux:icon name="arrow-down-tray" class="size-4" />
                                        <span>Import records</span>
                                    </button>
                                    <a href="{{ route('export.purchase-orders') }}" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                        <flux:icon name="arrow-up-tray" class="size-4" />
                                        <span>Export All</span>
                                    </a>
        </x-slot:actions>

        <x-slot:search>

                            @if(count($selected) > 0)
                                {{-- Selection Toolbar --}}
                                <x-ui.selection-toolbar :count="count($selected)">
                <button wire:click="exportSelected" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                            <flux:icon name="arrow-up-tray" class="size-4" />
                                            <span>Export</span>
                                        </button>
                
                                        <flux:dropdown position="bottom" align="center">
                                            <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                                <flux:icon name="ellipsis-horizontal" class="size-4" />
                                            </button>
                
                                            <flux:menu class="w-56">
                                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <flux:icon name="printer" class="size-4" />
                                                    <span>Print</span>
                                                </button>
                                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <flux:icon name="document-duplicate" class="size-4" />
                                                    <span>Duplicate</span>
                                                </button>
                                                <flux:menu.separator />
                                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <flux:icon name="check-circle" class="size-4" />
                                                    <span>Confirm Order</span>
                                                </button>
                                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <flux:icon name="envelope" class="size-4" />
                                                    <span>Send by Email</span>
                                                </button>
                                                <flux:menu.separator />
                                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                                    <flux:icon name="trash" class="size-4" />
                                                    <span>Delete</span>
                                                </button>
                                            </flux:menu>
                                        </flux:dropdown>
                                </x-ui.selection-toolbar>
                            @else
                                <x-ui.searchbox-dropdown
                                    placeholder="Search purchase orders..."
                                    widthClass="w-[480px]"
                                    width="480px"
                                    :activeFilterCount="$this->getActiveFilterCount()"
                                    clearAction="clearFilters"
                                >
                                    <div class="flex flex-col gap-4 p-3 md:flex-row">
                                        <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:pr-3 dark:border-zinc-700">
                                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                                <flux:icon name="funnel" class="size-3.5" />
                                                <span>Status</span>
                                            </div>
                                            <div class="space-y-1">
                                                @foreach([
                                                    '' => 'All Status',
                                                    'purchase_order' => 'To Receive',
                                                    'partially_received' => 'Partially Received',
                                                    'received' => 'Received',
                                                    'billed' => 'Billed',
                                                    'cancelled' => 'Cancelled',
                                                ] as $value => $label)
                                                    <button type="button" wire:click="$set('status', '{{ $value }}')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                        <span>{{ $label }}</span>
                                                        @if((string) $status === (string) $value)<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="flex-1 md:pl-3">
                                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                                <flux:icon name="arrows-up-down" class="size-3.5" />
                                                <span>Sort By</span>
                                            </div>
                                            <div class="space-y-1">
                                                @foreach([
                                                    'latest' => 'Latest',
                                                    'oldest' => 'Oldest',
                                                ] as $value => $label)
                                                    <button type="button" wire:click="$set('sort', '{{ $value }}')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                        <span>{{ $label }}</span>
                                                        @if($sort === $value)<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </x-ui.searchbox-dropdown>
                            @endif
        </x-slot:search>
    </x-ui.index-header>

    {{-- Content --}}
    <div>
        @if($viewType === 'list')
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
                            @if($visibleColumns['supplier'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Supplier</th>
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
                                            <input type="checkbox" wire:model.live="visibleColumns.supplier" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Supplier</span>
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
                            @php $isSelected = in_array($order->id, $selected); @endphp
                            <tr 
                                onclick="window.location.href='{{ route('purchase.orders.edit', $order->id) }}'"
                                class="group cursor-pointer transition-all duration-150 {{ $isSelected 
                                    ? 'bg-zinc-900/[0.03] dark:bg-zinc-100/[0.03]' 
                                    : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}"
                            >
                                <td class="relative py-4 pl-4 pr-1 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                    <div class="absolute inset-y-0 left-0 w-0.5 transition-all duration-150 {{ $isSelected ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-transparent group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700' }}"></div>
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selected"
                                        value="{{ $order->id }}"
                                        class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600 {{ $isSelected ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-100/20' : '' }}"
                                    >
                                </td>
                                @if($visibleColumns['order'])
                                    <td class="py-4 pl-2 pr-4">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $order->reference }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['supplier'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $order->supplier_name }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['date'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['total'])
                                    <td class="px-4 py-4 text-right">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">${{ number_format($order->total, 2) }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['status'])
                                    <td class="px-4 py-4">
                                        <x-ui.status-badge :status="$order->status" type="purchase_order" />
                                    </td>
                                @endif
                                <td class="py-4 pr-4 sm:pr-6 lg:pr-8"></td>
                            </tr>
                        @empty
                            @php
                                $emptyColspan = 2
                                    + ($visibleColumns['order'] ? 1 : 0)
                                    + ($visibleColumns['supplier'] ? 1 : 0)
                                    + ($visibleColumns['date'] ? 1 : 0)
                                    + ($visibleColumns['total'] ? 1 : 0)
                                    + ($visibleColumns['status'] ? 1 : 0);
                            @endphp
                            <tr>
                                <td colspan="{{ $emptyColspan }}" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                            <svg class="size-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No purchase orders found</p>
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
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse($orders as $order)
                    <a href="{{ route('purchase.orders.edit', $order->id) }}" wire:navigate class="relative block rounded-lg border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                        @php
                            // Driver-aware: rows here come from DB::table, not Eloquent, so derive the enum from the raw status.
                            $orderState = \App\Enums\PurchaseOrderState::tryFrom($order->status);
                            $dotClass = match($orderState?->color()) {
                                'blue'    => 'bg-blue-500',
                                'amber'   => 'bg-amber-500',
                                'emerald' => 'bg-emerald-500',
                                'violet'  => 'bg-violet-500',
                                'red'     => 'bg-red-500',
                                default   => 'bg-zinc-400',
                            };
                        @endphp
                        <span class="absolute right-2 top-2 inline-flex h-2 w-2 rounded-full {{ $dotClass }}"></span>
                        
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $order->reference }}</h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $order->supplier_name }}</p>
                            <div class="flex items-center justify-between pt-2">
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}</span>
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">${{ number_format($order->total, 2) }}</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-lg border border-zinc-200 bg-white px-5 py-12 text-center dark:border-zinc-800 dark:bg-zinc-900">
                        <svg class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <p class="mt-4 text-sm font-light text-zinc-500 dark:text-zinc-400">No purchase orders found</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
