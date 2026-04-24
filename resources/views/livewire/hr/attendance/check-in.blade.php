<div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex items-center gap-2 mb-4">
        <flux:icon name="clock" class="size-5 text-zinc-400" />
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('attendance.attendance') }}</h3>
    </div>

    {{-- Flash Messages --}}
    @if(session()->has('success'))
        <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-xs text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700 dark:bg-red-900/20 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    {{-- Today's Status --}}
    @if($todayAttendance)
        <div class="mb-4 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('attendance.check_in_time') }}</span>
                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $todayAttendance->check_in_time ?? '-' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('attendance.check_out_time') }}</span>
                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $todayAttendance->check_out_time ?? '-' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('attendance.status') }}</span>
                @php
                    $statusConfig = match($todayAttendance->status) {
                        'present' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400'],
                        'late' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400'],
                        'absent' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400'],
                        default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                    };
                @endphp
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                    {{ __('attendance.' . $todayAttendance->status) }}
                </span>
            </div>
            @if($todayAttendance->work_duration_minutes > 0)
                <div class="flex items-center justify-between">
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('attendance.work_duration_minutes') }}</span>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                        {{ floor($todayAttendance->work_duration_minutes / 60) }}h {{ $todayAttendance->work_duration_minutes % 60 }}m
                    </span>
                </div>
            @endif
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex gap-2">
        @if($canCheckIn)
            <button wire:click="openCheckInModal" class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-zinc-900 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                <flux:icon name="arrow-right-end-on-rectangle" class="size-4" />
                {{ __('attendance.check_in') }}
            </button>
        @endif
        @if($canCheckOut)
            <button wire:click="openCheckOutModal" class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600">
                <flux:icon name="arrow-left-start-on-rectangle" class="size-4" />
                {{ __('attendance.check_out') }}
            </button>
        @endif
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-zinc-900/50 transition-opacity dark:bg-zinc-950/70" wire:click="closeModal"></div>
            <div class="relative w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $action === 'check_in' ? __('attendance.check_in') : __('attendance.check_out') }}
                    </h3>
                    <button wire:click="closeModal" class="rounded-md p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('attendance.location') }}</label>
                        <input type="text" wire:model="location" placeholder="{{ __('common.optional') }}" class="mt-1 block w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('attendance.notes') }}</label>
                        <textarea wire:model="notes" rows="3" placeholder="{{ __('common.optional') }}" class="mt-1 block w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="closeModal" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                        {{ __('common.cancel') }}
                    </button>
                    <button wire:click="submit" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        {{ __('common.confirm') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
