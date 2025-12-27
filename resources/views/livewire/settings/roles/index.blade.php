<div>
    <x-slot:header>
        <div class="flex items-center gap-3">
            <a 
                href="{{ route('settings.roles.create') }}"
                wire:navigate
                class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                New
            </a>
            <h1 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Roles & Permissions</h1>
            <flux:dropdown position="bottom" align="start">
                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="cog-6-tooth" class="size-5" />
                </button>

                <flux:menu class="w-48">
                    <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                        <flux:icon name="arrow-down-tray" class="size-4" />
                        <span>Import roles</span>
                    </button>
                    <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                        <flux:icon name="arrow-up-tray" class="size-4" />
                        <span>Export all</span>
                    </button>
                </flux:menu>
            </flux:dropdown>
        </div>
        <div class="flex items-center gap-4">
            {{-- Search or Selection --}}
            @if(count($selected) > 0)
                <div class="flex items-center gap-2">
                    <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-zinc-100 px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-200 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-600">
                        <flux:icon name="x-mark" class="size-4" />
                        <span>{{ count($selected) }} Selected</span>
                    </button>
                    <flux:dropdown position="bottom" align="center">
                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <span>Actions</span>
                            <flux:icon name="chevron-down" class="size-3" />
                        </button>
                        <flux:menu class="w-56">
                            <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                <flux:icon name="arrow-up-tray" class="size-4" />
                                <span>Export</span>
                            </button>
                            <button type="button" wire:click="confirmDelete" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                <flux:icon name="trash" class="size-4" />
                                <span>Delete</span>
                            </button>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            @else
                <div class="relative flex h-9 w-64 items-center overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search roles..." 
                        class="h-full w-full border-0 bg-transparent pl-9 pr-4 text-sm outline-none focus:ring-0" 
                    />
                </div>
            @endif

            {{-- Pagination --}}
            @if($roles instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $roles->firstItem() ?? 0 }}-{{ $roles->lastItem() ?? 0 }}/{{ $roles->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button 
                            type="button"
                            wire:click="previousPage"
                            @disabled($roles->onFirstPage())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button 
                            type="button"
                            wire:click="nextPage"
                            @disabled(!$roles->hasMorePages())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>
            @endif
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
    </div>

    {{-- Content: Table View --}}
    <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                <tr>
                    <th scope="col" class="w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                        <input 
                            type="checkbox" 
                            wire:model.live="selectAll"
                            class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600"
                        >
                    </th>
                    <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Role</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Guard</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Permissions</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Created</th>
                    <th scope="col" class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse($roles as $role)
                    <tr 
                        wire:click="goToEdit({{ $role->id }})"
                        class="cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                    >
                        <td class="py-4 pl-4 pr-1 sm:pl-6 lg:pl-8" wire:click.stop>
                            <input 
                                type="checkbox" 
                                wire:model.live="selected"
                                value="{{ $role->id }}"
                                class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600"
                            >
                        </td>
                        <td class="py-4 pl-2 pr-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                                    <flux:icon name="shield-check" class="size-5" />
                                </div>
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ ucfirst($role->name) }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $role->guard_name }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $role->permissions_count ?? 0 }} permissions</span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $role->created_at->format('M d, Y') }}</span>
                        </td>
                        <td class="py-4 pr-4 sm:pr-6 lg:pr-8" wire:click.stop>
                            <div class="flex items-center gap-1">
                                <a 
                                    href="{{ route('settings.roles.edit', $role->id) }}"
                                    wire:navigate
                                    class="inline-flex rounded-md p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                                >
                                    <flux:icon name="pencil-square" class="size-4" />
                                </a>
                                <button 
                                    type="button"
                                    wire:click="confirmDelete({{ $role->id }})"
                                    class="inline-flex rounded-md p-2 text-zinc-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                                >
                                    <flux:icon name="trash" class="size-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <flux:icon name="shield-check" class="size-6 text-zinc-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No roles found</p>
                                    <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">
                                        @if(class_exists('\Spatie\Permission\Models\Role'))
                                            Create your first role to get started
                                        @else
                                            Install spatie/laravel-permission to manage roles
                                        @endif
                                    </p>
                                </div>
                                @if(class_exists('\Spatie\Permission\Models\Role'))
                                    <button 
                                        type="button"
                                        wire:click="openCreateModal"
                                        class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                                    >
                                        <flux:icon name="plus" class="size-4" />
                                        Create Role
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 z-40 bg-zinc-500/75 transition-opacity dark:bg-zinc-900/75" wire:click="cancelDelete" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div class="relative z-50 inline-block w-full transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:max-w-md sm:align-middle dark:bg-zinc-900">
                    <div class="px-6 py-6">
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                                <flux:icon name="exclamation-triangle" class="size-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Delete Role(s)</h3>
                                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                    Are you sure you want to delete {{ count($selected) }} role(s)? This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <button 
                            type="button"
                            wire:click="cancelDelete"
                            class="rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        >
                            Cancel
                        </button>
                        <button 
                            type="button"
                            wire:click="deleteRoles"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
