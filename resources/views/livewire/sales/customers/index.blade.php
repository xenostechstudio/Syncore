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
    </div>

    {{-- Header Bar (inside Livewire root div so wire:click works) --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group: New Button, Title, Gear --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.customers.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Customers</span>
                
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
                        <a href="{{ route('export.customers') }}" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>Export All</span>
                        </a>
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Center Group: Search or Selection Toolbar --}}
            <div class="flex flex-1 items-center justify-center">
                @if(count($selected) > 0)
                    {{-- Selection Toolbar --}}
                    <div class="flex items-center gap-2 animate-in fade-in slide-in-from-top-2 duration-200">
                        {{-- Count Selected Button --}}
                        <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <span>{{ count($selected) }} selected</span>
                            <flux:icon name="x-mark" class="size-3.5" />
                        </button>

                        <div class="h-5 w-px bg-zinc-200 dark:bg-zinc-700"></div>

                        {{-- Export --}}
                        <button wire:click="exportSelected" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>Export</span>
                        </button>

                        {{-- Print --}}
                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <flux:icon name="printer" class="size-4" />
                            <span>Print</span>
                        </button>

                        {{-- Actions Dropdown --}}
                        <flux:dropdown position="bottom" align="center">
                            <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-2 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                <flux:icon name="ellipsis-horizontal" class="size-4" />
                            </button>

                            <flux:menu class="w-56">
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="document-duplicate" class="size-4" />
                                    <span>Duplicate</span>
                                </button>
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="document-text" class="size-4" />
                                    <span>Create Invoice(s)</span>
                                </button>
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="envelope" class="size-4" />
                                    <span>Send an Email</span>
                                </button>
                                <flux:menu.separator />
                                <button type="button" wire:click="confirmBulkDelete" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <flux:icon name="trash" class="size-4" />
                                    <span>Delete</span>
                                </button>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @else
                    <x-ui.searchbox-dropdown placeholder="Search customers..." widthClass="w-[520px]" width="520px">
                        <div class="flex flex-col gap-4 p-3 md:flex-row">
                            {{-- Filters Section --}}
                            <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:pr-3 dark:border-zinc-700">
                                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="funnel" class="size-3.5" />
                                    <span>Filters</span>
                                </div>
                                <div class="space-y-1">
                                    <label class="flex w-full cursor-pointer items-center justify-between gap-2 rounded px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" wire:model.live="filterActive" class="sr-only" />
                                            <span>Active Customers</span>
                                        </div>
                                        @if(!empty($filterActive))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </label>
                                    <label class="flex w-full cursor-pointer items-center justify-between gap-2 rounded px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" wire:model.live="filterInactive" class="sr-only" />
                                            <span>Inactive Customers</span>
                                        </div>
                                        @if(!empty($filterInactive))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </label>
                                    <label class="flex w-full cursor-pointer items-center justify-between gap-2 rounded px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" wire:model.live="filterWithOrders" class="sr-only" />
                                            <span>With Orders</span>
                                        </div>
        @if(!empty($filterWithOrders))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </label>
                                    <label class="flex w-full cursor-pointer items-center justify-between gap-2 rounded px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" wire:model.live="filterMine" class="sr-only" />
                                            <span>My Customers</span>
                                        </div>
                                        @if(!empty($filterMine))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </label>
                                </div>
                            </div>

                            {{-- Group By Section --}}
                            <div class="flex-1 md:px-3">
                                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="rectangle-group" class="size-3.5" />
                                    <span>Group By</span>
                                </div>
                                <div class="space-y-1">
                                    <label class="flex w-full cursor-pointer items-center justify-between gap-2 rounded px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" name="groupBy" value="salesperson" wire:model.live="groupBy" class="sr-only" />
                                            <span>Salesperson</span>
                                        </div>
                                        @if(($groupBy ?? '') === 'salesperson')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </label>
                                    <label class="flex w-full cursor-pointer items-center justify-between gap-2 rounded px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" name="groupBy" value="country" wire:model.live="groupBy" class="sr-only" />
                                            <span>Country</span>
                                        </div>
                                        @if(($groupBy ?? '') === 'country')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </label>
                                    <label class="flex w-full cursor-pointer items-center justify-between gap-2 rounded px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" name="groupBy" value="city" wire:model.live="groupBy" class="sr-only" />
                                            <span>City</span>
                                        </div>
                                        @if(($groupBy ?? '') === 'city')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </label>
                                    <label class="flex w-full cursor-pointer items-center justify-between gap-2 rounded px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" name="groupBy" value="status" wire:model.live="groupBy" class="sr-only" />
                                            <span>Status</span>
                                        </div>
                                        @if(($groupBy ?? '') === 'status')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </label>
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
                        {{ $customers->firstItem() ?? 0 }}-{{ $customers->lastItem() ?? 0 }}/{{ $customers->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button 
                            type="button"
                            wire:click="goToPreviousPage"
                            @disabled($customers->onFirstPage())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button 
                            type="button"
                            wire:click="goToNextPage"
                            @disabled(!$customers->hasMorePages())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                {{-- View Toggle --}}
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
                            @if($visibleColumns['customer'])
                                <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Customer</th>
                            @endif
                            @if($visibleColumns['contact'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Contact</th>
                            @endif
                            @if($visibleColumns['location'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Location</th>
                            @endif
                            @if($visibleColumns['orders'])
                                <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Orders</th>
                            @endif
                            @if($visibleColumns['total'])
                                <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Spent</th>
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
                                            <input type="checkbox" wire:model.live="visibleColumns.customer" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Customer</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.contact" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Contact</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.location" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Location</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.orders" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Orders</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.total" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Total Spent</span>
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
                        @forelse ($customers as $customer)
                            @php $isSelected = in_array($customer->id, $selected); @endphp
                            <tr 
                                onclick="window.location.href='{{ route('sales.customers.edit', $customer->id) }}'"
                                class="group cursor-pointer transition-all duration-150 {{ $isSelected ? 'bg-zinc-900/[0.03] dark:bg-zinc-100/[0.03]' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}"
                            >
                                <td class="relative py-4 pl-4 pr-1 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                    <div class="absolute inset-y-0 left-0 w-0.5 transition-all duration-150 {{ $isSelected ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-transparent group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700' }}"></div>
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selected"
                                        value="{{ $customer->id }}"
                                        class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600 {{ $isSelected ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-100/20' : '' }}"
                                    >
                                </td>
                                @if($visibleColumns['customer'])
                                    <td class="py-4 pl-2 pr-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                                {{ strtoupper(substr($customer->name, 0, 2)) }}
                                            </div>
                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $customer->name }}</span>
                                        </div>
                                    </td>
                                @endif
                                @if($visibleColumns['contact'])
                                    <td class="px-4 py-4">
                                        <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $customer->email }}</p>
                                        <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $customer->phone }}</p>
                                    </td>
                                @endif
                                @if($visibleColumns['location'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $customer->city }}{{ $customer->country ? ', ' . $customer->country : '' }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['orders'])
                                    <td class="px-4 py-4 text-center">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $customer->orders_count }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['total'])
                                    <td class="px-4 py-4 text-right">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($customer->sales_orders_sum_total ?? 0, 0, ',', '.') }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['status'])
                                    <td class="px-4 py-4">
                                        @if($customer->status === 'active')
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">Inactive</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="py-4 pr-4 sm:pr-6 lg:pr-8"></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                            <flux:icon name="users" class="size-6 text-zinc-400" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No customers found</p>
                                            <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Add your first customer to get started</p>
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
            <div class="-mx-4 sm:-mx-6 lg:-mx-8">
                <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 sm:p-6 lg:p-8">
                    @forelse ($customers as $customer)
                        <div class="group relative rounded-lg border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                            {{-- Checkbox --}}
                            <div class="absolute left-3 top-3" onclick="event.stopPropagation()">
                                <input 
                                    type="checkbox" 
                                    wire:model.live="selected"
                                    value="{{ $customer->id }}"
                                    class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600"
                                >
                            </div>
                            
                            <a href="{{ route('sales.customers.edit', $customer->id) }}" wire:navigate class="block">
                                <div class="flex items-center gap-3 pl-6">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 text-sm font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-zinc-900 truncate dark:text-zinc-100">{{ $customer->name }}</p>
                                        <p class="text-xs text-zinc-500 truncate dark:text-zinc-400">{{ $customer->email }}</p>
                                    </div>
                                    @if($customer->status === 'active')
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                                    @endif
                                </div>
                                <div class="mt-4 flex items-center justify-between text-sm">
                                    <span class="text-zinc-500 dark:text-zinc-400">{{ $customer->orders_count }} orders</span>
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($customer->sales_orders_sum_total ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No customers found
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- Pagination --}}
        @if($customers->hasPages())
            <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-800 sm:px-6 lg:px-8">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @isset($showDeleteConfirm)
        <x-ui.delete-confirm-modal 
            wire:model="showDeleteConfirm"
            :validation="$deleteValidation ?? []"
            title="Confirm Delete"
            itemLabel="customers"
        />
    @endisset
</div>
