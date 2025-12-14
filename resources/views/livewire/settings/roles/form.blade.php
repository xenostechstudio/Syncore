<div x-data="{ activeTab: 'modules', showSendMessage: false, showLogNote: false, showScheduleActivity: false }" x-cloak>
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

    {{-- Header --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('settings.roles.index') }}" wire:navigate class="inline-flex items-center justify-center rounded-md p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div>
                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $roleId ? 'Edit Role' : 'Create Role' }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $roleId ? 'Update role name & module permissions' : 'Assign access before inviting team members' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Bar (like Product form) --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
            {{-- Left: Actions --}}
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button 
                        type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        <flux:icon name="document-check" class="size-4" />
                        <span wire:loading.remove wire:target="save">Save</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>

                    <a 
                        href="{{ route('settings.roles.index') }}"
                        wire:navigate
                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        <flux:icon name="x-mark" class="size-4" />
                        Cancel
                    </a>

                    @if($roleId)
                        <button 
                            type="button"
                            wire:click="delete"
                            wire:confirm="Delete this role? This action cannot be undone."
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-900/30 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20"
                        >
                            <flux:icon name="trash" class="size-4" />
                            Delete
                        </button>
                    @endif
                </div>
            </div>

            {{-- Right: Chatter Icons --}}
            <div class="col-span-3 flex items-center justify-end gap-1">
                <button 
                    @click="showSendMessage = !showSendMessage; showLogNote = false; showScheduleActivity = false" 
                    :class="showSendMessage ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'"
                    class="rounded-lg p-2 transition-colors" 
                    title="Send message"
                >
                    <flux:icon name="chat-bubble-left" class="size-5" />
                </button>
                <button 
                    @click="showLogNote = !showLogNote; showSendMessage = false; showScheduleActivity = false" 
                    :class="showLogNote ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'"
                    class="rounded-lg p-2 transition-colors" 
                    title="Log note"
                >
                    <flux:icon name="pencil-square" class="size-5" />
                </button>
                <button 
                    @click="showScheduleActivity = !showScheduleActivity; showSendMessage = false; showLogNote = false" 
                    :class="showScheduleActivity ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'"
                    class="rounded-lg p-2 transition-colors" 
                    title="Schedule activity"
                >
                    <flux:icon name="clock" class="size-5" />
                </button>
            </div>
        </div>
    </div>

    {{-- Main Content (like Product form) --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
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
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Module Access</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Grant entry to major modules</p>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-blue-600 dark:text-blue-400">
                                <button type="button" wire:click="selectAllModuleAccess" class="hover:underline">Select all</button>
                                <span class="text-zinc-300 dark:text-zinc-600">|</span>
                                <button type="button" wire:click="deselectAll" class="hover:underline">Clear</button>
                            </div>
                        </div>

                        <div class="grid gap-3">
                            @foreach($moduleCards as $card)
                                @php
                                    $isActive = in_array($card['permission'], $selectedPermissions, true);
                                @endphp
                                <button
                                    type="button"
                                    wire:click="togglePermission('{{ $card['permission'] }}')"
                                    class="group flex items-center justify-between rounded-2xl border px-4 py-3 text-left transition {{ $isActive ? 'border-'.$card['color'].'-500 bg-'.$card['color'].'-50 dark:border-'.$card['color'].'-400/60 dark:bg-'.$card['color'].'-950/40' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-800 dark:hover:border-zinc-700' }}"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $card['color'] }}-100 text-{{ $card['color'] }}-600 dark:bg-{{ $card['color'] }}-900/30 dark:text-{{ $card['color'] }}-300">
                                            <flux:icon name="{{ $card['icon'] }}" class="size-5" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $card['label'] }}</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $card['description'] }}</p>
                                        </div>
                                    </div>
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full border {{ $isActive ? 'border-'.$card['color'].'-500 bg-white text-'.$card['color'].'-600 dark:border-'.$card['color'].'-400 dark:text-'.$card['color'].'-300' : 'border-zinc-300 text-transparent dark:border-zinc-600' }}">
                                        <flux:icon name="check" class="size-4" />
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Module Access Tab --}}
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

            <div x-show="showSendMessage" x-collapse class="mb-4">
                <div class="flex gap-3">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        <flux:icon name="chat-bubble-left" class="size-4" />
                    </div>
                    <div class="flex-1">
                        <textarea 
                            rows="3"
                            placeholder="Send a message to followers..."
                            class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-blue-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"
                        ></textarea>
                        <div class="mt-2 flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Attach file">
                                    <flux:icon name="paper-clip" class="size-4" />
                                </button>
                                <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Mention">
                                    <flux:icon name="at-symbol" class="size-4" />
                                </button>
                            </div>
                            <button type="button" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-blue-700">
                                Send
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="showLogNote" x-collapse class="mb-4">
                <div class="flex gap-3">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                        <flux:icon name="pencil-square" class="size-4" />
                    </div>
                    <div class="flex-1">
                        <textarea 
                            wire:model.defer="noteDraft"
                            rows="3"
                            placeholder="Log an internal note..."
                            class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-amber-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"
                        ></textarea>
                        <div class="mt-2 flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Attach file">
                                    <flux:icon name="paper-clip" class="size-4" />
                                </button>
                                <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Mention">
                                    <flux:icon name="at-symbol" class="size-4" />
                                </button>
                            </div>
                            <button 
                                type="button"
                                wire:click="addNote"
                                wire:loading.attr="disabled"
                                class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-amber-700 disabled:opacity-50"
                            >
                                Log Note
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="showScheduleActivity" x-collapse class="mb-4">
                <div class="flex gap-3">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400">
                        <flux:icon name="clock" class="size-4" />
                    </div>
                    <div class="flex-1 space-y-3">
                        <div>
                            <label class="mb-1 block text-xs text-zinc-500 dark:text-zinc-400">Activity Type</label>
                            <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-violet-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                <option value="">Select activity type...</option>
                                <option value="call">Call</option>
                                <option value="meeting">Meeting</option>
                                <option value="todo">To-Do</option>
                                <option value="email">Email</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-zinc-500 dark:text-zinc-400">Due Date</label>
                            <input type="date" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-violet-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-zinc-500 dark:text-zinc-400">Summary</label>
                            <input type="text" placeholder="Activity summary..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-violet-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                        </div>
                        <div class="flex justify-end">
                            <button type="button" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-violet-700">
                                Schedule
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>
