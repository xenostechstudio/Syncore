<div x-data="{ 
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false
}">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.configuration.taxes.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Tax</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $taxId ? $name : 'New Tax' }}
                        </span>
                        @if($taxId)
                            <flux:dropdown position="bottom" align="start">
                                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="cog-6-tooth" class="size-4" />
                                </button>
                                <flux:menu class="w-40">
                                    <button type="button" wire:click="delete" wire:confirm="Are you sure you want to delete this tax?" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
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
            <x-ui.alert type="success" :duration="5000">
                {{ session('success') }}
            </x-ui.alert>
        @endif
        @if($errors->any())
            <x-ui.alert type="error" :duration="10000">
                <span class="font-medium">Please fix the following errors:</span>
                <ul class="mt-1 list-inside list-disc text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif
    </div>

    {{-- Action Buttons Bar --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
            {{-- Left: Action Buttons (col-span-9) --}}
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button 
                        type="button" 
                        wire:click="save" 
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        <flux:icon name="document-check" wire:loading.remove wire:target="save" class="size-4" />
                        <flux:icon name="arrow-path" wire:loading wire:target="save" class="size-4 animate-spin" />
                        <span wire:loading.remove wire:target="save">Save</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                    @if($taxId)
                        <button 
                            type="button" 
                            wire:click="delete" 
                            wire:confirm="Are you sure you want to delete this tax?" 
                            wire:loading.attr="disabled"
                            wire:target="delete"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 disabled:opacity-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400"
                        >
                            <flux:icon name="trash" wire:loading.remove wire:target="delete" class="size-4" />
                            <flux:icon name="arrow-path" wire:loading wire:target="delete" class="size-4 animate-spin" />
                            <span wire:loading.remove wire:target="delete">Delete</span>
                            <span wire:loading wire:target="delete">Deleting...</span>
                        </button>
                    @endif
                </div>
                {{-- Status Badge --}}
                @if($is_active)
                    <x-ui.status-badge status="active" />
                @else
                    <x-ui.status-badge status="inactive" />
                @endif
            </div>

            {{-- Right: Chatter Icons (col-span-3) --}}
            <div class="col-span-3">
                <x-ui.chatter-buttons :showMessage="false" />
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Main Form (col-span-9) --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    {{-- Header Section --}}
                    <div class="p-5">
                        {{-- Title --}}
                        <div class="mb-6">
                            <h2 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $taxId ? $name : 'New' }}
                            </h2>
                        </div>

                        {{-- Form Fields --}}
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                            {{-- Name --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Name <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="name" placeholder="e.g., PPN 11%" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            {{-- Code --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Code <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="code" placeholder="e.g., PPN11" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm uppercase focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            {{-- Rate --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Rate <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" wire:model="rate" placeholder="11" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            {{-- Type --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</label>
                                <select wire:model="type" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="fixed">Fixed Amount</option>
                                </select>
                            </div>

                            {{-- Scope --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Scope</label>
                                <select wire:model="scope" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                    <option value="both">Sales & Purchase</option>
                                    <option value="sales">Sales Only</option>
                                    <option value="purchase">Purchase Only</option>
                                </select>
                            </div>

                            {{-- Include in Price --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Include in Price</label>
                                <label class="flex cursor-pointer items-center gap-2">
                                    <input type="checkbox" wire:model="include_in_price" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Tax is included in product price</span>
                                </label>
                            </div>

                            {{-- Active --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                                <label class="flex cursor-pointer items-center gap-2">
                                    <input type="checkbox" wire:model="is_active" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Active</span>
                                </label>
                            </div>

                            {{-- Description --}}
                            <div class="col-span-2 flex items-start gap-4">
                                <label class="w-32 shrink-0 pt-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
                                <textarea wire:model="description" rows="3" placeholder="Optional description..." class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Activity Log (col-span-3) --}}
            <div class="lg:col-span-3">
                {{-- Chatter Forms --}}
                <x-ui.chatter-forms :showMessage="false" />

                {{-- Activity Timeline --}}
                @if($taxId)
                    <div class="flex items-center gap-3 py-2">
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                            @if($this->activitiesAndNotes->isNotEmpty() && $this->activitiesAndNotes->first()['created_at']->isToday())
                                Today
                            @else
                                Activity
                            @endif
                        </span>
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    </div>

                    <div class="space-y-3">
                        @forelse($this->activitiesAndNotes as $item)
                            @if($item['type'] === 'note')
                                <x-ui.note-item :note="$item['data']" />
                            @else
                                <x-ui.activity-item :activity="$item['data']" emptyMessage="Tax created" />
                            @endif
                        @empty
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <x-ui.user-name :user="auth()->user()" />
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $createdAt ?? now()->format('H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Tax created</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="py-8 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon name="chat-bubble-left-right" class="size-6 text-zinc-400" />
                        </div>
                        <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No activity yet</p>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500">Activity will appear here once you save</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
