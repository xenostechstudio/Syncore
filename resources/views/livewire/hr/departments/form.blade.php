<div>
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.departments.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Department</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $departmentId ? $name : 'New Department' }}</span>
                        @if($departmentId)
                            <flux:dropdown position="bottom" align="start">
                                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="cog-6-tooth" class="size-4" />
                                </button>
                                <flux:menu class="w-40">
                                    <button type="button" wire:click="delete" wire:confirm="Delete this department?" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                        <flux:icon name="trash" class="size-4" />
                                        <span>Delete</span>
                                    </button>
                                </flux:menu>
                            </flux:dropdown>
                        @endif
                    </div>
                </div>
            </div>
            @if($departmentId)
                <span class="inline-flex rounded-full {{ $isActive ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-400' }} px-3 py-1 text-xs font-medium">
                    {{ $isActive ? 'Active' : 'Inactive' }}
                </span>
            @endif
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

    {{-- Action Bar --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="flex items-center gap-2">
            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                <flux:icon name="document-check" wire:loading.remove wire:target="save" class="size-4" />
                <flux:icon name="arrow-path" wire:loading wire:target="save" class="size-4 animate-spin" />
                <span wire:loading.remove wire:target="save">Save</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </div>

    {{-- Form --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="max-w-2xl">
            <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="p-5 space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Name <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Code</label>
                        <input type="text" wire:model="code" placeholder="e.g., HR, IT, FIN" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Parent Department</label>
                        <select wire:model="parentId" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            <option value="">None</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Manager</label>
                        <select wire:model="managerId" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            <option value="">None</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Description</label>
                        <textarea wire:model="description" rows="3" class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
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
