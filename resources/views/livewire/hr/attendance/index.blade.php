<div>
    <x-ui.flash />

    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group --}}
            <div class="flex items-center gap-3">
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">{{ __('attendance.attendances') }}</span>

                {{-- Date Range --}}
                <div class="flex items-center gap-1.5">
                    <input type="date" wire:model.live="dateFrom" class="rounded-md border-zinc-300 bg-white px-2 py-1 text-xs text-zinc-700 shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    <span class="text-xs text-zinc-400">—</span>
                    <input type="date" wire:model.live="dateTo" class="rounded-md border-zinc-300 bg-white px-2 py-1 text-xs text-zinc-700 shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                </div>

                {{-- Actions Menu (Gear) --}}
                <flux:dropdown position="bottom" align="start">
                    <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="cog-6-tooth" class="size-5" />
                    </button>
                    <flux:menu class="w-48">
                        <button type="button" wire:click="exportSelected" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>{{ __('common.export') }}</span>
                        </button>
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Center Group: Search or Selection Toolbar --}}
            <div class="flex flex-1 items-center justify-center">
                @if(count($selected) > 0)
                    <div class="flex items-center gap-2 animate-in fade-in slide-in-from-top-2 duration-200">
                        <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <span>{{ count($selected) }} {{ __('common.selected') }}</span>
                            <flux:icon name="x-mark" class="size-3.5" />
                        </button>

                        <div class="h-5 w-px bg-zinc-200 dark:bg-zinc-700"></div>

                        <button wire:click="exportSelected" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>{{ __('common.export') }}</span>
                        </button>

                        <flux:dropdown position="bottom" align="center">
                            <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-2 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                <flux:icon name="ellipsis-horizontal" class="size-4" />
                            </button>
                            <flux:menu class="w-40">
                                <button type="button" wire:click="confirmBulkDelete" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <flux:icon name="trash" class="size-4" />
                                    <span>{{ __('common.delete') }}</span>
                                </button>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @else
                    <x-ui.searchbox-dropdown placeholder="{{ __('common.search') }}..." widthClass="w-[520px]" width="520px">
                        <div class="flex flex-col gap-4 p-3 md:flex-row">
                            {{-- Filters column --}}
                            <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:pr-3 dark:border-zinc-700">
                                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="funnel" class="size-3.5" />
                                    <span>{{ __('attendance.status') }}</span>
                                </div>
                                <div class="space-y-1">
                                    <button type="button" wire:click="$set('status', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>{{ __('common.all_statuses') }}</span>
                                        @if(empty($status))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    @foreach(['present' => 'emerald', 'late' => 'amber', 'absent' => 'red', 'half_day' => 'orange', 'on_leave' => 'blue', 'weekend' => 'zinc', 'holiday' => 'violet'] as $key => $color)
                                        <button type="button" wire:click="$set('status', '{{ $key }}')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <div class="flex items-center gap-2">
                                                <span class="h-1.5 w-1.5 rounded-full bg-{{ $color }}-500"></span>
                                                <span>{{ __('attendance.' . $key) }}</span>
                                            </div>
                                            @if($status === $key)<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Sort column --}}
                            <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:px-3 dark:border-zinc-700">
                                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="arrows-up-down" class="size-3.5" />
                                    <span>{{ __('common.sort_by') }}</span>
                                </div>
                                <div class="space-y-1">
                                    @foreach(['latest' => __('common.latest'), 'oldest' => __('common.oldest'), 'late_high' => __('attendance.late_minutes') . ' ↓', 'duration_high' => __('attendance.work_duration_minutes') . ' ↓'] as $key => $label)
                                        <button type="button" wire:click="$set('sort', '{{ $key }}')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <span>{{ $label }}</span>
                                            @if($sort === $key)<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Employee column --}}
                            <div class="flex-1 md:pl-3">
                                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="user" class="size-3.5" />
                                    <span>{{ __('common.employee') }}</span>
                                </div>
                                <div class="space-y-1 max-h-48 overflow-y-auto">
                                    <button type="button" wire:click="$set('employeeId', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>{{ __('common.all_employees') }}</span>
                                        @if(empty($employeeId))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    @foreach($employees as $emp)
                                        <button type="button" wire:click="$set('employeeId', '{{ $emp->id }}')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <span>{{ $emp->name }}</span>
                                            @if($employeeId == $emp->id)<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </x-ui.searchbox-dropdown>
                @endif
            </div>

            {{-- Right Group --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $attendances->firstItem() ?? 0 }}-{{ $attendances->lastItem() ?? 0 }}/{{ $attendances->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button type="button" wire:click="goToPreviousPage" @disabled($attendances->onFirstPage()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button type="button" wire:click="goToNextPage" @disabled(!$attendances->hasMorePages()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>
                <div class="flex h-9 items-center rounded-lg border border-zinc-200 p-0.5 dark:border-zinc-700">
                    <button type="button" wire:click="toggleStats" class="{{ $showStats ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300' }} rounded-md p-1.5 transition-colors">
                        <flux:icon name="chart-bar" class="size-[18px]" />
                    </button>
                </div>
                <x-ui.view-toggle :view="$view" :views="['list']" />
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    @if($showStats && $statistics && !$attendances->isEmpty())
        <div class="-mx-4 -mt-6 mb-6 border-b border-zinc-200 bg-white px-4 py-4 sm:-mx-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-950">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="clipboard-document-list" class="size-4 text-zinc-400 dark:text-zinc-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">{{ __('attendance.total_days') }}</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['total']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="check-circle" class="size-4 text-emerald-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">{{ __('attendance.present') }}</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['present']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="clock" class="size-4 text-amber-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">{{ __('attendance.late') }}</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['late']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="x-circle" class="size-4 text-red-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">{{ __('attendance.absent') }}</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['absent']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="minus-circle" class="size-4 text-orange-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">{{ __('attendance.half_day') }}</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['half_day']) }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Content --}}
    @if($attendances->isEmpty())
        <div class="-mx-4 -mt-6 -mb-6 flex min-h-[70vh] items-center justify-center bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
            <div class="-mt-16 flex flex-col items-center gap-4 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="clock" class="size-8 text-zinc-400" />
                </div>
                <div>
                    <p class="text-base font-medium text-zinc-900 dark:text-zinc-100">{{ __('common.no_records_found') }}</p>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('attendance.no_attendance_hint') }}</p>
                </div>
            </div>
        </div>
    @else
        <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                    <tr>
                        <th scope="col" class="w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600">
                        </th>
                        <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('common.employee') }}</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('attendance.date') }}</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('attendance.check_in') }}</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('attendance.check_out') }}</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('attendance.status') }}</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('attendance.late_minutes') }}</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('attendance.work_duration_minutes') }}</th>
                        <th scope="col" class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @foreach($attendances as $attendance)
                        @php $isSelected = in_array($attendance->id, $selected); @endphp
                        <tr wire:key="att-{{ $attendance->id }}" class="group transition-all duration-150 {{ $isSelected ? 'bg-zinc-900/[0.03] dark:bg-zinc-100/[0.03]' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                            <td class="relative py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                                <div class="absolute inset-y-0 left-0 w-0.5 transition-all duration-150 {{ $isSelected ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-transparent group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700' }}"></div>
                                <input type="checkbox" wire:model.live="selected" value="{{ $attendance->id }}" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600 {{ $isSelected ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-100/20' : '' }}">
                            </td>
                            <td class="py-3 pl-2 pr-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $attendance->employee->initials ?? '?' }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $attendance->employee->name }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $attendance->workSchedule?->name ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $attendance->date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $attendance->check_in_time ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $attendance->check_out_time ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusConfig = match($attendance->status) {
                                        'present' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400'],
                                        'late' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400'],
                                        'absent' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400'],
                                        'half_day' => ['bg' => 'bg-orange-100 dark:bg-orange-900/30', 'text' => 'text-orange-700 dark:text-orange-400'],
                                        'on_leave' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400'],
                                        default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                    {{ __('attendance.' . $attendance->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                @if($attendance->late_minutes > 0)
                                    <span class="text-amber-600 dark:text-amber-400">{{ $attendance->late_minutes }} min</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                @if($attendance->work_duration_minutes > 0)
                                    {{ floor($attendance->work_duration_minutes / 60) }}h {{ $attendance->work_duration_minutes % 60 }}m
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3 pr-4 sm:pr-6 lg:pr-8"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteConfirm)
        <x-ui.delete-confirm-modal :validation="$deleteValidation" />
    @endif
</div>
