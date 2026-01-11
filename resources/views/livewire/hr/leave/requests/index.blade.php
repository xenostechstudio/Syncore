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
            {{-- Left Group: New Button + Title --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.leave.requests.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Leave Requests</span>
                
                {{-- Actions Menu (Gear) --}}
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

            {{-- Center Group: Search or Selection Toolbar --}}
            <div class="flex flex-1 items-center justify-center">
                @if(count($selected) > 0)
                    {{-- Selection Toolbar --}}
                    <div class="flex items-center gap-2 animate-in fade-in slide-in-from-top-2 duration-200">
                        {{-- Count Selected Button --}}
                        <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <span>{{ count($selected) }} selected</span>
                            <flux:icon name="x-mark" class="size-3.5" />
                        </button>

                        <div class="h-5 w-px bg-zinc-200 dark:bg-zinc-700"></div>

                        {{-- Export --}}
                        <button wire:click="exportSelected" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>Export</span>
                        </button>

                        {{-- Approve --}}
                        <button wire:click="approveSelected" wire:confirm="Approve selected leave requests?" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <flux:icon name="check" class="size-4 text-emerald-500" />
                            <span>Approve</span>
                        </button>

                        {{-- Reject --}}
                        <button wire:click="rejectSelected" wire:confirm="Reject selected leave requests?" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <flux:icon name="x-mark" class="size-4 text-red-500" />
                            <span>Reject</span>
                        </button>

                        {{-- Actions Dropdown --}}
                        <flux:dropdown position="bottom" align="center">
                            <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-2 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                <flux:icon name="ellipsis-horizontal" class="size-4" />
                            </button>
                            <flux:menu class="w-40">
                                <button type="button" wire:click="confirmBulkDelete" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <flux:icon name="trash" class="size-4" />
                                    <span>Delete</span>
                                </button>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @else
                    {{-- Search --}}
                    <x-ui.searchbox-dropdown placeholder="Search leave requests..." widthClass="w-[480px]" width="480px">
                        <div class="flex flex-col gap-4 p-3 md:flex-row">
                            {{-- Filters column --}}
                            <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:pr-3 dark:border-zinc-700">
                                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="funnel" class="size-3.5" />
                                    <span>Status</span>
                                </div>
                                <div class="space-y-1">
                                    <button type="button" wire:click="$set('status', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>All Status</span>
                                        @if(empty($status))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'draft')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-zinc-500"></span>
                                            <span>Draft</span>
                                        </div>
                                        @if($status === 'draft')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'pending')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            <span>Pending</span>
                                        </div>
                                        @if($status === 'pending')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'approved')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Approved</span>
                                        </div>
                                        @if($status === 'approved')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'rejected')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                            <span>Rejected</span>
                                        </div>
                                        @if($status === 'rejected')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'cancelled')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span>
                                            <span>Cancelled</span>
                                        </div>
                                        @if($status === 'cancelled')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
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
                                    <button type="button" wire:click="$set('sort', 'days_high')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>Days: High to Low</span>
                                        @if($sort === 'days_high')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('sort', 'days_low')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>Days: Low to High</span>
                                        @if($sort === 'days_low')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                </div>
                            </div>

                            {{-- Leave Type column --}}
                            <div class="flex-1 md:pl-3">
                                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="tag" class="size-3.5" />
                                    <span>Leave Type</span>
                                </div>
                                <div class="space-y-1">
                                    <button type="button" wire:click="$set('leaveTypeId', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>All Types</span>
                                        @if(empty($leaveTypeId))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    @foreach($leaveTypes as $type)
                                        <button type="button" wire:click="$set('leaveTypeId', '{{ $type->id }}')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <span>{{ $type->name }}</span>
                                            @if($leaveTypeId == $type->id)<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </x-ui.searchbox-dropdown>
                @endif
            </div>

            {{-- Right Group: Pagination Info + View Toggle --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $requests->firstItem() ?? 0 }}-{{ $requests->lastItem() ?? 0 }}/{{ $requests->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button 
                            type="button"
                            wire:click="goToPreviousPage"
                            @disabled($requests->onFirstPage())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button 
                            type="button"
                            wire:click="goToNextPage"
                            @disabled(!$requests->hasMorePages())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>
                <x-ui.view-toggle :view="$view" :views="['list', 'grid', 'kanban']" />
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div>
        @if($requests->isEmpty())
            {{-- Empty State --}}
            <div class="-mx-4 -mt-6 -mb-6 flex min-h-[70vh] items-center justify-center bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                <div class="-mt-16 flex flex-col items-center gap-4 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="calendar-days" class="size-8 text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-base font-medium text-zinc-900 dark:text-zinc-100">No leave requests found</p>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Get started by creating a new leave request</p>
                    </div>
                    <a href="{{ route('hr.leave.requests.create') }}" wire:navigate class="mt-2 inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="plus" class="size-4" />
                        New Leave Request
                    </a>
                </div>
            </div>
        @else
            @if($view === 'list')
                {{-- List View --}}
                <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                            <tr>
                                <th scope="col" class="w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selectAll"
                                        class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600"
                                    >
                                </th>
                                <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Employee</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Leave Type</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Period</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Days</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                                <th scope="col" class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($requests as $request)
                                @php $isSelected = in_array($request->id, $selected); @endphp
                                <tr 
                                    wire:key="lr-{{ $request->id }}" 
                                    onclick="window.location.href='{{ route('hr.leave.requests.edit', $request->id) }}'"
                                    class="group cursor-pointer transition-all duration-150 {{ $isSelected ? 'bg-zinc-900/[0.03] dark:bg-zinc-100/[0.03]' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}"
                                >
                                    <td class="relative py-3 pl-4 pr-2 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                        <div class="absolute inset-y-0 left-0 w-0.5 transition-all duration-150 {{ $isSelected ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-transparent group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700' }}"></div>
                                        <input 
                                            type="checkbox" 
                                            wire:model.live="selected"
                                            value="{{ $request->id }}"
                                            class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600 {{ $isSelected ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-100/20' : '' }}"
                                        >
                                    </td>
                                    <td class="py-3 pl-2 pr-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                                {{ $request->employee->initials }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $request->employee->name }}</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $request->employee->position?->name ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $request->leaveType->name }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ $request->start_date->format('M d') }} — {{ $request->end_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $request->days }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusConfig = match($request->status) {
                                                'draft' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                                'pending' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400'],
                                                'approved' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400'],
                                                'rejected' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400'],
                                                'cancelled' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                                default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                            };
                                        @endphp
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 pr-4 sm:pr-6 lg:pr-8"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif($view === 'grid')
                {{-- Grid View (Simplified) --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($requests as $request)
                        @php
                            $statusConfig = match($request->status) {
                                'draft' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                'pending' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400'],
                                'approved' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400'],
                                'rejected' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400'],
                                'cancelled' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                            };
                        @endphp
                        <a 
                            href="{{ route('hr.leave.requests.edit', $request->id) }}"
                            wire:navigate
                            class="group rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $request->employee->initials }}
                                    </div>
                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $request->employee->name }}</span>
                                </div>
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </div>
                            <div class="mt-3 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                <span>{{ $request->leaveType->name }}</span>
                                <span>{{ $request->days }} day(s)</span>
                            </div>
                            <div class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                                {{ $request->start_date->format('M d') }} — {{ $request->end_date->format('M d, Y') }}
                            </div>
                        </a>
                    @endforeach
                </div>
            @elseif($view === 'kanban')
                {{-- Kanban View --}}
                @php
                    $statuses = [
                        'draft' => ['label' => 'Draft', 'color' => 'zinc', 'headerBg' => 'bg-zinc-100 dark:bg-zinc-800'],
                        'pending' => ['label' => 'Pending', 'color' => 'amber', 'headerBg' => 'bg-amber-50 dark:bg-amber-900/20'],
                        'approved' => ['label' => 'Approved', 'color' => 'emerald', 'headerBg' => 'bg-emerald-50 dark:bg-emerald-900/20'],
                        'rejected' => ['label' => 'Rejected', 'color' => 'red', 'headerBg' => 'bg-red-50 dark:bg-red-900/20'],
                        'cancelled' => ['label' => 'Cancelled', 'color' => 'zinc', 'headerBg' => 'bg-zinc-100 dark:bg-zinc-800'],
                    ];
                    $requestsByStatus = $requests->groupBy('status');
                @endphp
                <div class="flex gap-4 overflow-x-auto pb-4">
                    @foreach($statuses as $statusKey => $statusInfo)
                        <div class="flex w-72 flex-shrink-0 flex-col rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/50">
                            {{-- Column Header --}}
                            <div class="flex items-center justify-between rounded-t-lg {{ $statusInfo['headerBg'] }} px-3 py-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-{{ $statusInfo['color'] }}-500"></span>
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $statusInfo['label'] }}</span>
                                    <span class="rounded-full bg-white px-1.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                        {{ $requestsByStatus->get($statusKey)?->count() ?? 0 }}
                                    </span>
                                </div>
                            </div>

                            {{-- Column Cards --}}
                            <div class="flex flex-1 flex-col gap-2 p-2">
                                @forelse($requestsByStatus->get($statusKey, collect()) as $request)
                                    <a 
                                        href="{{ route('hr.leave.requests.edit', $request->id) }}"
                                        wire:navigate
                                        class="rounded-lg border border-zinc-200 bg-white p-3 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                    >
                                        <div class="mb-2 flex items-center gap-2">
                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                {{ $request->employee->initials }}
                                            </div>
                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $request->employee->name }}</span>
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $request->leaveType->name }}</div>
                                        <div class="mt-2 flex items-center justify-between text-xs">
                                            <span class="text-zinc-400 dark:text-zinc-500">{{ $request->start_date->format('M d') }} — {{ $request->end_date->format('M d') }}</span>
                                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $request->days }}d</span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="flex flex-1 items-center justify-center py-8">
                                        <p class="text-xs text-zinc-400 dark:text-zinc-500">No requests</p>
                                    </div>
                                @endforelse
                            </div>

                            {{-- Column Footer --}}
                            @if($requestsByStatus->get($statusKey)?->count() > 0)
                                <div class="border-t border-zinc-200 px-3 py-2 dark:border-zinc-700">
                                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                        {{ $requestsByStatus->get($statusKey)->sum('days') }} day(s)
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @isset($showDeleteConfirm)
        <x-ui.delete-confirm-modal 
            wire:model="showDeleteConfirm"
            :validation="$deleteValidation ?? []"
            title="Confirm Delete"
            itemLabel="leave requests"
        />
    @endisset
</div>
