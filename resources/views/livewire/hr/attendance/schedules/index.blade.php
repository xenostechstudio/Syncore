<div>
    <x-ui.flash />    <x-ui.index-header
        title="{{ __('attendance.work_schedules') }}"
        :createRoute="route('hr.attendance.schedules.create')"
        :paginator="$schedules"
        :view="$view"
        :views="['list', 'grid']"
    >
        <x-slot:search>

                            @if(count($selected) > 0)
                                <x-ui.selection-toolbar :count="count($selected)">
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
                                </x-ui.selection-toolbar>
                            @else
                                <x-ui.searchbox-dropdown placeholder="{{ __('common.search') }}..." widthClass="w-[420px]" width="420px">
                                    <div class="flex flex-col gap-4 p-3 md:flex-row">
                                        {{-- Status filter --}}
                                        <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:pr-3 dark:border-zinc-700">
                                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                                <flux:icon name="funnel" class="size-3.5" />
                                                <span>{{ __('common.status') }}</span>
                                            </div>
                                            <div class="space-y-1">
                                                <button type="button" wire:click="$set('status', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <span>{{ __('common.all_statuses') }}</span>
                                                    @if(empty($status))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                                </button>
                                                <button type="button" wire:click="$set('status', '1')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <div class="flex items-center gap-2">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                        <span>{{ __('common.active') }}</span>
                                                    </div>
                                                    @if($status === '1')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                                </button>
                                                <button type="button" wire:click="$set('status', '0')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <div class="flex items-center gap-2">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span>
                                                        <span>{{ __('common.inactive') }}</span>
                                                    </div>
                                                    @if($status === '0')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                                </button>
                                            </div>
                                        </div>
                                        {{-- Sort --}}
                                        <div class="flex-1 md:pl-3">
                                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                                <flux:icon name="arrows-up-down" class="size-3.5" />
                                                <span>{{ __('common.sort_by') }}</span>
                                            </div>
                                            <div class="space-y-1">
                                                @foreach(['name_asc' => 'Name: A to Z', 'name_desc' => 'Name: Z to A', 'latest' => __('common.latest'), 'oldest' => __('common.oldest')] as $key => $label)
                                                    <button type="button" wire:click="$set('sort', '{{ $key }}')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                        <span>{{ $label }}</span>
                                                        @if($sort === $key)<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </x-ui.searchbox-dropdown>
                            @endif
        </x-slot:search>
    </x-ui.index-header>

    {{-- Content --}}
    @if($schedules->isEmpty())
        <x-ui.empty-state
                layout="fullscreen"
                icon="calendar"
                title="{{ __('common.no_records_found') }}"
                description="{{ __('attendance.create_schedule') }}"
                :actionUrl="route('hr.attendance.schedules.create')"
                actionLabel="{{ __('attendance.create_schedule') }}"
            />
    @else
        <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                    <tr>
                        <th scope="col" class="w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600">
                        </th>
                        <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('attendance.name') }}</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('attendance.code') }}</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('attendance.start_time') }}</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('attendance.end_time') }}</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('attendance.work_days') }}</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('common.status') }}</th>
                        <th scope="col" class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @foreach($schedules as $schedule)
                        @php $isSelected = in_array($schedule->id, $selected); @endphp
                        <tr wire:key="ws-{{ $schedule->id }}" onclick="window.Livewire.navigate('{{ route('hr.attendance.schedules.edit', $schedule->id) }}')" class="group cursor-pointer transition-all duration-150 {{ $isSelected ? 'bg-zinc-900/[0.03] dark:bg-zinc-100/[0.03]' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                            <td class="relative py-3 pl-4 pr-2 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                <div class="absolute inset-y-0 left-0 w-0.5 transition-all duration-150 {{ $isSelected ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-transparent group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700' }}"></div>
                                <input type="checkbox" wire:model.live="selected" value="{{ $schedule->id }}" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600 {{ $isSelected ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-100/20' : '' }}">
                            </td>
                            <td class="py-3 pl-2 pr-4">
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $schedule->name }}</p>
                                    @if($schedule->description)
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ Str::limit($schedule->description, 40) }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">{{ $schedule->code }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $schedule->start_time }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $schedule->end_time }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        $dayLabels = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
                                    @endphp
                                    @foreach($schedule->work_days ?? [] as $day)
                                        <span class="inline-flex rounded bg-blue-50 px-1.5 py-0.5 text-[10px] font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                            {{ $dayLabels[$day] ?? $day }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3" onclick="event.stopPropagation()">
                                <button wire:click="toggleStatus({{ $schedule->id }})" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $schedule->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                    {{ $schedule->is_active ? __('common.active') : __('common.inactive') }}
                                </button>
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
