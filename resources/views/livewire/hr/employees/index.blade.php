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
                <a href="{{ route('hr.employees.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Employees</span>
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
                <x-ui.searchbox-dropdown placeholder="Search employees..." widthClass="w-[520px]" width="520px">
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
                                <button type="button" wire:click="$set('status', 'active')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <div class="flex items-center gap-2">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Active</span>
                                    </div>
                                    @if($status === 'active')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('status', 'inactive')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <div class="flex items-center gap-2">
                                        <span class="h-1.5 w-1.5 rounded-full bg-zinc-500"></span>
                                        <span>Inactive</span>
                                    </div>
                                    @if($status === 'inactive')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('status', 'terminated')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <div class="flex items-center gap-2">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        <span>Terminated</span>
                                    </div>
                                    @if($status === 'terminated')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('status', 'resigned')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <div class="flex items-center gap-2">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        <span>Resigned</span>
                                    </div>
                                    @if($status === 'resigned')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <div class="my-2 border-t border-zinc-100 dark:border-zinc-700"></div>
                                <div class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-zinc-400">Department</div>
                                <button type="button" wire:click="$set('departmentId', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>All Departments</span>
                                    @if(empty($departmentId))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                @foreach($departments as $dept)
                                    <button type="button" wire:click="$set('departmentId', '{{ $dept->id }}')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>{{ $dept->name }}</span>
                                        @if($departmentId == $dept->id)<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        {{-- Sort column --}}
                        <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:px-3 dark:border-zinc-700">
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
                                <button type="button" wire:click="$set('sort', 'hire_date')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Hire Date</span>
                                    @if($sort === 'hire_date')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                            </div>
                        </div>
                        {{-- Group column --}}
                        <div class="flex-1 md:pl-3">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                <flux:icon name="rectangle-group" class="size-3.5" />
                                <span>Group By</span>
                            </div>
                            <div class="space-y-1">
                                <button type="button" wire:click="$set('groupBy', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>None</span>
                                    @if(empty($groupBy))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('groupBy', 'department')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Department</span>
                                    @if($groupBy === 'department')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('groupBy', 'position')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Position</span>
                                    @if($groupBy === 'position')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('groupBy', 'status')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Status</span>
                                    @if($groupBy === 'status')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
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
                        {{ $employees->firstItem() ?? 0 }}-{{ $employees->lastItem() ?? 0 }}/{{ $employees->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button type="button" wire:click="goToPreviousPage" @disabled($employees->onFirstPage()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button type="button" wire:click="goToNextPage" @disabled(!$employees->hasMorePages()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
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
    @if($showStats && $statistics && !$employees->isEmpty())
        <div class="-mx-4 -mt-6 mb-6 border-b border-zinc-200 bg-white px-4 py-4 sm:-mx-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-950">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="users" class="size-4 text-zinc-400 dark:text-zinc-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Total</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['total']) }}</p>
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
                        <flux:icon name="pause-circle" class="size-4 text-zinc-400" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Inactive</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['inactive']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="x-circle" class="size-4 text-red-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Terminated</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['terminated']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="arrow-right-start-on-rectangle" class="size-4 text-amber-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Resigned</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['resigned']) }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Content --}}
    @if($employees->isEmpty())
        <div class="-mx-4 -mt-6 -mb-6 flex min-h-[70vh] items-center justify-center bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
            <div class="-mt-16 flex flex-col items-center gap-4 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="users" class="size-8 text-zinc-400" />
                </div>
                <div>
                    <p class="text-base font-medium text-zinc-900 dark:text-zinc-100">No employees found</p>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Get started by adding a new employee</p>
                </div>
                <a href="{{ route('hr.employees.create') }}" wire:navigate class="mt-2 inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    <flux:icon name="plus" class="size-4" />
                    New Employee
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
                            <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Employee</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Position</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Department</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Hire Date</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                            <th scope="col" class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach($employees as $employee)
                            <tr wire:key="emp-{{ $employee->id }}" onclick="window.Livewire.navigate('{{ route('hr.employees.edit', $employee->id) }}')" class="cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="py-3 pl-4 pr-2 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                    <input type="checkbox" wire:model.live="selected" value="{{ $employee->id }}" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600">
                                </td>
                                <td class="py-3 pl-2 pr-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-sm font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ $employee->initials }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $employee->name }}</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $employee->email ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $employee->position?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $employee->department?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $employee->hire_date?->format('M d, Y') ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusConfig = match($employee->status) {
                                            'active' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400'],
                                            'inactive' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                            'terminated' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400'],
                                            'resigned' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400'],
                                            default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                        {{ ucfirst($employee->status) }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 sm:pr-6 lg:pr-8"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif($view === 'kanban')
            {{-- Kanban View --}}
            @php
                $statuses = [
                    'active' => ['label' => 'Active', 'color' => 'emerald', 'icon' => 'check-circle'],
                    'inactive' => ['label' => 'Inactive', 'color' => 'zinc', 'icon' => 'pause-circle'],
                    'terminated' => ['label' => 'Terminated', 'color' => 'red', 'icon' => 'x-circle'],
                    'resigned' => ['label' => 'Resigned', 'color' => 'amber', 'icon' => 'arrow-right-start-on-rectangle'],
                ];
                $groupedEmployees = $employees->groupBy('status');
            @endphp
            <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-zinc-50 px-4 py-6 sm:-mx-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
                <div class="flex gap-4" style="min-width: max-content;">
                    @foreach($statuses as $statusKey => $statusInfo)
                        @php
                            $statusEmployees = $groupedEmployees->get($statusKey, collect());
                            $colorClasses = match($statusInfo['color']) {
                                'emerald' => 'bg-emerald-500',
                                'red' => 'bg-red-500',
                                'amber' => 'bg-amber-500',
                                default => 'bg-zinc-400',
                            };
                        @endphp
                        <div class="w-72 flex-shrink-0">
                            {{-- Column Header --}}
                            <div class="mb-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full {{ $colorClasses }}"></span>
                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $statusInfo['label'] }}</span>
                                    <span class="rounded-full bg-zinc-200 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ $statusEmployees->count() }}</span>
                                </div>
                            </div>
                            {{-- Column Content --}}
                            <div class="space-y-2">
                                @forelse($statusEmployees as $employee)
                                    <a href="{{ route('hr.employees.edit', $employee->id) }}" wire:navigate class="block rounded-lg border border-zinc-200 bg-white p-3 shadow-sm transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-zinc-100 text-sm font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                {{ $employee->initials }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $employee->name }}</p>
                                                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $employee->position?->name ?? 'No position' }}</p>
                                            </div>
                                        </div>
                                        <div class="mt-2 flex items-center justify-between text-xs text-zinc-400 dark:text-zinc-500">
                                            <span>{{ $employee->department?->name ?? '-' }}</span>
                                            @if($employee->hire_date)
                                                <span>{{ $employee->hire_date->format('M Y') }}</span>
                                            @endif
                                        </div>
                                    </a>
                                @empty
                                    <div class="rounded-lg border border-dashed border-zinc-300 bg-zinc-50 p-4 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                                        <p class="text-xs text-zinc-400 dark:text-zinc-500">No employees</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            {{-- Grid View --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($employees as $employee)
                    <a href="{{ route('hr.employees.edit', $employee->id) }}" wire:navigate class="group rounded-xl border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                        <div class="flex items-start gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 text-lg font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $employee->initials }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $employee->name }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $employee->email ?? '-' }}</p>
                            </div>
                            @php
                                $statusConfig = match($employee->status) {
                                    'active' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400'],
                                    'inactive' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                    'terminated' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400'],
                                    'resigned' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400'],
                                    default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                };
                            @endphp
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </div>
                        <div class="mt-3 space-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                            <p>{{ $employee->position?->name ?? '-' }}</p>
                            <p>{{ $employee->department?->name ?? '-' }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    @endif
</div>
