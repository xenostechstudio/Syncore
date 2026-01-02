<div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
    <div class="grid gap-6 lg:grid-cols-12">
        <div class="lg:col-span-9">
            <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="p-5">
                    <h1 class="mb-5 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $requestId ? 'LR-' . str_pad($requestId, 5, '0', STR_PAD_LEFT) : 'New Leave Request' }}</h1>
                    <div class="grid gap-6 sm:grid-cols-2">
                        {{-- Employee Selection --}}
                        <div>
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Employee <span class="text-red-500">*</span></label>
                            @if($requestId && !in_array($status, ['draft', 'pending']))
                                <div class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-left text-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                                    @if($this->selectedEmployee)
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ $this->selectedEmployee->initials }}</div>
                                            <div>
                                                <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $this->selectedEmployee->name }}</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $this->selectedEmployee->position?->name ?? 'No position' }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-zinc-400">No employee selected</span>
                                    @endif
                                    <flux:icon name="lock-closed" class="size-4 text-zinc-400" />
                                </div>
                            @else
                                <div class="relative" x-data="{ open: false, search: '' }">
                                    <button type="button" @click="open = !open; $nextTick(() => { if(open) $refs.empSearch.focus() })" class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-left text-sm transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600">
                                        @if($this->selectedEmployee)
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ $this->selectedEmployee->initials }}</div>
                                                <div>
                                                    <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $this->selectedEmployee->name }}</p>
                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $this->selectedEmployee->position?->name ?? 'No position' }}</p>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-zinc-400">Select an employee...</span>
                                        @endif
                                        <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                    </button>
                                    <div x-show="open" @click.outside="open = false; search = ''" x-transition class="absolute left-0 top-full z-[100] mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                        <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                            <input type="text" x-ref="empSearch" x-model="search" placeholder="Search employees..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" @keydown.escape="open = false; search = ''" />
                                        </div>
                                        <div class="max-h-60 overflow-auto py-1">
                                            @foreach($employees as $emp)
                                                <button type="button" x-show="search === '' || '{{ strtolower($emp->name) }}'.includes(search.toLowerCase())" wire:click="$set('employeeId', {{ $emp->id }})" @click="open = false; search = ''" class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $employeeId == $emp->id ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}">
                                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ $emp->initials }}</div>
                                                    <div><p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $emp->name }}</p><p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $emp->position?->name ?? 'No position' }} • {{ $emp->department?->name ?? 'No department' }}</p></div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @error('employeeId')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        {{-- Right Column Fields --}}
                        <div class="space-y-3">
                            <div class="flex items-center gap-4" x-data="{ open: false }">
                                <label class="w-24 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Leave Type <span class="text-red-500">*</span></label>
                                @if($requestId && !in_array($status, ['draft', 'pending']))
                                    <div class="flex-1 px-3 py-1.5 text-sm text-zinc-900 dark:text-zinc-100">{{ $leaveTypes->firstWhere('id', $leaveTypeId)?->name ?? '-' }}</div>
                                @else
                                    <div class="relative flex-1">
                                        <button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-left text-sm transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:hover:border-zinc-700">
                                            <span class="{{ $leaveTypeId ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400' }}">{{ $leaveTypes->firstWhere('id', $leaveTypeId)?->name ?? 'Select leave type...' }}</span>
                                            <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                        </button>
                                        <div x-show="open" @click.outside="open = false" x-transition class="absolute left-0 top-full z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                            @foreach($leaveTypes as $type)
                                                <button type="button" wire:click="$set('leaveTypeId', {{ $type->id }})" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">{{ $type->name }} <span class="text-xs text-zinc-400">({{ $type->days_per_year }} days/year)</span></button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @error('leaveTypeId')<p class="ml-28 text-xs text-red-500">{{ $message }}</p>@enderror

                            <div class="flex items-center gap-4">
                                <label class="w-24 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Date <span class="text-red-500">*</span></label>
                                @if($requestId && !in_array($status, ['draft', 'pending']))
                                    <div class="flex-1 px-3 py-1.5 text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}
                                        <span class="text-zinc-400 mx-1">—</span>
                                        {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                                    </div>
                                @else
                                    <div class="flex flex-1 items-center gap-2">
                                        <input type="date" wire:model.live="startDate" class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700" />
                                        <span class="text-zinc-400">—</span>
                                        <input type="date" wire:model.live="endDate" class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700" />
                                    </div>
                                @endif
                            </div>
                            @error('startDate')<p class="ml-28 text-xs text-red-500">{{ $message }}</p>@enderror
                            @error('endDate')<p class="ml-28 text-xs text-red-500">{{ $message }}</p>@enderror

                            <div class="flex items-center gap-4">
                                <label class="w-24 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Days</label>
                                @if($requestId && !in_array($status, ['draft', 'pending']))
                                    <div class="flex-1 px-3 py-1.5 text-sm text-zinc-900 dark:text-zinc-100">{{ $days }} day(s)</div>
                                @else
                                    <input type="number" wire:model="days" step="0.5" min="0.5" class="flex-1 rounded-lg border border-transparent bg-zinc-50 px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-700" />
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-zinc-200 p-5 dark:border-zinc-800">
                    <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Reason for Leave</label>
                    @if($requestId && !in_array($status, ['draft', 'pending']))
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100">{{ $reason ?: 'No reason provided' }}</div>
                    @else
                        <textarea wire:model="reason" rows="3" placeholder="Describe the reason for your leave request..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                    @endif
                </div>

                @if($requestId && $leaveRequest && $leaveRequest->approved_at)
                    <div class="border-t border-zinc-200 p-5 dark:border-zinc-800">
                        <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800">
                            <div class="flex items-center gap-2 text-sm">
                                @if($leaveRequest->status === 'approved')
                                    <flux:icon name="check-circle" class="size-5 text-emerald-500" /><span class="font-medium text-emerald-700 dark:text-emerald-400">Approved</span>
                                @else
                                    <flux:icon name="x-circle" class="size-5 text-red-500" /><span class="font-medium text-red-700 dark:text-red-400">Rejected</span>
                                @endif
                                <span class="text-zinc-600 dark:text-zinc-400">by</span>
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $leaveRequest->approver?->name ?? 'Unknown' }}</span>
                                <span class="text-zinc-600 dark:text-zinc-400">on {{ $leaveRequest->approved_at->format('M d, Y H:i') }}</span>
                            </div>
                            @if($leaveRequest->rejection_reason)<p class="mt-2 text-sm text-red-600 dark:text-red-400">Reason: {{ $leaveRequest->rejection_reason }}</p>@endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Activity Column --}}
        <div class="lg:col-span-3">
            <x-ui.chatter-forms :showMessage="false" />

            @if($requestId)
                <x-ui.activity-timeline 
                    :activities="$activities" 
                    emptyMessage="Leave request created"
                    :createdAt="$leaveRequest?->created_at?->format('H:i')"
                />
            @else
                <div class="flex items-center gap-3 py-2">
                    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Activity</span>
                    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                </div>
                <div class="py-8 text-center">
                    <flux:icon name="clock" class="mx-auto size-8 text-zinc-300 dark:text-zinc-600" />
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">No activity yet</p>
                </div>
            @endif
        </div>
    </div>
</div>
