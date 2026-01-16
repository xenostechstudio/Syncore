<div x-data="{ showLogNote: false, showSendMessage: false, showScheduleActivity: false }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.payroll.components.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Salary Component</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $componentId ? $name : 'New Component' }}</span>
                        @if($componentId)
                            <flux:dropdown position="bottom" align="start">
                                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="cog-6-tooth" class="size-4" />
                                </button>
                                <flux:menu class="w-40">
                                    <button type="button" wire:click="delete" wire:confirm="Delete this component?" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
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
                    <a href="{{ route('hr.payroll.components.index') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="x-mark" class="size-4" />
                        Cancel
                    </a>
                </div>
                <div class="hidden items-center gap-2 lg:flex">
                    @php
                        $compType = $type;
                        $isEarning = $compType === 'earning';
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $isEarning ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                        {{ ucfirst($compType) }}
                    </span>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $isActive ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
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
                    {{-- Header Section --}}
                    <div class="p-5">
                        <h1 class="mb-5 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $componentId ? $code : 'New Component' }}</h1>
                        <div class="grid gap-6 sm:grid-cols-2">
                            {{-- Left: Code and Name --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Code <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="code" class="w-full rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" readonly>
                                    @error('code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Name <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.live="name" placeholder="Component Name" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Right: Inline Fields --}}
                            <div class="space-y-3">
                                <div class="flex items-center gap-4" x-data="{ open: false }">
                                    <label class="w-28 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Type <span class="text-red-500">*</span></label>
                                    <div class="relative flex-1">
                                        <button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-left text-sm transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:hover:border-zinc-700">
                                            <span class="text-zinc-900 dark:text-zinc-100">{{ ucfirst($type) }}</span>
                                            <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                        </button>
                                        <div x-show="open" @click.outside="open = false" x-transition class="absolute left-0 top-full z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                            <button type="button" wire:click="$set('type', 'earning')" @click="open = false" class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                Earning (Allowance)
                                            </button>
                                            <button type="button" wire:click="$set('type', 'deduction')" @click="open = false" class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                                Deduction
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4" x-data="{ open: false }">
                                    <label class="w-28 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Calculation</label>
                                    <div class="relative flex-1">
                                        <button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-left text-sm transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:hover:border-zinc-700">
                                            <span class="text-zinc-900 dark:text-zinc-100">{{ ucfirst($calculationType) }}</span>
                                            <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                        </button>
                                        <div x-show="open" @click.outside="open = false" x-transition class="absolute left-0 top-full z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                            <button type="button" wire:click="$set('calculationType', 'fixed')" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">Fixed Amount</button>
                                            <button type="button" wire:click="$set('calculationType', 'percentage')" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">Percentage</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <label class="w-28 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Amount <span class="text-red-500">*</span></label>
                                    <div class="relative flex-1">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400">Rp</span>
                                        <input type="number" wire:model="defaultAmount" class="w-full rounded-lg border border-transparent bg-transparent py-1.5 pl-10 pr-3 text-sm text-zinc-900 [appearance:textfield] transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700">
                                    </div>
                                </div>
                                @error('defaultAmount')<p class="ml-32 text-xs text-red-500">{{ $message }}</p>@enderror

                                <div class="flex items-center gap-4">
                                    <label class="w-28 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Sort Order</label>
                                    <input type="number" wire:model="sortOrder" class="flex-1 rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 [appearance:textfield] transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700">
                                </div>

                                <div class="flex items-center gap-4 pt-2">
                                    <label class="w-28 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Options</label>
                                    <div class="flex flex-1 flex-wrap gap-4">
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox" wire:model="isTaxable" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Taxable</span>
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
                        <textarea wire:model="description" rows="3" placeholder="Additional information about this component..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                    </div>
                </div>
            </div>

            {{-- Right Column: Activity Timeline --}}
            <div class="lg:col-span-3">
                <x-ui.chatter-forms :showMessage="false" />

                @if($componentId)
                    <x-ui.activity-timeline 
                        :activities="$activities" 
                        emptyMessage="Component created"
                        :createdAt="$componentCreatedAt"
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
