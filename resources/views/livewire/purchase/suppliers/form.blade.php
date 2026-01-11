<div x-data="{ 
    showSendMessage: false, 
    showLogNote: false, 
    showScheduleActivity: false,
    showDeleteModal: false 
}">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('purchase.suppliers.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        Supplier
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $supplierId ? $name : 'New Supplier' }}
                        </span>
                        @if($supplierId)
                            <flux:dropdown position="bottom" align="start">
                                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="cog-6-tooth" class="size-4" />
                                </button>
                                <flux:menu class="w-40">
                                    <button type="button" @click="showDeleteModal = true" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
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
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button 
                        type="button"
                        wire:click="save"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        <flux:icon name="document-check" class="size-4" />
                        Save
                    </button>
                </div>

                {{-- Status Badge --}}
                <div class="hidden items-center lg:flex">
                    @if($is_active)
                        <span class="inline-flex h-[38px] items-center rounded-lg bg-emerald-100 px-4 text-sm font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <flux:icon name="check-circle" class="mr-1.5 size-4" />
                            Active
                        </span>
                    @else
                        <span class="inline-flex h-[38px] items-center rounded-lg bg-zinc-200 px-4 text-sm font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">
                            Inactive
                        </span>
                    @endif
                </div>
            </div>

            {{-- Right: Chatter Icons --}}
            <div class="col-span-3">
                <x-ui.chatter-buttons />
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div 
        x-show="showDeleteModal" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-black/50" @click="showDeleteModal = false"></div>
        <div 
            class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="showDeleteModal = false"
        >
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon name="trash" class="size-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Delete Supplier</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">This action cannot be undone.</p>
                </div>
            </div>
            <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">
                Are you sure you want to delete this supplier? All associated data will be permanently removed.
            </p>
            <div class="flex justify-end gap-3">
                <button 
                    type="button"
                    @click="showDeleteModal = false"
                    class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                >
                    Cancel
                </button>
                <button 
                    type="button"
                    wire:click="delete"
                    @click="showDeleteModal = false"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700"
                >
                    Delete
                </button>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Main Form --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="p-5">
                        {{-- Big Title --}}
                        <div class="mb-6">
                            <input 
                                type="text"
                                wire:model="name"
                                placeholder="Supplier Name"
                                class="w-full border-0 bg-transparent p-0 text-2xl font-semibold text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:placeholder-zinc-500"
                            />
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Form Fields --}}
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                            {{-- Left Column --}}
                            <div class="space-y-4">
                                <div class="flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Contact Person</label>
                                    <input 
                                        type="text"
                                        wire:model="contact_person"
                                        placeholder="Contact name"
                                        class="flex-1 rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                    />
                                </div>

                                <div class="flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
                                    <input 
                                        type="email"
                                        wire:model="email"
                                        placeholder="supplier@example.com"
                                        class="flex-1 rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                    />
                                </div>
                                @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                                <div class="flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Phone</label>
                                    <input 
                                        type="text"
                                        wire:model="phone"
                                        placeholder="+1 234 567 890"
                                        class="flex-1 rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                    />
                                </div>
                            </div>

                            {{-- Right Column --}}
                            <div class="space-y-4">
                                <div class="flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">City</label>
                                    <input 
                                        type="text"
                                        wire:model="city"
                                        placeholder="City"
                                        class="flex-1 rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                    />
                                </div>

                                <div class="flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Country</label>
                                    <input 
                                        type="text"
                                        wire:model="country"
                                        placeholder="Country"
                                        class="flex-1 rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                    />
                                </div>

                                <div class="flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                                    <label class="flex cursor-pointer items-center gap-2">
                                        <input 
                                            type="checkbox"
                                            wire:model="is_active"
                                            class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700"
                                        />
                                        <span class="text-sm text-zinc-700 dark:text-zinc-300">Active</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Address --}}
                        <div class="mt-6">
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Address</label>
                            <textarea 
                                wire:model="address"
                                rows="3"
                                placeholder="Full address..."
                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Activity Timeline --}}
            <div class="lg:col-span-3">
                {{-- Chatter Forms --}}
                <x-ui.chatter-forms />

                {{-- Activity Timeline --}}
                @if($supplierId)
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

                    <div class="space-y-4">
                        @forelse($activities as $item)
                            @if($item['type'] === 'note')
                                {{-- Note Item --}}
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <x-ui.user-avatar :user="$item['data']->user" size="md" :showPopup="true" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <x-ui.user-name :user="$item['data']->user" />
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500">
                                                {{ $item['created_at']->diffForHumans() }}
                                            </span>
                                        </div>
                                        <div class="mt-1 rounded-lg bg-amber-50 px-3 py-2 text-sm text-zinc-700 dark:bg-amber-900/20 dark:text-zinc-300">
                                            <div class="flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400 mb-1">
                                                <flux:icon name="pencil-square" class="size-3" />
                                                <span>Internal Note</span>
                                            </div>
                                            {{ $item['data']->content }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- Activity Log Item --}}
                                <x-ui.activity-item :activity="$item['data']" emptyMessage="Supplier created" />
                            @endif
                        @empty
                            {{-- Supplier Created (fallback when no activities yet) --}}
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-ui.user-name :user="auth()->user()" />
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $createdAt ?? now()->format('H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Supplier created</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @else
                    {{-- Empty State for New Supplier --}}
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
