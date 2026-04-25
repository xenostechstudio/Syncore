<div>
    <x-ui.flash />

    <x-slot:header>
        <x-ui.index-header
            :bare="true"
            title="Warehouses"
            :createRoute="route('inventory.warehouses.create')"
            :paginator="$warehouses"
            :view="$view"
            searchPlaceholder="Search warehouses..."
        >
            <x-slot:actions>
                <button type="button" wire:click="openImportModal" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                    <flux:icon name="arrow-down-tray" class="size-4" />
                    <span>Import records</span>
                </button>
                <button type="button" wire:click="exportSelected" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                    <flux:icon name="arrow-up-tray" class="size-4" />
                    <span>Export All</span>
                </button>
            </x-slot:actions>
        </x-ui.index-header>
    </x-slot:header>

    {{-- Content --}}
    <div class="-mx-4 -mt-6 sm:-mx-6 lg:-mx-8">
        @if($view === 'list')
            {{-- Table View --}}
            <div class="overflow-hidden bg-white dark:bg-zinc-950">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                        <tr>
                            <th scope="col" class="py-3 pl-4 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pl-6 lg:pl-8 dark:text-zinc-400">Name</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Location</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Contact</th>
                            <th scope="col" class="py-3 pl-4 pr-4 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pr-6 lg:pr-8 dark:text-zinc-400">Products</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-950">
                        @forelse($warehouses as $warehouse)
                            <tr 
                                onclick="window.location.href='{{ route('inventory.warehouses.edit', $warehouse->id) }}'"
                                class="group cursor-pointer transition-all duration-150 hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            >
                                <td class="relative whitespace-nowrap py-4 pl-4 pr-4 sm:pl-6 lg:pl-8">
                                    <div class="absolute inset-y-0 left-0 w-0.5 bg-transparent transition-all duration-150 group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700"></div>
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                            <flux:icon name="building-storefront" class="size-5 text-zinc-500 dark:text-zinc-400" />
                                        </div>
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $warehouse->name }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $warehouse->location ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $warehouse->contact_info ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-4 text-right sm:pr-6 lg:pr-8">
                                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $warehouse->products_count ?? 0 }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                            <flux:icon name="building-storefront" class="size-6 text-zinc-400" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No warehouses found</p>
                                            <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Create your first warehouse to get started</p>
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
            <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 sm:p-6 lg:p-8">
                @forelse($warehouses as $warehouse)
                    <a href="{{ route('inventory.warehouses.edit', $warehouse->id) }}" wire:navigate class="group relative block rounded-xl border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                        {{-- Header with Name & Status --}}
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-base font-semibold text-zinc-900 group-hover:text-zinc-700 dark:text-zinc-100 dark:group-hover:text-zinc-300">
                                {{ $warehouse->name }}
                            </h3>
                            <span class="inline-flex shrink-0 items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
                                Active
                            </span>
                        </div>

                        {{-- Location --}}
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2">
                            {{ $warehouse->location ?? 'No location specified' }}
                        </p>

                        {{-- Footer Stats --}}
                        <div class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-zinc-800">
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $warehouse->products_count ?? 0 }}</span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">products</span>
                            </div>
                            @if($warehouse->contact_info)
                                <span class="text-xs text-zinc-400 dark:text-zinc-500 truncate max-w-[120px]">{{ $warehouse->contact_info }}</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon name="building-storefront" class="size-6 text-zinc-400" />
                            </div>
                            <div>
                                <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No warehouses found</p>
                                <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Create your first warehouse to get started</p>
                            </div>
                            <a href="{{ route('inventory.warehouses.create') }}" wire:navigate class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                                <flux:icon name="plus" class="size-4" />
                                New Warehouse
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif
    </div>

    {{-- Import Modal --}}
    <x-ui.import-modal
        wire:model="showImportModal"
        title="Import Warehouses"
        :livewire="true"
        :result="$this->importResult"
        :importErrors="$this->importErrors"
    />
</div>
