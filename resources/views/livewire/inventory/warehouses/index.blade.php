<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-xl font-normal text-zinc-900 dark:text-zinc-100">Warehouses</h1>
        <div class="flex items-center gap-2">
            <flux:button variant="primary" icon="plus" href="{{ route('inventory.warehouses.create') }}" wire:navigate>
                New Warehouse
            </flux:button>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {{-- Search --}}
        <div class="relative w-full sm:w-72">
            <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search"
                placeholder="Search warehouses..."
                class="w-full rounded-lg border border-zinc-200 bg-white py-2 pl-10 pr-4 text-sm font-light text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder-zinc-500 dark:focus:border-zinc-700"
            />
        </div>

        {{-- Right: View Toggle & Per Page --}}
        <div class="flex items-center gap-3">
             <div class="hidden sm:block">
                <select 
                    wire:model.live="perPage"
                    class="h-9 rounded-lg border border-zinc-200 bg-white px-3 py-0 text-sm font-light text-zinc-600 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:focus:border-zinc-700"
                >
                    <option value="15">15 rows</option>
                    <option value="25">25 rows</option>
                    <option value="50">50 rows</option>
                    <option value="100">100 rows</option>
                </select>
            </div>
            <x-ui.view-toggle :view="$view" />
        </div>
    </div>

    {{-- Content --}}
    <div class="relative min-h-[500px]">
        {{-- Loading State --}}
        <div wire:loading.delay class="absolute inset-0 z-10 flex items-start justify-center bg-white/50 pt-20 backdrop-blur-sm dark:bg-zinc-950/50">
            <div class="flex items-center gap-2 rounded-full bg-white px-4 py-2 shadow-lg ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-800">
                <svg class="size-4 animate-spin text-zinc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Loading...</span>
            </div>
        </div>

        @if($view === 'list')
            {{-- List View --}}
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Name</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Location</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Contact</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Items</th>
                            <th class="px-6 py-3 text-right font-medium text-zinc-500 dark:text-zinc-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($warehouses as $warehouse)
                            <tr 
                                onclick="window.location='{{ route('inventory.warehouses.edit', $warehouse) }}'"
                                class="group cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                                            <flux:icon name="building-storefront" class="size-5" />
                                        </div>
                                        <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $warehouse->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400">
                                    {{ $warehouse->location }}
                                </td>
                                <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400">
                                    {{ $warehouse->contact_info }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                        {{ $warehouse->items_count ?? 0 }} items
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100" onclick="event.stopPropagation()">
                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="pencil" 
                                            href="{{ route('inventory.warehouses.edit', $warehouse) }}" 
                                            wire:navigate 
                                        />
                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="trash" 
                                            class="text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20" 
                                            wire:click="delete({{ $warehouse->id }})"
                                            wire:confirm="Are you sure you want to delete this warehouse?"
                                        />
                                    </div>
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
                                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">No warehouses found</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Add your first warehouse to get started</p>
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
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($warehouses as $warehouse)
                    <div 
                        onclick="window.location='{{ route('inventory.warehouses.edit', $warehouse) }}'"
                        class="group relative flex cursor-pointer flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700"
                    >
                        <div class="flex flex-1 flex-col p-5">
                            <div class="mb-4 flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                                    <flux:icon name="building-storefront" class="size-5" />
                                </div>
                                <div>
                                    <h3 class="font-medium text-zinc-900 dark:text-zinc-100">{{ $warehouse->name }}</h3>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $warehouse->items_count ?? 0 }} items stored</p>
                                </div>
                            </div>
                            
                            <div class="mt-auto space-y-2 text-sm">
                                <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="map-pin" class="size-4" />
                                    <span class="line-clamp-1">{{ $warehouse->location }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="phone" class="size-4" />
                                    <span class="line-clamp-1">{{ $warehouse->contact_info }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Hover Actions --}}
                        <div class="absolute right-2 top-2 flex gap-1 opacity-0 transition-opacity group-hover:opacity-100" onclick="event.stopPropagation()">
                            <button 
                                wire:click="delete({{ $warehouse->id }})"
                                wire:confirm="Are you sure you want to delete this warehouse?"
                                class="rounded-lg bg-white/90 p-1.5 text-red-500 shadow-sm backdrop-blur hover:text-red-600 dark:bg-zinc-900/90"
                            >
                                <flux:icon name="trash" class="size-4" />
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon name="building-storefront" class="size-6 text-zinc-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">No warehouses found</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Add your first warehouse to get started</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- Pagination --}}
        @if($warehouses->hasPages())
            <div class="mt-6">
                {{ $warehouses->links('livewire.pagination') }}
            </div>
        @endif
    </div>
</div>
