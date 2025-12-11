<div x-data="{ showCancelModal: false, showDeleteModal: false }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.warehouses.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Warehouse</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $editing && $warehouse ? $warehouse->name : 'New Warehouse' }}
                        </span>
                        @if($editing)
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
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
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

    {{-- Action Bar --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="flex items-center justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    <flux:icon name="document-check" class="size-4" />
                    Save
                </button>
                <button type="button" @click="showCancelModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                    <flux:icon name="x-mark" class="size-4" />
                    Cancel
                </button>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="p-5">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {{-- Name --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name <span class="text-red-500">*</span></label>
                        <input 
                            type="text"
                            wire:model="name"
                            placeholder="Warehouse name..."
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Contact Info --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Contact Info</label>
                        <input 
                            type="text"
                            wire:model="contact_info"
                            placeholder="Phone or email..."
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('contact_info') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Location --}}
                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Location</label>
                        <textarea 
                            wire:model="location"
                            rows="3"
                            placeholder="Full address..."
                            class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        ></textarea>
                        @error('location') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cancel Modal --}}
    <x-ui.confirm-modal show="showCancelModal">
        <x-slot:icon>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                <flux:icon name="exclamation-triangle" class="size-6" />
            </div>
        </x-slot:icon>
        <x-slot:title>Discard changes?</x-slot:title>
        <x-slot:description>If you leave this page, any unsaved changes will be lost.</x-slot:description>
        <x-slot:actions>
            <button type="button" @click="showCancelModal = false" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">Keep editing</button>
            <a href="{{ route('inventory.warehouses.index') }}" wire:navigate class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600">Discard & leave</a>
        </x-slot:actions>
    </x-ui.confirm-modal>

    {{-- Delete Modal --}}
    <x-ui.confirm-modal show="showDeleteModal">
        <x-slot:icon>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                <flux:icon name="trash" class="size-6" />
            </div>
        </x-slot:icon>
        <x-slot:title>Delete warehouse?</x-slot:title>
        <x-slot:description>This action cannot be undone. All associated data will be permanently removed.</x-slot:description>
        <x-slot:actions>
            <button type="button" @click="showDeleteModal = false" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">Cancel</button>
            <button type="button" wire:click="delete" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600">Delete</button>
        </x-slot:actions>
    </x-ui.confirm-modal>
</div>
