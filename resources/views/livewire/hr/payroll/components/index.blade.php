<div>
    <x-ui.flash />    <x-ui.index-header
        title="Salary Components"
        :createRoute="route('hr.payroll.components.create')"
        :paginator="$components"
        :view="$view"
        :views="['list']"
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

                            @if(count($selected) > 0)
                                <div class="flex items-center gap-2 animate-in fade-in slide-in-from-top-2 duration-200">
                                    <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-zinc-100 px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-200 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-600">
                                        <flux:icon name="x-mark" class="size-4" />
                                        <span>{{ count($selected) }} Selected</span>
                                    </button>
                                </div>
                            @else
                                <x-ui.searchbox-dropdown placeholder="Search components..." widthClass="w-[420px]" width="420px" :activeFilterCount="$this->getActiveFilterCount()" clearAction="clearFilters">
                                    <x-slot:badge>
                                        @if($componentType)
                                            <div class="flex items-center">
                                                <span class="inline-flex h-6 items-center gap-1.5 rounded-md bg-zinc-900 px-2 text-[10px] font-semibold text-white shadow-sm dark:bg-zinc-100 dark:text-zinc-900">
                                                    <span>{{ ucfirst($componentType) }}</span>
                                                    <button type="button" onclick="event.stopPropagation()" wire:click="$set('componentType', '')" class="-mr-0.5 inline-flex h-4 w-4 items-center justify-center rounded-md text-zinc-400 hover:bg-zinc-200 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-200">
                                                        <flux:icon name="x-mark" class="size-3" />
                                                    </button>
                                                </span>
                                            </div>
                                        @endif
                                    </x-slot:badge>
                                    <div class="flex flex-col gap-4 p-3 md:flex-row">
                                        {{-- Filters column --}}
                                        <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:pr-3 dark:border-zinc-700">
                                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                                <flux:icon name="funnel" class="size-3.5" />
                                                <span>Type</span>
                                            </div>
                                            <div class="space-y-1">
                                                <button type="button" wire:click="$set('componentType', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <span>All Types</span>
                                                    @if(empty($componentType))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                                </button>
                                                <button type="button" wire:click="$set('componentType', 'earning')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <div class="flex items-center gap-2">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                        <span>Earning</span>
                                                    </div>
                                                    @if($componentType === 'earning')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                                </button>
                                                <button type="button" wire:click="$set('componentType', 'deduction')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <div class="flex items-center gap-2">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                                        <span>Deduction</span>
                                                    </div>
                                                    @if($componentType === 'deduction')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                                </button>
                                            </div>
                                        </div>
                                        {{-- Sort column --}}
                                        <div class="flex-1 md:pl-3">
                                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                                <flux:icon name="arrows-up-down" class="size-3.5" />
                                                <span>Sort By</span>
                                            </div>
                                            <div class="space-y-1">
                                                <button type="button" wire:click="$set('sort', 'sort_order')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <span>Default Order</span>
                                                    @if($sort === 'sort_order')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                                </button>
                                                <button type="button" wire:click="$set('sort', 'name_asc')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <span>Name: A to Z</span>
                                                    @if($sort === 'name_asc')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                                </button>
                                                <button type="button" wire:click="$set('sort', 'name_desc')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <span>Name: Z to A</span>
                                                    @if($sort === 'name_desc')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                                </button>
                                                <button type="button" wire:click="$set('sort', 'amount_desc')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <span>Amount: High to Low</span>
                                                    @if($sort === 'amount_desc')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                                </button>
                                                <button type="button" wire:click="$set('sort', 'amount_asc')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <span>Amount: Low to High</span>
                                                    @if($sort === 'amount_asc')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </x-ui.searchbox-dropdown>
                            @endif
        </x-slot:search>
    </x-ui.index-header>

    {{-- Statistics Cards --}}
    @if($showStats && $statistics && !$components->isEmpty())
        <div class="-mx-4 -mt-6 mb-6 border-b border-zinc-200 bg-white px-4 py-4 sm:-mx-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-950">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="rectangle-stack" class="size-4 text-zinc-400 dark:text-zinc-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Total</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['total']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="plus-circle" class="size-4 text-emerald-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Earnings</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['earnings']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="minus-circle" class="size-4 text-red-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Deductions</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['deductions']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="check-circle" class="size-4 text-emerald-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Active</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['active']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="receipt-percent" class="size-4 text-amber-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Taxable</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['taxable']) }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Content --}}
    @if($components->isEmpty())
        <x-ui.empty-state
                layout="fullscreen"
                icon="rectangle-stack"
                title="No salary components found"
                description="Get started by adding a new salary component"
                :actionUrl="route('hr.payroll.components.create')"
                actionLabel="New Component"
            />
    @else
        @if($view === 'list')
        <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                    <tr>
                        <th scope="col" class="w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600">
                        </th>
                        <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Component</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Type</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Default Amount</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Taxable</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                        <th scope="col" class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @foreach($components as $component)
                        @php
                            $compType = $component->type;
                            $isEarning = $compType === 'earning';
                            $isSelected = in_array($component->id, $selected);
                        @endphp
                        <tr wire:key="comp-{{ $component->id }}" onclick="window.location.href='{{ route('hr.payroll.components.edit', $component->id) }}'" class="group cursor-pointer transition-all duration-150 {{ $isSelected ? 'bg-zinc-900/[0.03] dark:bg-zinc-100/[0.03]' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                            <td class="relative py-3 pl-4 pr-2 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                <div class="absolute inset-y-0 left-0 w-0.5 transition-all duration-150 {{ $isSelected ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-transparent group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700' }}"></div>
                                <input type="checkbox" wire:model.live="selected" value="{{ $component->id }}" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600 {{ $isSelected ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-100/20' : '' }}">
                            </td>
                            <td class="py-3 pl-2 pr-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $isEarning ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-red-100 dark:bg-red-900/30' }}">
                                        <flux:icon name="{{ $isEarning ? 'plus' : 'minus' }}" class="size-4 {{ $isEarning ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $component->name }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $component->code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $isEarning ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                    {{ ucfirst($compType) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                Rp {{ number_format($component->default_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($component->is_taxable)
                                    <flux:icon name="check" class="mx-auto size-4 text-emerald-500" />
                                @else
                                    <flux:icon name="x-mark" class="mx-auto size-4 text-zinc-400" />
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $component->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                    {{ $component->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-3 pr-4 sm:pr-6 lg:pr-8"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @elseif($view === 'grid')
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($components as $component)
                @php
                    $compType = $component->type;
                    $isEarning = $compType === 'earning';
                @endphp
                <a href="{{ route('hr.payroll.components.edit', $component->id) }}" wire:navigate class="group rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg {{ $isEarning ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-red-100 dark:bg-red-900/30' }}">
                            <flux:icon name="{{ $isEarning ? 'plus' : 'minus' }}" class="size-4 {{ $isEarning ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}" />
                        </div>
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $component->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                            {{ $component->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <p class="mt-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $component->name }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $component->code }}</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $isEarning ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                            {{ ucfirst($compType) }}
                        </span>
                        <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($component->default_amount / 1000, 0) }}K</span>
                    </div>
                </a>
            @endforeach
        </div>
        @endif
    @endif
</div>
