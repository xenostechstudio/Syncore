<div x-data="{
    showCancelModal: false,
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false
}">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            {{-- Left Group: Back Button, Title, Gear Dropdown --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.categories.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    {{-- Small module label --}}
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        Category
                    </span>

                    {{-- Category name + gear dropdown inline --}}
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $categoryId ? $name : 'New Category' }}
                        </span>

                        @if($categoryId)
                            {{-- Header actions dropdown (Duplicate, Delete) --}}
                            <flux:dropdown position="bottom" align="start">
                                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="cog-6-tooth" class="size-4" />
                                </button>

                                <flux:menu class="w-40">
                                    <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                        <flux:icon name="document-duplicate" class="size-4" />
                                        <span>Duplicate</span>
                                    </button>
                                    <flux:menu.separator />
                                    <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
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

        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">
                {{ session('error') }}
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
            {{-- Left: Action Buttons + Status (col-span-9) --}}
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        <flux:icon name="check" wire:loading.remove wire:target="save" class="size-4" />
                        <flux:icon name="arrow-path" wire:loading wire:target="save" class="size-4 animate-spin" />
                        <span wire:loading.remove wire:target="save">Save</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>

                    <button
                        type="button"
                        @click="showCancelModal = true"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Cancel
                    </button>
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
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="p-6">
                        <div class="grid gap-6 lg:grid-cols-2">
                            {{-- Left Column --}}
                            <div class="space-y-4">
                                {{-- Name --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-32 shrink-0 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Name <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex-1">
                                        <input
                                            type="text"
                                            wire:model="name"
                                            placeholder="Category name"
                                            class="w-full rounded-lg border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                        />
                                        @error('name')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Code --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-32 shrink-0 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Code
                                    </label>
                                    <div class="flex-1">
                                        <input
                                            type="text"
                                            wire:model="code"
                                            placeholder="e.g. CAT-001"
                                            class="w-full rounded-lg border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                        />
                                        @error('code')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Parent Category --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-32 shrink-0 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Parent
                                    </label>
                                    <div class="relative flex-1">
                                        <select
                                            wire:model="parent_id"
                                            class="w-full appearance-none rounded-lg border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                        >
                                            <option value="">No parent (root category)</option>
                                            @foreach($parentCategories as $parent)
                                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                            @endforeach
                                        </select>
                                        <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                    </div>
                                </div>
                            </div>

                            {{-- Right Column --}}
                            <div class="space-y-4">
                                {{-- Color --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-32 shrink-0 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Color
                                    </label>
                                    <div class="relative flex-1">
                                        <select
                                            wire:model="color"
                                            class="w-full appearance-none rounded-lg border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                        >
                                            <option value="">Default</option>
                                            <option value="red">Red</option>
                                            <option value="orange">Orange</option>
                                            <option value="amber">Amber</option>
                                            <option value="yellow">Yellow</option>
                                            <option value="lime">Lime</option>
                                            <option value="green">Green</option>
                                            <option value="emerald">Emerald</option>
                                            <option value="teal">Teal</option>
                                            <option value="cyan">Cyan</option>
                                            <option value="sky">Sky</option>
                                            <option value="blue">Blue</option>
                                            <option value="indigo">Indigo</option>
                                            <option value="violet">Violet</option>
                                            <option value="purple">Purple</option>
                                            <option value="fuchsia">Fuchsia</option>
                                            <option value="pink">Pink</option>
                                            <option value="rose">Rose</option>
                                        </select>
                                        <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                    </div>
                                </div>

                                {{-- Sort Order --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-32 shrink-0 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Sort Order
                                    </label>
                                    <div class="flex-1">
                                        <input
                                            type="number"
                                            wire:model="sort_order"
                                            min="0"
                                            class="w-full rounded-lg border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                        />
                                    </div>
                                </div>

                                {{-- Status --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-32 shrink-0 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Status
                                    </label>
                                    <div class="flex-1">
                                        <div class="inline-flex rounded-lg border border-zinc-200 p-0.5 dark:border-zinc-700">
                                            <button
                                                type="button"
                                                wire:click="$set('is_active', true)"
                                                class="{{ $is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }} rounded-md px-3 py-1 text-sm font-medium transition-colors"
                                            >
                                                Active
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="$set('is_active', false)"
                                                class="{{ !$is_active ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }} rounded-md px-3 py-1 text-sm font-medium transition-colors"
                                            >
                                                Inactive
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="mt-6 border-t border-zinc-100 pt-6 dark:border-zinc-800">
                            <div class="flex gap-4">
                                <label class="w-32 shrink-0 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Description
                                </label>
                                <div class="flex-1">
                                    <textarea
                                        wire:model="description"
                                        rows="3"
                                        placeholder="Optional description for this category..."
                                        class="w-full rounded-lg border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                    ></textarea>
                                </div>
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
                @if($categoryId)
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
                                <x-ui.activity-item :activity="$item['data']" emptyMessage="Category created" />
                            @endif
                        @empty
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <x-ui.user-name :user="auth()->user()" />
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ now()->format('H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Category created</p>
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

    {{-- Cancel Confirmation Modal --}}
    <x-ui.confirm-modal show="showCancelModal">
        <x-slot:icon>
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                <flux:icon name="exclamation-triangle" class="size-7" />
            </div>
        </x-slot:icon>

        <x-slot:title>
            Discard changes?
        </x-slot:title>

        <x-slot:description>
            You have unsaved changes. Are you sure you want to leave this page?
        </x-slot:description>

        <x-slot:actions>
            <button
                type="button"
                @click="showCancelModal = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                Keep editing
            </button>

            <a
                href="{{ route('inventory.categories.index') }}"
                wire:navigate
                class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600"
            >
                Discard & leave
            </a>
        </x-slot:actions>
    </x-ui.confirm-modal>
</div>
