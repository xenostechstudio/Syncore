<div>
    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.employees.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New Employee
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">HR Overview</span>
            </div>
            <div class="flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                <flux:icon name="calendar" class="size-4" />
                <span>{{ now()->format('F Y') }}</span>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column --}}
            <div class="space-y-6 lg:col-span-4 lg:sticky lg:top-20 lg:h-fit">
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Quick Actions</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <a href="{{ route('hr.employees.index') }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <flux:icon name="users" class="size-4 text-zinc-400" />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">Employees</span>
                            </div>
                            <flux:icon name="chevron-right" class="size-4 text-zinc-400" />
                        </a>
                        <a href="{{ route('hr.departments.index') }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <flux:icon name="building-office" class="size-4 text-zinc-400" />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">Departments</span>
                            </div>
                            <flux:icon name="chevron-right" class="size-4 text-zinc-400" />
                        </a>
                        <a href="{{ route('hr.positions.index') }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <flux:icon name="briefcase" class="size-4 text-zinc-400" />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">Positions</span>
                            </div>
                            <flux:icon name="chevron-right" class="size-4 text-zinc-400" />
                        </a>
                        <a href="{{ route('hr.leave.requests.index') }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <flux:icon name="calendar-days" class="size-4 text-zinc-400" />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">Leave Requests</span>
                            </div>
                            <flux:icon name="chevron-right" class="size-4 text-zinc-400" />
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6 lg:col-span-8">
                {{-- Summary Cards --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="users" class="size-4 text-blue-500" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Active Employees</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($totalEmployees) }}</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="building-office" class="size-4 text-violet-500" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Departments</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($totalDepartments) }}</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="clock" class="size-4 text-amber-500" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Pending Leave</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-amber-600 dark:text-amber-400">{{ number_format($pendingLeaveRequests) }}</p>
                    </div>
                </div>

                {{-- Recent Employees --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent Employees</h2>
                        <a href="{{ route('hr.employees.index') }}" wire:navigate class="text-xs text-zinc-500 hover:text-zinc-700 dark:text-zinc-400">View all →</a>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($recentEmployees as $employee)
                            <a href="{{ route('hr.employees.edit', $employee->id) }}" wire:navigate class="flex items-center justify-between px-5 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-sm font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ strtoupper(substr($employee->first_name, 0, 1)) }}{{ strtoupper(substr($employee->last_name ?? '', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $employee->full_name }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $employee->position?->name ?? '-' }} • {{ $employee->department?->name ?? '-' }}</p>
                                    </div>
                                </div>
                                <span class="text-xs text-zinc-400">{{ $employee->employee_code }}</span>
                            </a>
                        @empty
                            <div class="px-5 py-8 text-center text-sm text-zinc-400">No employees yet</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
