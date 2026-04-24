<div>
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.attendance.schedules.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('attendance.work_schedule') }}</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $scheduleId ? $name : __('attendance.create_schedule') }}</span>
                        @if($scheduleId)
                            <flux:dropdown position="bottom" align="start">
                                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="cog-6-tooth" class="size-4" />
                                </button>
                                <flux:menu class="w-40">
                                    <button type="button" wire:click="delete" wire:confirm="{{ __('common.are_you_sure') }}" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                        <flux:icon name="trash" class="size-4" />
                                        <span>{{ __('common.delete') }}</span>
                                    </button>
                                </flux:menu>
                            </flux:dropdown>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-slot:header>

    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert type="error" :duration="5000">{{ session('error') }}</x-ui.alert>
        @endif
        @if($errors->any())
            <x-ui.alert type="error" :duration="10000">
                <span class="font-medium">{{ __('common.please_fix_errors') }}</span>
                <ul class="mt-1 list-inside list-disc text-xs">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </x-ui.alert>
        @endif
    </div>

    {{-- Action Buttons Bar --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="document-check" wire:loading.remove wire:target="save" class="size-4" />
                        <flux:icon name="arrow-path" wire:loading wire:target="save" class="size-4 animate-spin" />
                        <span wire:loading.remove wire:target="save">{{ __('common.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('common.saving') }}</span>
                    </button>
                    <a href="{{ route('hr.attendance.schedules.index') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="x-mark" class="size-4" />
                        {{ __('common.cancel') }}
                    </a>
                </div>
                <div class="hidden items-center lg:flex">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                        @if($is_active)
                            <flux:icon name="check-circle" class="mr-1 size-3" />
                        @endif
                        {{ $is_active ? __('common.active') : __('common.inactive') }}
                    </span>
                </div>
            </div>
            <div class="col-span-3"></div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        {{-- Basic Info --}}
                        <div class="grid grid-cols-1 gap-x-6 gap-y-4 p-6 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('attendance.name') }} <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="name" class="mt-1 block w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('attendance.code') }} <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="code" class="mt-1 block w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            </div>
                            <div class="flex items-end gap-4">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model="is_active" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800">
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('attendance.is_active') }}</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model="is_flexible" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800">
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('attendance.is_flexible') }}</span>
                                </label>
                            </div>
                        </div>

                        {{-- Time Settings --}}
                        <div class="grid grid-cols-1 gap-x-6 gap-y-4 p-6 md:grid-cols-4">
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('attendance.start_time') }} <span class="text-red-500">*</span></label>
                                <input type="time" wire:model="start_time" class="mt-1 block w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('attendance.end_time') }} <span class="text-red-500">*</span></label>
                                <input type="time" wire:model="end_time" class="mt-1 block w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('attendance.break_duration') }} <span class="text-red-500">*</span></label>
                                <input type="number" wire:model="break_duration" min="0" class="mt-1 block w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('attendance.grace_period_minutes') }} <span class="text-red-500">*</span></label>
                                <input type="number" wire:model="grace_period_minutes" min="0" class="mt-1 block w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            </div>
                        </div>

                        {{-- Work Days --}}
                        <div class="p-6">
                            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-3">{{ __('attendance.work_days') }} <span class="text-red-500">*</span></label>
                            <div class="flex flex-wrap gap-2">
                                @php
                                    $dayLabels = [
                                        1 => __('attendance.monday'),
                                        2 => __('attendance.tuesday'),
                                        3 => __('attendance.wednesday'),
                                        4 => __('attendance.thursday'),
                                        5 => __('attendance.friday'),
                                        6 => __('attendance.saturday'),
                                        7 => __('attendance.sunday'),
                                    ];
                                @endphp
                                @foreach($dayLabels as $value => $label)
                                    <label class="flex items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800 {{ in_array($value, $work_days) ? 'bg-zinc-900/5 border-zinc-400 dark:bg-zinc-100/5 dark:border-zinc-500' : '' }}">
                                        <input type="checkbox" wire:model="work_days" value="{{ $value }}" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800">
                                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Half Day Threshold & Description --}}
                        <div class="grid grid-cols-1 gap-x-6 gap-y-4 p-6 md:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('attendance.half_day_threshold_minutes') }}</label>
                                <input type="number" wire:model="half_day_threshold_minutes" min="0" class="mt-1 block w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('attendance.description') }}</label>
                                <textarea wire:model="description" rows="2" class="mt-1 block w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Sidebar --}}
            <div class="lg:col-span-3">
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('common.info') }}</h4>
                    <div class="mt-3 space-y-2 text-xs text-zinc-500 dark:text-zinc-400">
                        <p>{{ __('attendance.grace_period_minutes') }}: {{ $grace_period_minutes }} min</p>
                        <p>{{ __('attendance.break_duration') }}: {{ $break_duration }} min</p>
                        <p>{{ __('attendance.work_days') }}: {{ count($work_days) }} days</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
