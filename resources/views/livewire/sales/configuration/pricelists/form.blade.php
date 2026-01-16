<div x-data="{ 
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false
}">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.configuration.pricelists.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">
                    {{ $pricelistId ? $name : 'New Pricelist' }}
                </span>
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
                    @if($pricelistId)
                        <button 
                            type="button" 
                            wire:click="delete" 
                            wire:confirm="Are you sure you want to delete this pricelist?" 
                            wire:loading.attr="disabled"
                            wire:target="delete"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 disabled:opacity-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400"
                        >
                            <flux:icon name="trash" wire:loading.remove wire:target="delete" class="size-4" />
                            <flux:icon name="arrow-path" wire:loading wire:target="delete" class="size-4 animate-spin" />
                            <span wire:loading.remove wire:target="delete">Delete</span>
                            <span wire:loading wire:target="delete">Deleting...</span>
                        </button>
                    @endif
                </div>
                @if($is_active)
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">Inactive</span>
                @endif
            </div>
            <div class="col-span-3 flex items-center justify-end gap-1">
                <x-ui.chatter-buttons :showMessage="false" />
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Form --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="p-5">
                        {{-- Header: Name & Code (like product form) --}}
                        <div class="mb-6">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Pricelist</p>
                            <div class="mt-2">
                                <input 
                                    type="text"
                                    wire:model="name"
                                    placeholder="Pricelist name..."
                                    class="w-full rounded-md border border-transparent bg-transparent px-2 py-1 text-xl font-semibold text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                />
                            </div>
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">Code:</span>
                                <input 
                                    type="text"
                                    wire:model="code"
                                    placeholder="e.g., RETAIL"
                                    class="rounded border border-transparent bg-transparent px-1.5 py-0.5 text-xs uppercase text-zinc-600 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:text-zinc-400 dark:placeholder-zinc-500 dark:hover:border-zinc-600"
                                />
                            </div>
                            @error('code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-6">
                            {{-- Status --}}
                            <div class="flex items-center gap-4">
                                <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                                <label class="flex cursor-pointer items-center gap-2">
                                    <input type="checkbox" wire:model="is_active" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Active</span>
                                </label>
                            </div>

                            {{-- Validity Period --}}
                            <div class="flex items-center gap-4">
                                <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Validity</label>
                                <div class="flex items-center gap-2">
                                    <input type="date" wire:model="start_date" class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    <span class="text-sm text-zinc-400">to</span>
                                    <input type="date" wire:model="end_date" class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                </div>
                            </div>

                            {{-- Discount Type --}}
                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <label class="mb-3 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Discount Type</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-zinc-200 bg-white p-4 transition-colors hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600 {{ $type === 'percentage' ? 'ring-2 ring-zinc-900 dark:ring-zinc-100' : '' }}">
                                        <input type="radio" wire:model.live="type" value="percentage" class="mt-0.5 border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600" />
                                        <div>
                                            <span class="block text-sm font-medium text-zinc-900 dark:text-zinc-100">Percentage Discount</span>
                                            <span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">Apply a percentage discount from the base price (e.g., 15% off)</span>
                                        </div>
                                    </label>
                                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-zinc-200 bg-white p-4 transition-colors hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600 {{ $type === 'fixed' ? 'ring-2 ring-zinc-900 dark:ring-zinc-100' : '' }}">
                                        <input type="radio" wire:model.live="type" value="fixed" class="mt-0.5 border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600" />
                                        <div>
                                            <span class="block text-sm font-medium text-zinc-900 dark:text-zinc-100">Fixed Amount</span>
                                            <span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">Set a fixed discount amount to subtract from base price</span>
                                        </div>
                                    </label>
                                </div>

                                {{-- Discount Value --}}
                                <div class="mt-4 flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ $type === 'percentage' ? 'Discount' : 'Amount' }}
                                    </label>
                                    <div class="flex items-center gap-2">
                                        @if($type === 'percentage')
                                            <input type="number" step="0.01" min="0" max="100" wire:model="discount" placeholder="0" class="w-32 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                            <span class="text-sm text-zinc-500 dark:text-zinc-400">%</span>
                                        @else
                                            <span class="text-sm text-zinc-500 dark:text-zinc-400">Rp</span>
                                            <input type="number" step="1" min="0" wire:model="discount" placeholder="0" class="w-40 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="flex items-start gap-4">
                                <label class="w-28 shrink-0 pt-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
                                <textarea wire:model="description" rows="3" placeholder="Optional description..." class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Activity Timeline --}}
            <div class="lg:col-span-3">
                {{-- Chatter Forms --}}
                @if($pricelistId)
                    <x-ui.chatter-forms :showMessage="false" />
                @endif

                {{-- Activity Timeline --}}
                @if($pricelistId)
                    {{-- Date Separator --}}
                    <div class="flex items-center gap-3 py-2">
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                            @if($activities->isNotEmpty() && $activities->first()['created_at']->isToday())
                                Today
                            @else
                                Activity
                            @endif
                        </span>
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    </div>

                    {{-- Activity Items --}}
                    <div class="space-y-3">
                        @forelse($activities as $item)
                            @if($item['type'] === 'note')
                                <x-ui.note-item :note="$item['data']" />
                            @else
                                <x-ui.activity-item :activity="$item['data']" emptyMessage="Pricelist created" />
                            @endif
                        @empty
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-ui.user-name :user="auth()->user()" />
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $createdAt ?? now()->format('H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Pricelist created</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @else
                    {{-- Empty State for New Pricelist --}}
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
