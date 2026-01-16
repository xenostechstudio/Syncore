<div x-data="{ showLogNote: false, showSendMessage: false, showScheduleActivity: false }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.leave.types.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Leave Type</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $leaveTypeId ? $name : 'New Leave Type' }}</span>
                        @if($leaveTypeId)
                            <flux:dropdown position="bottom" align="start">
                                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="cog-6-tooth" class="size-4" />
                                </button>
                                <flux:menu class="w-40">
                                    <button type="button" wire:click="delete" wire:confirm="Delete this leave type?" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
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
                    <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="document-check" wire:loading.remove wire:target="save" class="size-4" />
                        <flux:icon name="arrow-path" wire:loading wire:target="save" class="size-4 animate-spin" />
                        <span wire:loading.remove wire:target="save">Save</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                    <a href="{{ route('hr.leave.types.index') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
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
            <div class="col-span-3 flex items-center justify-end gap-1">
                <x-ui.chatter-buttons :showMessage="false" />
            </div>
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
                        <h1 class="mb-5 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $leaveTypeId ? $name : 'New Leave Type' }}</h1>
                        <div class="grid gap-6 sm:grid-cols-2">
                            {{-- Left: Name and Code --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Name <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.live="name" placeholder="Leave Type Name" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Code <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="code" placeholder="e.g., AL, SL" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    @error('code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Right: Inline Fields --}}
                            <div class="space-y-3">
                                <div class="flex items-center gap-4">
                                    <label class="w-32 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Days Per Year <span class="text-red-500">*</span></label>
                                    <input type="number" wire:model="daysPerYear" min="0" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm [appearance:textfield] focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                </div>
                                @error('daysPerYear')<p class="ml-36 text-xs text-red-500">{{ $message }}</p>@enderror

                                <div class="flex items-center gap-4 pt-2">
                                    <label class="w-32 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Settings</label>
                                    <div class="flex flex-1 flex-wrap gap-4">
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox" wire:model="isPaid" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Paid</span>
                                        </label>
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox" wire:model="requiresApproval" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Requires Approval</span>
                                        </label>
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox" wire:model="isActive" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Active</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="border-t border-zinc-200 p-5 dark:border-zinc-800">
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Description</label>
                        <textarea wire:model="description" rows="3" placeholder="Additional information about this leave type..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                    </div>
                </div>
            </div>

            {{-- Right Column: Activity Timeline --}}
            <div class="lg:col-span-3">
                <x-ui.chatter-forms :showMessage="false" />

                @if($leaveTypeId)
                    <x-ui.activity-timeline 
                        :activities="$activities" 
                        emptyMessage="Leave type created"
                        :createdAt="$leaveTypeCreatedAt"
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
</div>
