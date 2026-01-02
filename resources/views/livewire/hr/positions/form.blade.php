<div>
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.positions.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Position</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $positionId ? $name : 'New Position' }}</span>
                        @if($positionId)
                            <flux:dropdown position="bottom" align="start">
                                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="cog-6-tooth" class="size-4" />
                                </button>
                                <flux:menu class="w-40">
                                    <button type="button" wire:click="delete" wire:confirm="Delete this position?" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                        <flux:icon name="trash" class="size-4" />
                                        <span>Delete</span>
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
        @if($errors->any())
            <x-ui.alert type="error" :duration="10000">
                <span class="font-medium">Please fix the following errors:</span>
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
                    <button type="button" wire:click="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="document-check" class="size-4" />
                        Save
                    </button>
                    <a href="{{ route('hr.positions.index') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="x-mark" class="size-4" />
                        Cancel
                    </a>
                </div>
                <div class="hidden items-center lg:flex">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $isActive ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                        @if($isActive)
                            <flux:icon name="check-circle" class="mr-1 size-3" />
                        @endif
                        {{ $isActive ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            <div class="col-span-3"></div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Main Form --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    {{-- Profile Header Section --}}
                    <div class="p-5">
                        <div class="flex items-start gap-6">
                            {{-- Icon Placeholder --}}
                            <div class="relative flex-shrink-0">
                                <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                    <flux:icon name="briefcase" class="size-10 text-zinc-300 dark:text-zinc-600" />
                                </div>
                            </div>

                            {{-- Name --}}
                            <div class="flex-1 space-y-1">
                                {{-- Name (Big Input) --}}
                                <div>
                                    <input type="text" wire:model="name" placeholder="Position Name" class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-2xl font-bold text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-200 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-700 dark:focus:border-zinc-700" />
                                    @error('name') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Department --}}
                                <div class="flex items-center gap-2 pl-2">
                                    <flux:icon name="building-office" class="size-4 flex-shrink-0 text-zinc-400" />
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $departments->firstWhere('id', $departmentId)?->name ?? 'No department' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Content --}}
                    <div class="border-t border-zinc-200 px-5 py-5 dark:border-zinc-800">
                        <div class="space-y-8">
                            {{-- Organization --}}
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                <div class="lg:w-72">
                                    <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Organization</h3>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Department assignment.</p>
                                </div>
                                <div class="flex-1">
                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Department</label>
                                        <div class="relative">
                                            <select wire:model="departmentId" class="w-full appearance-none rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-9 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                <option value="">None</option>
                                                @foreach($departments as $dept)
                                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Salary --}}
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                <div class="lg:w-72">
                                    <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Salary Range</h3>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Minimum and maximum salary.</p>
                                </div>
                                <div class="flex-1">
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Min Salary</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400">Rp</span>
                                                <input type="number" wire:model="minSalary" class="w-full rounded-lg border border-zinc-200 bg-white py-2 pl-10 pr-3 text-sm text-zinc-900 [appearance:textfield] focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Max Salary</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400">Rp</span>
                                                <input type="number" wire:model="maxSalary" class="w-full rounded-lg border border-zinc-200 bg-white py-2 pl-10 pr-3 text-sm text-zinc-900 [appearance:textfield] focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Details --}}
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                <div class="lg:w-72">
                                    <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Details</h3>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Description and requirements.</p>
                                </div>
                                <div class="flex-1 space-y-4">
                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Description</label>
                                        <textarea wire:model="description" rows="3" class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Requirements</label>
                                        <textarea wire:model="requirements" rows="3" placeholder="Skills, qualifications, experience..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="isActive" id="isActive" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                                        <label for="isActive" class="text-sm text-zinc-700 dark:text-zinc-300">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Activity Timeline --}}
            <div class="lg:col-span-3">
                @if($positionId)
                    {{-- Date Separator --}}
                    <div class="flex items-center gap-3 py-2">
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                            @if($activities->isNotEmpty() && $activities->first()->created_at->isToday())
                                Today
                            @else
                                Activity
                            @endif
                        </span>
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    </div>

                    {{-- Activity Items --}}
                    <div class="space-y-4">
                        @forelse($activities as $activity)
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <x-ui.user-avatar :user="$activity->causer" size="md" :showPopup="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-ui.user-name :user="$activity->causer" />
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">
                                            {{ $activity->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        @if($activity->properties->has('old') && $activity->event === 'updated')
                                            @php
                                                $changes = collect($activity->properties->get('attributes', []))
                                                    ->filter(fn($val, $key) => isset($activity->properties->get('old', [])[$key]) && $activity->properties->get('old')[$key] !== $val)
                                                    ->keys()
                                                    ->map(fn($key) => '<span class="font-medium text-zinc-900 dark:text-zinc-100">' . str_replace('_', ' ', $key) . '</span>')
                                                    ->implode(', ');
                                            @endphp
                                            @if($changes)
                                                Updated {!! $changes !!}
                                            @else
                                                {{ $activity->description }}
                                            @endif
                                        @else
                                            {{ $activity->description }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @empty
                            {{-- Position Created (fallback when no activities yet) --}}
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-ui.user-name :user="auth()->user()" />
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $position?->created_at?->format('H:i') ?? now()->format('H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Position created</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @else
                    {{-- Empty State for New Position --}}
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
</div>
