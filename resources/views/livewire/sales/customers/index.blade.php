<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-normal text-zinc-900 dark:text-zinc-100">Customers</h1>
        <flux:button variant="primary" icon="plus">
            Add Customer
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
                    placeholder="Search customers..."
                    class="w-64 rounded-lg border border-zinc-200 bg-white py-2 pl-10 pr-4 text-sm font-light text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:focus:border-zinc-600"
                />
            </div>

            {{-- Status Filter --}}
            <select 
                wire:model.live="status"
                class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-light text-zinc-600 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
            >
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            @if($search || $status)
                <button wire:click="clearFilters" class="text-sm font-light text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                    Clear filters
                </button>
            @endif
        </div>

        <x-ui.view-toggle :view="$view" />
    </div>

    {{-- Results Count --}}
    <div class="text-sm font-light text-zinc-500 dark:text-zinc-400">
        {{ $customers->total() }} {{ Str::plural('customer', $customers->total()) }}
    </div>

    {{-- Content --}}
    @if($view === 'list')
        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900">
                        <th class="px-5 py-3 text-left text-xs font-normal text-zinc-500 dark:text-zinc-400">Customer</th>
                        <th class="px-5 py-3 text-left text-xs font-normal text-zinc-500 dark:text-zinc-400">Contact</th>
                        <th class="px-5 py-3 text-left text-xs font-normal text-zinc-500 dark:text-zinc-400">Location</th>
                        <th class="px-5 py-3 text-left text-xs font-normal text-zinc-500 dark:text-zinc-400">Orders</th>
                        <th class="px-5 py-3 text-left text-xs font-normal text-zinc-500 dark:text-zinc-400">Total Spent</th>
                        <th class="px-5 py-3 text-left text-xs font-normal text-zinc-500 dark:text-zinc-400">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-normal text-zinc-500 dark:text-zinc-400"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($customers as $customer)
                        <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 text-sm font-normal text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                    </div>
                                    <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $customer->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm font-light text-zinc-600 dark:text-zinc-300">{{ $customer->email }}</p>
                                <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $customer->phone }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm font-light text-zinc-600 dark:text-zinc-300">{{ $customer->city }}, {{ $customer->country }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm font-light text-zinc-600 dark:text-zinc-300">{{ $customer->orders_count }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">${{ number_format($customer->orders_sum_total ?? 0, 2) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @if($customer->status === 'active')
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-light text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-light text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button class="rounded p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <svg class="size-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
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
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($customers as $customer)
                <div class="rounded-lg border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 text-sm font-normal text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ strtoupper(substr($customer->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $customer->name }}</p>
                            <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $customer->city }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                            <span class="font-light">{{ $customer->email }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                            </svg>
                            <span class="font-light">{{ $customer->phone }}</span>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-zinc-800">
                        <div>
                            <p class="text-lg font-normal text-zinc-900 dark:text-zinc-100">${{ number_format($customer->orders_sum_total ?? 0, 0) }}</p>
                            <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $customer->orders_count }} orders</p>
                        </div>
                        @if($customer->status === 'active')
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-light text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-light text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">Inactive</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center">
                    <p class="text-sm font-light text-zinc-500 dark:text-zinc-400">No customers found</p>
                </div>
            @endforelse
        </div>
    @endif

    {{-- Pagination --}}
    @if($customers->hasPages())
        <div class="flex items-center justify-between border-t border-zinc-200 pt-4 dark:border-zinc-800">
            <p class="text-sm font-light text-zinc-500 dark:text-zinc-400">
                Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} results
            </p>
            {{ $customers->links('livewire.pagination') }}
        </div>
    @endif
</div>
