<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('inventory.warehouses.index') }}" wire:navigate class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-normal text-zinc-900 dark:text-zinc-100">
                    {{ $editing ? 'Edit Warehouse' : 'New Warehouse' }}
                </h1>
                <p class="text-sm font-light text-zinc-500 dark:text-zinc-400">
                    {{ $editing ? 'Update warehouse details' : 'Create a new warehouse' }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <flux:button variant="ghost" href="{{ route('inventory.warehouses.index') }}" wire:navigate>
                Cancel
            </flux:button>
            <flux:button wire:click="save" variant="primary">
                {{ $editing ? 'Update Warehouse' : 'Create Warehouse' }}
            </flux:button>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Left Column: Main Form --}}
        <div class="space-y-6 lg:col-span-8">
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Warehouse Information</h2>
                </div>
                <div class="p-5">
                    <div class="grid gap-6">
                        {{-- Name --}}
                        <div>
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Warehouse Name <span class="text-red-500">*</span></label>
                            <flux:input 
                                wire:model="name" 
                                placeholder="e.g., Central Distribution Center"
                            />
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Location --}}
                        <div>
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Location</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 dark:text-zinc-500">
                                    <flux:icon name="map-pin" class="size-4" />
                                </div>
                                <flux:input 
                                    wire:model="location" 
                                    placeholder="e.g., 123 Warehouse Blvd, Logistics City"
                                    class="pl-9"
                                />
                            </div>
                            @error('location') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Contact Info --}}
                        <div>
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Contact Information</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 dark:text-zinc-500">
                                    <flux:icon name="phone" class="size-4" />
                                </div>
                                <flux:input 
                                    wire:model="contact_info" 
                                    placeholder="e.g., +1 (555) 123-4567"
                                    class="pl-9"
                                />
                            </div>
                            @error('contact_info') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Info --}}
        <div class="space-y-6 lg:col-span-4">
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">About Warehouses</h2>
                </div>
                <div class="p-5">
                    <p class="text-sm font-light text-zinc-600 dark:text-zinc-400">
                        Warehouses are physical locations where you store your inventory items. You can track stock levels per warehouse and manage transfers between them.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
