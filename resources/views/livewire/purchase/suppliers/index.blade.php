<div>
    <x-ui.flash />

    {{-- Header --}}
    <x-ui.index-header
        title="Suppliers"
        :createRoute="route('purchase.suppliers.create')"
        :paginator="$suppliers"
        :view="$view"
        :views="['list']"
    >
        <x-slot:actions>
            <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                        <flux:icon name="arrow-down-tray" class="size-4" />
                                        <span>Import suppliers</span>
                                    </button>
                                    <a href="{{ route('export.suppliers') }}" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                        <flux:icon name="arrow-up-tray" class="size-4" />
                                        <span>Export all</span>
                                    </a>
        </x-slot:actions>

        <x-slot:search>
            <x-ui.searchbox-dropdown
                placeholder="Search suppliers..."
                widthClass="w-[480px]"
                width="480px"
                :activeFilterCount="$this->getActiveFilterCount()"
                clearAction="clearFilters"
            >
                <div class="flex flex-col gap-4 p-3 md:flex-row">
                    {{-- Status --}}
                    <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:pr-3 dark:border-zinc-700">
                        <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                            <flux:icon name="funnel" class="size-3.5" />
                            <span>Status</span>
                        </div>
                        <div class="space-y-1">
                            @foreach([
                                'all' => 'All Suppliers',
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ] as $value => $label)
                                <button type="button" wire:click="$set('status', '{{ $value }}')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>{{ $label }}</span>
                                    @if($status === $value)<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Sort --}}
                    <div class="flex-1 md:pl-3">
                        <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                            <flux:icon name="arrows-up-down" class="size-3.5" />
                            <span>Sort By</span>
                        </div>
                        <div class="space-y-1">
                            @foreach([
                                'latest' => 'Latest',
                                'oldest' => 'Oldest',
                                'name' => 'Name A-Z',
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
        </x-slot:search>
    </x-ui.index-header>

    {{-- Content --}}
    <div>
        @if($viewType === 'list')
            <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                        <tr>
                            @if($visibleColumns['supplier'])
                                <th class="py-3 pl-4 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pl-6 lg:pl-8 dark:text-zinc-400">Supplier</th>
                            @endif
                            @if($visibleColumns['contact'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Contact</th>
                            @endif
                            @if($visibleColumns['email'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Email</th>
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
                                            <input type="checkbox" wire:model.live="visibleColumns.supplier" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Supplier</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.contact" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Contact</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.email" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Email</span>
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
                        @forelse($suppliers as $supplier)
                            <tr onclick="window.location.href='{{ route('purchase.suppliers.edit', $supplier->id) }}'"
                                class="group cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                @if($visibleColumns['supplier'])
                                    <td class="py-4 pl-4 pr-4 sm:pl-6 lg:pl-8">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                                {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $supplier->name }}</span>
                                                @if($supplier->city)
                                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $supplier->city }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                @endif
                                @if($visibleColumns['contact'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $supplier->contact_person ?? '-' }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['email'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $supplier->email ?? '-' }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['status'])
                                    <td class="px-4 py-4">
                                        @if($supplier->is_active)
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">Active</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">Inactive</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="py-4 pr-4 sm:pr-6 lg:pr-8" onclick="event.stopPropagation()">
                                    <a href="{{ route('purchase.suppliers.edit', $supplier->id) }}" wire:navigate class="inline-flex rounded-md p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-300">
                                        <flux:icon name="pencil-square" class="size-4" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                            <flux:icon name="building-storefront" class="size-6 text-zinc-400" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No suppliers found</p>
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
                @forelse($suppliers as $supplier)
                    <a href="{{ route('purchase.suppliers.edit', $supplier->id) }}" wire:navigate class="relative block rounded-lg border border-zinc-200 bg-white p-3 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                        <span class="absolute right-2 top-2 inline-flex h-2 w-2 rounded-full {{ $supplier->is_active ? 'bg-emerald-500' : 'bg-zinc-400' }}"></span>
                        <div class="flex items-start gap-3">
                            <div class="relative flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 text-sm font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ strtoupper(substr($supplier->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $supplier->name }}</h3>
                                <p class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $supplier->email ?? 'No email' }}</p>
                                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">{{ $supplier->city ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-lg border border-zinc-200 bg-white px-5 py-12 text-center dark:border-zinc-800 dark:bg-zinc-900">
                        <flux:icon name="building-storefront" class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" />
                        <p class="mt-4 text-sm font-light text-zinc-500 dark:text-zinc-400">No suppliers found</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
