<div x-data="{ activeTab: 'modules', showSendMessage: false, showLogNote: false, showScheduleActivity: false }" x-cloak>
    <x-slot:header>
        <div class="flex items-center gap-3">
            <button 
                type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
                class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                <flux:icon name="document-check" wire:loading.remove wire:target="save" class="size-4" />
                <flux:icon name="arrow-path" wire:loading wire:target="save" class="size-4 animate-spin" />
                <span wire:loading.remove wire:target="save">Save</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
            <a href="{{ route('settings.roles.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                <flux:icon name="arrow-left" class="size-5" />
            </a>
            <div class="flex flex-col">
                <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Role</span>
                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $roleId ? ucfirst($roleName) : 'New Role' }}</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            @if($roleId)
                <button 
                    type="button"
                    wire:click="delete"
                    wire:confirm="Delete this role? This action cannot be undone."
                    wire:loading.attr="disabled"
                    wire:target="delete"
                    class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 disabled:opacity-50 dark:border-red-900/30 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20"
                >
                    <flux:icon name="trash" wire:loading.remove wire:target="delete" class="size-4" />
                    <flux:icon name="arrow-path" wire:loading wire:target="delete" class="size-4 animate-spin" />
                    <span wire:loading.remove wire:target="delete">Delete</span>
                    <span wire:loading wire:target="delete">Deleting...</span>
                </button>
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

    {{-- Main Content --}}
    <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Main Form --}}
            <div class="lg:col-span-9">
            <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="p-5">
                    {{-- Details Tab --}}
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Role</p>
                            <div class="mt-2 flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                                    <flux:icon name="shield-check" class="size-5" />
                                </div>
                                <input 
                                    type="text"
                                    wire:model="roleName"
                                    placeholder="Role name..."
                                    class="flex-1 rounded-md border border-transparent bg-transparent px-2 py-1 text-lg text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                />
                            </div>
                            @error('roleName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                            <div class="mt-3 flex items-center gap-3">
                                <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Guard</label>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 text-xs">
                                        <button
                                            type="button"
                                            wire:click="$set('roleGuard', 'web')"
                                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 {{ $roleGuard === 'web'
                                                ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                                                : 'border border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-500' }}"
                                        >
                                            <span>web</span>
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="$set('roleGuard', 'api')"
                                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 {{ $roleGuard === 'api'
                                                ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                                                : 'border border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-500' }}"
                                        >
                                            <span>api</span>
                                        </button>
                                    </div>
                                    @error('roleGuard') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabs --}}
                    <div class="mb-4 border-b border-zinc-200 dark:border-zinc-800">
                        <nav class="-mb-px flex space-x-4 text-sm">
                            <button 
                                type="button"
                                @click="activeTab = 'modules'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                :class="activeTab === 'modules' 
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                Module Access
                            </button>
                            <button 
                                type="button"
                                @click="activeTab = 'permissions'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                :class="activeTab === 'permissions' 
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                Permissions
                            </button>
                        </nav>
                    </div>

                    {{-- Module Access Tab --}}
                    <div x-show="activeTab === 'modules'" x-cloak>
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Module Access</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Set access level for each module</p>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-blue-600 dark:text-blue-400">
                                <button type="button" wire:click="selectAllModuleAccess" class="hover:underline">Grant full access</button>
                                <span class="text-zinc-300 dark:text-zinc-600">|</span>
                                <button type="button" wire:click="deselectAll" class="hover:underline">Clear all</button>
                            </div>
                        </div>

                        <div class="grid gap-6 lg:grid-cols-2">
                            {{-- Left Column --}}
                            <div class="space-y-6">
                                @foreach(['supply_chain', 'hr'] as $groupKey)
                                    @if(isset($moduleGroups[$groupKey]))
                                        @php $group = $moduleGroups[$groupKey]; @endphp
                                        <div>
                                            <h4 class="mb-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ $group['label'] }}</h4>
                                            <div class="space-y-2">
                                                @foreach($group['modules'] as $module)
                                                    @php $currentLevel = $moduleAccessLevels[$module['key']] ?? ''; @endphp
                                                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 px-4 py-2.5 dark:border-zinc-700">
                                                        <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $module['label'] }}</span>
                                                        <select 
                                                            wire:model.live="moduleAccessLevels.{{ $module['key'] }}"
                                                            class="rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 {{ $currentLevel !== '' ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 dark:text-zinc-400' }}"
                                                        >
                                                            @foreach($accessLevelOptions as $value => $label)
                                                                <option value="{{ $value }}">{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Right Column --}}
                            <div class="space-y-6">
                                @foreach(['sales', 'finance', 'admin'] as $groupKey)
                                    @if(isset($moduleGroups[$groupKey]))
                                        @php $group = $moduleGroups[$groupKey]; @endphp
                                        <div>
                                            <h4 class="mb-2 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ $group['label'] }}</h4>
                                            <div class="space-y-2">
                                                @foreach($group['modules'] as $module)
                                                    @php $currentLevel = $moduleAccessLevels[$module['key']] ?? ''; @endphp
                                                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 px-4 py-2.5 dark:border-zinc-700">
                                                        <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $module['label'] }}</span>
                                                        <select 
                                                            wire:model.live="moduleAccessLevels.{{ $module['key'] }}"
                                                            class="rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 {{ $currentLevel !== '' ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 dark:text-zinc-400' }}"
                                                        >
                                                            @foreach($accessLevelOptions as $value => $label)
                                                                <option value="{{ $value }}">{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Permissions Tab --}}
                    <div x-show="activeTab === 'permissions'" x-cloak>
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Fine-grained permissions</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Pick additional abilities beyond module access</p>
                            </div>
                            <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $totalPermissions }} total permissions</span>
                        </div>

                        @if($otherPermissionGroups->isEmpty())
                            <div class="rounded-xl border border-dashed border-zinc-200 px-5 py-8 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                No additional permissions available.
                            </div>
                        @else
                            <div class="space-y-5">
                                @foreach($otherPermissionGroups as $group => $perms)
                                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-800">
                                        <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-2 dark:border-zinc-800">
                                            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">
                                                <flux:icon name="rectangle-stack" class="size-4" />
                                                {{ ucfirst($group) }}
                                            </div>
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $perms->count() }} permissions</span>
                                        </div>
                                        <div class="grid gap-2 p-4 sm:grid-cols-2">
                                            @foreach($perms as $permission)
                                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-transparent px-2 py-1.5 text-sm text-zinc-700 hover:border-zinc-200 dark:text-zinc-300 dark:hover:border-zinc-700">
                                                    <input 
                                                        type="checkbox"
                                                        wire:click="togglePermission('{{ $permission->name }}')"
                                                        @checked(in_array($permission->name, $selectedPermissions, true))
                                                        class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700"
                                                    />
                                                    <span class="truncate">{{ $permission->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

            {{-- Right Column: Panels (no timeline) --}}
            <div class="lg:col-span-3">

            <div x-show="!showSendMessage && !showLogNote && !showScheduleActivity" class="py-8 text-center" x-cloak>
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="chat-bubble-left-right" class="size-6 text-zinc-400" />
                </div>
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No messages yet</p>
                <p class="text-xs text-zinc-400 dark:text-zinc-500">Use the icons above to send a message or log a note</p>
            </div>

            {{-- Chatter Forms --}}
            <x-ui.chatter-forms />
            </div>
        </div>
</div>
