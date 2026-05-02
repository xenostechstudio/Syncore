<div>
    <x-ui.flash />    <x-ui.index-header
        title="Payroll"
        :createRoute="route('hr.payroll.create')"
        :paginator="$periods"
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

                            <x-ui.searchbox-dropdown placeholder="Search payroll..." widthClass="w-[420px]" width="420px" :activeFilterCount="$this->getActiveFilterCount()" clearAction="clearFilters">
                                <x-slot:badge>
                                    @if($status)
                                        <div class="flex items-center">
                                            <span class="inline-flex h-6 items-center gap-1.5 rounded-md bg-zinc-900 px-2 text-[10px] font-semibold text-white shadow-sm dark:bg-zinc-100 dark:text-zinc-900">
                                                <span>{{ ucfirst($status) }}</span>
                                                <button type="button" onclick="event.stopPropagation()" wire:click="$set('status', '')" class="-mr-0.5 inline-flex h-4 w-4 items-center justify-center rounded-md text-zinc-400 hover:bg-zinc-200 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-200">
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
                                            <span>Filters</span>
                                        </div>
                                        <div class="space-y-1">
                                            <button type="button" wire:click="$set('status', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <span>All Status</span>
                                                @if(empty($status))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                            </button>
                                            <button type="button" wire:click="$set('status', 'draft')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <div class="flex items-center gap-2">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span>
                                                    <span>Draft</span>
                                                </div>
                                                @if($status === 'draft')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                            </button>
                                            <button type="button" wire:click="$set('status', 'processing')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <div class="flex items-center gap-2">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                                    <span>Processing</span>
                                                </div>
                                                @if($status === 'processing')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                            </button>
                                            <button type="button" wire:click="$set('status', 'approved')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <div class="flex items-center gap-2">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    <span>Approved</span>
                                                </div>
                                                @if($status === 'approved')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                            </button>
                                            <button type="button" wire:click="$set('status', 'paid')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <div class="flex items-center gap-2">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
                                                    <span>Paid</span>
                                                </div>
                                                @if($status === 'paid')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
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
                                            <button type="button" wire:click="$set('sort', 'latest')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <span>Latest</span>
                                                @if($sort === 'latest')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                            </button>
                                            <button type="button" wire:click="$set('sort', 'oldest')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <span>Oldest</span>
                                                @if($sort === 'oldest')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                            </button>
                                            <button type="button" wire:click="$set('sort', 'name_asc')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <span>Name: A to Z</span>
                                                @if($sort === 'name_asc')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                            </button>
                                            <button type="button" wire:click="$set('sort', 'name_desc')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <span>Name: Z to A</span>
                                                @if($sort === 'name_desc')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                            </button>
                                            <button type="button" wire:click="$set('sort', 'total_desc')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <span>Total: High to Low</span>
                                                @if($sort === 'total_desc')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                            </button>
                                            <button type="button" wire:click="$set('sort', 'total_asc')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <span>Total: Low to High</span>
                                                @if($sort === 'total_asc')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </x-ui.searchbox-dropdown>
        </x-slot:search>
    </x-ui.index-header>

    {{-- Statistics Cards --}}
    @if($showStats && $statistics && !$periods->isEmpty())
        <div class="-mx-4 -mt-6 mb-6 border-b border-zinc-200 bg-white px-4 py-4 sm:-mx-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-950">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-6">
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="document-text" class="size-4 text-zinc-400 dark:text-zinc-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Total</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['total']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="pencil-square" class="size-4 text-zinc-400" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Draft</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['draft']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="arrow-path" class="size-4 text-blue-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Processing</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['processing']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="check-circle" class="size-4 text-emerald-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Approved</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['approved']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="banknotes" class="size-4 text-violet-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Paid</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['paid']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="currency-dollar" class="size-4 text-emerald-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Total Paid</p>
                    </div>
                    <p class="mt-2 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($statistics['total_amount'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Content --}}
    @if($periods->isEmpty())
        <x-ui.empty-state
                layout="fullscreen"
                icon="banknotes"
                title="No payroll runs found"
                description="Get started by creating a new payroll run"
                :actionUrl="route('hr.payroll.create')"
                actionLabel="New Payroll Run"
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
                        <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Period</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Date Range</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Employees</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Net</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                        <th scope="col" class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @foreach($periods as $period)
                        @php $isSelected = in_array($period->id, $selected); @endphp
                        <tr wire:key="period-{{ $period->id }}" onclick="window.location.href='{{ route('hr.payroll.edit', $period->id) }}'" class="group cursor-pointer transition-all duration-150 {{ $isSelected ? 'bg-zinc-900/[0.03] dark:bg-zinc-100/[0.03]' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                            <td class="relative py-3 pl-4 pr-2 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                <div class="absolute inset-y-0 left-0 w-0.5 transition-all duration-150 {{ $isSelected ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-transparent group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700' }}"></div>
                                <input type="checkbox" wire:model.live="selected" value="{{ $period->id }}" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600 {{ $isSelected ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-100/20' : '' }}">
                            </td>
                            <td class="py-3 pl-2 pr-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                        <flux:icon name="calendar" class="size-4 text-zinc-500 dark:text-zinc-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $period->name }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $period->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $period->start_date->format('d M') }} - {{ $period->end_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $period->items_count }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                Rp {{ number_format($period->total_net, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <x-ui.status-badge :status="$period->state" />
                            </td>
                            <td class="py-3 pr-4 sm:pr-6 lg:pr-8"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @elseif($view === 'grid')
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($periods as $period)
                <a href="{{ route('hr.payroll.edit', $period->id) }}" wire:navigate class="group rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $period->start_date->format('d M') }} - {{ $period->end_date->format('d M Y') }}</span>
                        <x-ui.status-badge :status="$period->state" class="px-2 py-0.5" />
                    </div>
                    <p class="mt-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $period->name }}</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $period->items_count }} employees</span>
                        <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($period->total_net, 0, ',', '.') }}</span>
                    </div>
                </a>
            @endforeach
        </div>
        @elseif($view === 'kanban')
        @php
            // Drive kanban columns from the PayrollState enum so ordering
            // and colors stay in sync with the real state machine
            // (DRAFT → APPROVED → PROCESSING → PAID, with CANCELLED as a
            // branch handled elsewhere).
            $kanbanStates = [
                \App\Enums\PayrollState::DRAFT,
                \App\Enums\PayrollState::APPROVED,
                \App\Enums\PayrollState::PROCESSING,
                \App\Enums\PayrollState::PAID,
            ];
            $periodsByStatus = $periods->groupBy('status');
        @endphp
        <div class="flex gap-4 overflow-x-auto pb-4">
            @foreach($kanbanStates as $state)
                @php
                    // Literal classes so Tailwind JIT picks them up.
                    [$headerBg, $dotBg] = match ($state->color()) {
                        'blue'    => ['bg-blue-50 dark:bg-blue-900/20', 'bg-blue-500'],
                        'amber'   => ['bg-amber-50 dark:bg-amber-900/20', 'bg-amber-500'],
                        'emerald' => ['bg-emerald-50 dark:bg-emerald-900/20', 'bg-emerald-500'],
                        'red'     => ['bg-red-50 dark:bg-red-900/20', 'bg-red-500'],
                        default   => ['bg-zinc-100 dark:bg-zinc-800', 'bg-zinc-500'],
                    };
                @endphp
                <div class="flex w-72 flex-shrink-0 flex-col rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/50">
                    <div class="flex items-center justify-between rounded-t-lg {{ $headerBg }} px-3 py-2.5">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full {{ $dotBg }}"></span>
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $state->label() }}</span>
                            <span class="rounded-full bg-white px-1.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                {{ $periodsByStatus->get($state->value)?->count() ?? 0 }}
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col gap-2 p-2">
                        @forelse($periodsByStatus->get($state->value, collect()) as $period)
                            <a href="{{ route('hr.payroll.edit', $period->id) }}" wire:navigate class="rounded-lg border border-zinc-200 bg-white p-3 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600">
                                <div class="mb-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $period->name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $period->start_date->format('d M') }} - {{ $period->end_date->format('d M') }}</div>
                                <div class="mt-2 flex items-center justify-between text-xs">
                                    <span class="text-zinc-400 dark:text-zinc-500">{{ $period->items_count }} emp</span>
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">Rp {{ number_format($period->total_net / 1000000, 1) }}M</span>
                                </div>
                            </a>
                        @empty
                            <div class="flex flex-1 items-center justify-center py-8">
                                <p class="text-xs text-zinc-400 dark:text-zinc-500">No payroll</p>
                            </div>
                        @endforelse
                    </div>
                    @if($periodsByStatus->get($state->value)?->count() > 0)
                        <div class="border-t border-zinc-200 px-3 py-2 dark:border-zinc-700">
                            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                Rp {{ number_format($periodsByStatus->get($state->value)->sum('total_net'), 0, ',', '.') }}
                            </span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        @endif
    @endif
</div>
