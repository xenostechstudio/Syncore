<div>
    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.employees.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    {{ __('hr.new_employee') }}
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">{{ __('hr.overview') }}</span>
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
                {{-- Recent Employees --}}
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Recent Employees</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Last 5 Employees</span>
                        <a href="{{ route('hr.employees.create') }}" wire:navigate class="rounded-md bg-zinc-900 px-2 py-1 text-xs font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            Add New
                        </a>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($recentEmployees as $employee)
                            <a href="{{ route('hr.employees.edit', $employee->id) }}" wire:navigate class="flex items-center justify-between px-4 py-2 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 odd:bg-white even:bg-zinc-50/50 dark:odd:bg-zinc-900 dark:even:bg-zinc-900/50">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $employee->initials }}
                                    </div>
                                    <div>
                                        <span class="text-sm font-light text-zinc-600 dark:text-zinc-300">{{ $employee->name }}</span>
                                        <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $employee->position?->name ?? '-' }}</p>
                                    </div>
                                </div>
                                <span class="text-xs text-zinc-400">{{ $employee->department?->name ?? '-' }}</span>
                            </a>
                        @empty
                            <div class="px-5 py-6 text-center text-sm font-light text-zinc-400">No employees yet</div>
                        @endforelse
                    </div>
                    <div class="border-t border-zinc-100 px-5 py-3 dark:border-zinc-800">
                        <a href="{{ route('hr.employees.index') }}" wire:navigate class="text-xs font-light text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                            View all employees →
                        </a>
                    </div>
                </div>

                {{-- Employee Statistics --}}
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Employee Status</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Active</span>
                            <span class="text-sm font-normal text-emerald-600 dark:text-emerald-400">{{ number_format($activeEmployees) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">On Leave</span>
                            <span class="text-sm font-normal text-amber-600 dark:text-amber-400">{{ number_format($onLeaveEmployees) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Inactive</span>
                            <span class="text-sm font-normal text-zinc-500 dark:text-zinc-400">{{ number_format($inactiveEmployees) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Terminated</span>
                            <span class="text-sm font-normal text-red-600 dark:text-red-400">{{ number_format($terminatedEmployees) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Leave Requests Statistics --}}
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Leave Requests</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Pending</span>
                            <span class="text-sm font-normal {{ $pendingLeaveRequests > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-500' }}">{{ number_format($pendingLeaveRequests) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Approved</span>
                            <span class="text-sm font-normal text-emerald-600 dark:text-emerald-400">{{ number_format($approvedLeaveRequests) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Rejected</span>
                            <span class="text-sm font-normal text-red-600 dark:text-red-400">{{ number_format($rejectedLeaveRequests) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">This Month</span>
                            <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ number_format($leaveRequestsThisMonth) }}</span>
                        </div>
                    </div>
                    <div class="border-t border-zinc-100 px-5 py-3 dark:border-zinc-800">
                        <a href="{{ route('hr.leave.requests.index') }}" wire:navigate class="text-xs font-light text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                            View all requests →
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6 lg:col-span-8">
                {{-- Overview Stats --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="users" class="size-4 text-blue-500" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Active Employees</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($totalEmployees) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Total workforce</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="building-office" class="size-4 text-violet-500" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Departments</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($totalDepartments) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Active departments</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="briefcase" class="size-4 text-emerald-500" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Positions</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($totalPositions) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Job positions</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="clock" class="size-4 text-amber-500" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Pending Leave</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold {{ $pendingLeaveRequests > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-900 dark:text-zinc-100' }}">{{ number_format($pendingLeaveRequests) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Awaiting approval</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="calendar-days" class="size-4 text-red-500" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">On Leave Today</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($onLeaveToday) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Employees absent</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="banknotes" class="size-4 text-emerald-500" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Payroll Pending</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold {{ $payrollPending > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-900 dark:text-zinc-100' }}">{{ number_format($payrollPending) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Runs to process</p>
                    </div>
                </div>

                {{-- Headcount Trend --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Headcount Trend</h2>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">Last 6 months</span>
                    </div>
                    <div class="p-5">
                        <div class="mb-4 grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Headcount</p>
                                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($totalEmployees) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">New This Month</p>
                                <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($newEmployeesThisMonth) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Last Month</p>
                                <p class="mt-1 text-2xl font-bold text-zinc-600 dark:text-zinc-400">{{ number_format($newEmployeesLastMonth) }}</p>
                            </div>
                        </div>
                        
                        {{-- Simple Bar Chart --}}
                        <div class="mt-6">
                            <div class="flex items-end justify-between gap-2 h-32">
                                @php
                                    $maxCount = $headcountTrend->max('count') ?: 1;
                                @endphp
                                @foreach($headcountTrend as $data)
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="w-full rounded-t relative" style="height: {{ max(($data['count'] / $maxCount) * 100, 5) }}%">
                                            <div class="absolute inset-0 bg-blue-500 dark:bg-blue-400 rounded-t opacity-80"></div>
                                        </div>
                                        <span class="text-[10px] text-zinc-500 dark:text-zinc-400">{{ $data['month'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Department Distribution --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Department Distribution</h2>
                        <a href="{{ route('hr.departments.index') }}" wire:navigate class="text-xs text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300">
                            View all →
                        </a>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($departmentDistribution as $department)
                            <div class="flex items-center gap-4 px-5 py-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-violet-100 text-sm font-normal text-violet-600 dark:bg-violet-900/30 dark:text-violet-400">
                                    {{ strtoupper(substr($department->name, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $department->name }}</p>
                                    <div class="mt-1 h-1.5 w-full rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        @php
                                            $maxEmployees = $departmentDistribution->max('employees_count') ?: 1;
                                            $percentage = ($department->employees_count / $maxEmployees) * 100;
                                        @endphp
                                        <div class="h-1.5 rounded-full bg-violet-500" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $department->employees_count }}</p>
                                    <p class="text-xs text-zinc-400">employees</p>
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center text-sm text-zinc-400">
                                No departments found
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Recent Leave Requests --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent Leave Requests</h2>
                        <a href="{{ route('hr.leave.requests.index') }}" wire:navigate class="text-xs text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300">
                            View all →
                        </a>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($recentLeaveRequests as $request)
                            <a href="{{ route('hr.leave.requests.edit', $request->id) }}" wire:navigate class="flex items-center gap-4 px-5 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $request->employee?->initials ?? '?' }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $request->employee?->name ?? '-' }}</p>
                                    <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $request->leaveType?->name ?? '-' }} · {{ $request->start_date?->format('M d') }} - {{ $request->end_date?->format('M d') }}</p>
                                </div>
                                <div class="text-right">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                            'approved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                            'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                            'cancelled' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-400',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColors[$request->status] ?? $statusColors['pending'] }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <div class="px-5 py-8 text-center text-sm text-zinc-400">
                                No leave requests found
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Upcoming Birthdays --}}
                @if($upcomingBirthdays->count() > 0)
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">🎂 Upcoming Birthdays</h2>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">Next 30 days</span>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($upcomingBirthdays as $employee)
                            @php
                                $birthday = $employee->birth_date->setYear(now()->year);
                                if ($birthday->isPast()) {
                                    $birthday = $birthday->addYear();
                                }
                                $daysUntil = now()->diffInDays($birthday, false);
                            @endphp
                            <div class="flex items-center gap-4 px-5 py-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-pink-100 text-sm font-normal text-pink-600 dark:bg-pink-900/30 dark:text-pink-400">
                                    {{ $employee->initials }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $employee->name }}</p>
                                    <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $employee->department?->name ?? '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $birthday->format('M d') }}</p>
                                    <p class="text-xs {{ $daysUntil <= 7 ? 'text-pink-600 dark:text-pink-400' : 'text-zinc-400' }}">
                                        @if($daysUntil == 0)
                                            Today! 🎉
                                        @elseif($daysUntil == 1)
                                            Tomorrow
                                        @else
                                            In {{ $daysUntil }} days
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Quick Actions --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Quick Actions</h2>
                    </div>
                    <div class="grid grid-cols-2 gap-2 p-4 sm:grid-cols-4">
                        <a href="{{ route('hr.employees.index') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                            <flux:icon name="users" class="size-6 text-zinc-400" />
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Employees</span>
                        </a>
                        <a href="{{ route('hr.departments.index') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                            <flux:icon name="building-office" class="size-6 text-zinc-400" />
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Departments</span>
                        </a>
                        <a href="{{ route('hr.leave.requests.index') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                            <flux:icon name="calendar-days" class="size-6 text-zinc-400" />
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Leave Requests</span>
                        </a>
                        <a href="{{ route('hr.payroll.index') }}" wire:navigate class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 text-center transition-colors hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                            <flux:icon name="banknotes" class="size-6 text-zinc-400" />
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Payroll</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
