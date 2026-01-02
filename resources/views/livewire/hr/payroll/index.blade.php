<div>
    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
        @endif
    </div>

    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.payroll.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Payroll Runs</span>
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

            {{-- Center Group: Search --}}
            <div class="flex flex-1 items-center justify-center">
                <x-ui.searchbox-dropdown placeholder="Search payroll..." widthClass="w-[420px]" width="420px">
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
            </div>

            {{-- Right Group --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $periods->firstItem() ?? 0 }}-{{ $periods->lastItem() ?? 0 }}/{{ $periods->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button type="button" wire:click="goToPreviousPage" @disabled($periods->onFirstPage()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button type="button" wire:click="goToNextPage" @disabled(!$periods->hasMorePages()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>
                <div class="flex h-9 items-center rounded-lg border border-zinc-200 p-0.5 dark:border-zinc-700">
                    <button type="button" wire:click="toggleStats" class="{{ $showStats ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300' }} rounded-md p-1.5 transition-colors" title="{{ $showStats ? 'Hide statistics' : 'Show statistics' }}">
                        <flux:icon name="chart-bar" class="size-[18px]" />
                    </button>
                </div>
                <x-ui.view-toggle :view="$view" :views="['list', 'grid', 'kanban']" />
            </div>
        </div>
    </div>

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
        <div class="-mx-4 -mt-6 -mb-6 flex min-h-[70vh] items-center justify-center bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
            <div class="-mt-16 flex flex-col items-center gap-4 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="banknotes" class="size-8 text-zinc-400" />
                </div>
                <div>
                    <p class="text-base font-medium text-zinc-900 dark:text-zinc-100">No payroll runs found</p>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Get started by creating a new payroll run</p>
                </div>
                <a href="{{ route('hr.payroll.create') }}" wire:navigate class="mt-2 inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    <flux:icon name="plus" class="size-4" />
                    New Payroll Run
                </a>
            </div>
        </div>
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
                        <tr wire:key="period-{{ $period->id }}" onclick="window.location.href='{{ route('hr.payroll.edit', $period->id) }}'" class="cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="py-3 pl-4 pr-2 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                <input type="checkbox" wire:model.live="selected" value="{{ $period->id }}" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600">
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
                                @php
                                    $statusConfig = match($period->status) {
                                        'draft' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                        'processing' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400'],
                                        'approved' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400'],
                                        'paid' => ['bg' => 'bg-violet-100 dark:bg-violet-900/30', 'text' => 'text-violet-700 dark:text-violet-400'],
                                        'cancelled' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400'],
                                        default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                    {{ ucfirst($period->status) }}
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
            @foreach($periods as $period)
                @php
                    $statusConfig = match($period->status) {
                        'draft' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                        'processing' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400'],
                        'approved' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400'],
                        'paid' => ['bg' => 'bg-violet-100 dark:bg-violet-900/30', 'text' => 'text-violet-700 dark:text-violet-400'],
                        'cancelled' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400'],
                        default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                    };
                @endphp
                <a href="{{ route('hr.payroll.edit', $period->id) }}" wire:navigate class="group rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $period->start_date->format('d M') }} - {{ $period->end_date->format('d M Y') }}</span>
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                            {{ ucfirst($period->status) }}
                        </span>
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
            $statuses = [
                'draft' => ['label' => 'Draft', 'color' => 'zinc', 'headerBg' => 'bg-zinc-100 dark:bg-zinc-800'],
                'processing' => ['label' => 'Processing', 'color' => 'blue', 'headerBg' => 'bg-blue-50 dark:bg-blue-900/20'],
                'approved' => ['label' => 'Approved', 'color' => 'emerald', 'headerBg' => 'bg-emerald-50 dark:bg-emerald-900/20'],
                'paid' => ['label' => 'Paid', 'color' => 'violet', 'headerBg' => 'bg-violet-50 dark:bg-violet-900/20'],
            ];
            $periodsByStatus = $periods->groupBy('status');
        @endphp
        <div class="flex gap-4 overflow-x-auto pb-4">
            @foreach($statuses as $statusKey => $statusInfo)
                <div class="flex w-72 flex-shrink-0 flex-col rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/50">
                    <div class="flex items-center justify-between rounded-t-lg {{ $statusInfo['headerBg'] }} px-3 py-2.5">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-{{ $statusInfo['color'] }}-500"></span>
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $statusInfo['label'] }}</span>
                            <span class="rounded-full bg-white px-1.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                {{ $periodsByStatus->get($statusKey)?->count() ?? 0 }}
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col gap-2 p-2">
                        @forelse($periodsByStatus->get($statusKey, collect()) as $period)
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
                    @if($periodsByStatus->get($statusKey)?->count() > 0)
                        <div class="border-t border-zinc-200 px-3 py-2 dark:border-zinc-700">
                            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                Rp {{ number_format($periodsByStatus->get($statusKey)->sum('total_net'), 0, ',', '.') }}
                            </span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        @endif
    @endif
</div>
