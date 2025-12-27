<div>
    <x-slot:header>
        <div class="flex items-center gap-3">
            @if($search || $logName || $event || $causerId || $dateFrom || $dateTo)
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
                        @if($logName || $event || $causerId || $dateFrom || $dateTo)
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-zinc-900 text-[10px] font-medium text-white dark:bg-zinc-100 dark:text-zinc-900">
                                {{ collect([$logName, $event, $causerId, $dateFrom, $dateTo])->filter()->count() }}
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
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Log Type</label>
                                    <select 
                                        wire:model.live="logName" 
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                        <option value="">All</option>
                                        @foreach($logNames as $name)
                                            <option value="{{ $name }}">{{ ucfirst($name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Event</label>
                                    <select 
                                        wire:model.live="event" 
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                        <option value="">All</option>
                                        @foreach($events as $evt)
                                            <option value="{{ $evt }}">{{ ucfirst($evt) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">User</label>
                                <select 
                                    wire:model.live="causerId" 
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
                        @if($logName || $event || $causerId || $dateFrom || $dateTo)
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

    {{-- Table --}}
    <div class="-mx-4 -mt-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                <tr>
                    <th scope="col" class="py-3 pl-4 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pl-6 lg:pl-8 dark:text-zinc-400">Date & Time</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">User</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Event</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Model</th>
                    <th scope="col" class="py-3 pl-4 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pr-6 lg:pr-8 dark:text-zinc-400">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse($activities as $activity)
                <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                    <td class="whitespace-nowrap py-4 pl-4 pr-4 sm:pl-6 lg:pl-8">
                        <div class="text-sm text-zinc-900 dark:text-zinc-100">{{ $activity->created_at->format('M d, Y') }}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $activity->created_at->format('H:i:s') }}</div>
                    </td>
                    <td class="px-4 py-4">
                        @if($activity->causer)
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ strtoupper(substr($activity->causer->name, 0, 2)) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $activity->causer->name }}</span>
                                </div>
                            </div>
                        @else
                            <span class="text-sm text-zinc-400 dark:text-zinc-500">System</span>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        <span @class([
                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                            'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400' => $activity->event === 'created',
                            'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' => $activity->event === 'updated',
                            'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' => $activity->event === 'deleted',
                            'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' => !in_array($activity->event, ['created', 'updated', 'deleted']),
                        ])>
                            {{ ucfirst($activity->event ?? 'unknown') }}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        @if($activity->subject_type)
                            <span class="rounded bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ $this->getModelName($activity->subject_type) }}</span>
                            @if($activity->subject_id)
                                <span class="ml-1 text-xs text-zinc-400">#{{ $activity->subject_id }}</span>
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
</div>
