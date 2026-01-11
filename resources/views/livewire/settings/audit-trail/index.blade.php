<div>
    <x-slot:header>
        <div class="flex items-center gap-3">
            @if($search || $action || $modelType || $userId || $dateFrom || $dateTo)
            <button 
                wire:click="clearFilters"
                class="inline-flex items-center justify-center rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                Clear
            </button>
            @endif
            <h1 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Audit Trail</h1>
            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                {{ $activities->total() }} activities
            </span>
        </div>
        <div class="flex items-center gap-4">
            {{-- Search with Filter Dropdown --}}
            <div class="flex items-center gap-2">
                <div class="relative flex h-9 w-64 items-center overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search activities..." 
                        class="h-full w-full border-0 bg-transparent pl-9 pr-4 text-sm outline-none focus:ring-0" 
                    />
                </div>
                
                {{-- Filter Button --}}
                <flux:dropdown position="bottom" align="end">
                    <button class="flex h-9 items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-600 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="funnel" class="size-4" />
                        <span>Filter</span>
                        @if($action || $modelType || $userId || $dateFrom || $dateTo)
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-zinc-900 text-[10px] font-medium text-white dark:bg-zinc-100 dark:text-zinc-900">
                                {{ collect([$action, $modelType, $userId, $dateFrom, $dateTo])->filter()->count() }}
                            </span>
                        @endif
                    </button>

                    <flux:menu class="w-80">
                        <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Filter Activities</h3>
                        </div>
                        <div class="space-y-4 p-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Action</label>
                                    <select 
                                        wire:model.live="action" 
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                        <option value="">All</option>
                                        @foreach($actions as $act)
                                            <option value="{{ $act }}">{{ ucfirst($act) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Model</label>
                                    <select 
                                        wire:model.live="modelType" 
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                        <option value="">All</option>
                                        @foreach($modelTypes as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">User</label>
                                <select 
                                    wire:model.live="userId" 
                                    class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                                    <option value="">All Users</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Date Range</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input 
                                        type="date" 
                                        wire:model.live="dateFrom" 
                                        placeholder="From"
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    />
                                    <input 
                                        type="date" 
                                        wire:model.live="dateTo" 
                                        placeholder="To"
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    />
                                </div>
                            </div>
                        </div>
                        @if($action || $modelType || $userId || $dateFrom || $dateTo)
                        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
                            <button 
                                wire:click="clearFilters"
                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                            >
                                Clear All Filters
                            </button>
                        </div>
                        @endif
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Pagination --}}
            <div class="flex items-center gap-2">
                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $activities->firstItem() ?? 0 }}-{{ $activities->lastItem() ?? 0 }}/{{ $activities->total() }}
                </span>
                <div class="flex items-center gap-0.5">
                    <button 
                        type="button"
                        wire:click="previousPage"
                        @disabled($activities->onFirstPage())
                        class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                    >
                        <flux:icon name="chevron-left" class="size-4" />
                    </button>
                    <button 
                        type="button"
                        wire:click="nextPage"
                        @disabled(!$activities->hasMorePages())
                        class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                    >
                        <flux:icon name="chevron-right" class="size-4" />
                    </button>
                </div>
            </div>
        </div>
    </x-slot:header>

    {{-- Statistics Cards --}}
    <div class="-mx-4 -mt-6 mb-6 border-b border-zinc-200 bg-white px-4 py-4 sm:-mx-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="clipboard-document-list" class="size-4 text-zinc-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Total Activities</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="calendar" class="size-4 text-blue-500" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Today</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($stats['today']) }}</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="chart-bar" class="size-4 text-emerald-500" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">This Week</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($stats['this_week']) }}</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="arrow-path" class="size-4 text-amber-500" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">By Event</p>
                </div>
                <div class="mt-2 flex items-center gap-2">
                    @foreach($stats['by_action'] as $actionName => $count)
                        <span @class([
                            'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                            'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400' => $actionName === 'created',
                            'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' => $actionName === 'updated',
                            'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' => $actionName === 'deleted',
                            'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' => !in_array($actionName, ['created', 'updated', 'deleted']),
                        ])>
                            {{ $count }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="-mx-4 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                <tr>
                    <th scope="col" class="py-3 pl-4 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pl-6 lg:pl-8 dark:text-zinc-400">Date & Time</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">User</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Action</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Model</th>
                    <th scope="col" class="py-3 pl-4 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pr-6 lg:pr-8 dark:text-zinc-400">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse($activities as $activity)
                <tr 
                    wire:click="showDetail({{ $activity->id }})"
                    class="group cursor-pointer transition-all duration-150 hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                >
                    <td class="relative whitespace-nowrap py-4 pl-4 pr-4 sm:pl-6 lg:pl-8">
                        <div class="absolute inset-y-0 left-0 w-0.5 bg-transparent transition-all duration-150 group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700"></div>
                        <div class="text-sm text-zinc-900 dark:text-zinc-100">{{ \Carbon\Carbon::parse($activity->created_at)->format('M d, Y') }}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ \Carbon\Carbon::parse($activity->created_at)->format('H:i:s') }}</div>
                    </td>
                    <td class="px-4 py-4">
                        @if($activity->causer_name)
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ strtoupper(substr($activity->causer_name, 0, 2)) }}
                                </div>
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $activity->causer_name }}</span>
                            </div>
                        @else
                            <span class="text-sm text-zinc-400 dark:text-zinc-500">{{ $activity->user_name ?? 'System' }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        <span @class([
                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                            'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400' => $activity->action === 'created',
                            'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' => $activity->action === 'updated',
                            'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' => $activity->action === 'deleted',
                            'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' => !in_array($activity->action, ['created', 'updated', 'deleted']),
                        ])>
                            {{ ucfirst($activity->action ?? 'unknown') }}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        @if($activity->model_type)
                            <span class="rounded bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ $this->getModelName($activity->model_type) }}</span>
                            @if($activity->model_id)
                                <span class="ml-1 text-xs text-zinc-400">#{{ $activity->model_id }}</span>
                            @endif
                        @else
                            <span class="text-sm text-zinc-400">-</span>
                        @endif
                    </td>
                    <td class="py-4 pl-4 pr-4 text-sm text-zinc-900 sm:pr-6 lg:pr-8 dark:text-zinc-100">
                        {{ $activity->description }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon name="clipboard-document-list" class="size-6 text-zinc-400" />
                            </div>
                            <div>
                                <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No activity logs found</p>
                                <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Try adjusting your search or filters</p>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Detail Modal --}}
    @if($showDetailModal && $selectedActivity)
    <div
        x-data="{ open: true }"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[100] overflow-y-auto"
    >
        <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm" wire:click="closeDetailModal"></div>

        <div class="relative flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-2xl overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                    <div class="flex items-center gap-3">
                        <div @class([
                            'flex h-10 w-10 items-center justify-center rounded-full',
                            'bg-emerald-100 dark:bg-emerald-900/30' => $selectedActivity['action'] === 'created',
                            'bg-blue-100 dark:bg-blue-900/30' => $selectedActivity['action'] === 'updated',
                            'bg-red-100 dark:bg-red-900/30' => $selectedActivity['action'] === 'deleted',
                            'bg-zinc-100 dark:bg-zinc-800' => !in_array($selectedActivity['action'], ['created', 'updated', 'deleted']),
                        ])>
                            @if($selectedActivity['action'] === 'created')
                                <flux:icon name="plus-circle" class="size-5 text-emerald-600 dark:text-emerald-400" />
                            @elseif($selectedActivity['action'] === 'updated')
                                <flux:icon name="pencil-square" class="size-5 text-blue-600 dark:text-blue-400" />
                            @elseif($selectedActivity['action'] === 'deleted')
                                <flux:icon name="trash" class="size-5 text-red-600 dark:text-red-400" />
                            @else
                                <flux:icon name="information-circle" class="size-5 text-zinc-600 dark:text-zinc-400" />
                            @endif
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Activity Detail</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $selectedActivity['created_at'] }}</p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        wire:click="closeDetailModal"
                        class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                    >
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                {{-- Content --}}
                <div class="max-h-[60vh] overflow-y-auto px-6 py-4">
                    {{-- Basic Info --}}
                    <div class="mb-6 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Action</label>
                            <p class="mt-1">
                                <span @class([
                                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400' => $selectedActivity['action'] === 'created',
                                    'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' => $selectedActivity['action'] === 'updated',
                                    'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' => $selectedActivity['action'] === 'deleted',
                                    'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' => !in_array($selectedActivity['action'], ['created', 'updated', 'deleted']),
                                ])>
                                    {{ ucfirst($selectedActivity['action'] ?? 'unknown') }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Model</label>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $selectedActivity['model_type'] ?? '-' }}
                                @if($selectedActivity['model_id'])
                                    <span class="text-zinc-400">#{{ $selectedActivity['model_id'] }}</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">User</label>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $selectedActivity['causer_name'] }}</p>
                            @if($selectedActivity['causer_email'])
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $selectedActivity['causer_email'] }}</p>
                            @endif
                        </div>
                        <div>
                            <label class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Time</label>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $selectedActivity['created_at_diff'] }}</p>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="mb-6">
                        <label class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Description</label>
                        <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $selectedActivity['description'] ?? '-' }}</p>
                    </div>

                    {{-- Changes --}}
                    @if(!empty($selectedActivity['old_values']) || !empty($selectedActivity['new_values']))
                    <div class="mb-6">
                        <label class="mb-2 block text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Changes</label>
                        <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Field</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Old Value</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">New Value</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($selectedActivity['new_values'] as $field => $newValue)
                                        @php $oldValue = $selectedActivity['old_values'][$field] ?? null; @endphp
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ ucfirst(str_replace('_', ' ', $field)) }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-zinc-500 dark:text-zinc-400">
                                                <span class="line-through">{{ $this->formatValue($oldValue) }}</span>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $this->formatValue($newValue) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                    <button
                        type="button"
                        wire:click="closeDetailModal"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
