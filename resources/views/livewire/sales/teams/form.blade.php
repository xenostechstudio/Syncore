<div x-data="{ 
    activeTab: 'details',
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false
}"
@open-archive-modal.window="$wire.openArchiveModal()"
@open-delete-modal.window="$wire.openDeleteModal()"
@restore-team.window="$wire.restore()"
>
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.teams.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        Sales Team
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $teamId ? $name : 'New Sales Team' }}
                        </span>
                        @if($teamId)
                            <flux:dropdown position="bottom" align="start">
                                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="cog-6-tooth" class="size-4" />
                                </button>
                                <flux:menu class="w-48">
                                    @if($is_active)
                                        <button type="button" @click="$dispatch('open-archive-modal')" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/20">
                                            <flux:icon name="archive-box" class="size-4" />
                                            <span>Archive</span>
                                        </button>
                                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <flux:icon name="document-duplicate" class="size-4" />
                                            <span>Duplicate</span>
                                        </button>
                                    @else
                                        <button type="button" @click="$dispatch('restore-team')" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-900/20">
                                            <flux:icon name="archive-box-arrow-down" class="size-4" />
                                            <span>Restore</span>
                                        </button>
                                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <flux:icon name="document-duplicate" class="size-4" />
                                            <span>Duplicate</span>
                                        </button>
                                        <flux:menu.separator />
                                        <button type="button" @click="$dispatch('open-delete-modal')" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                            <flux:icon name="trash" class="size-4" />
                                            <span>Delete Permanently</span>
                                        </button>
                                    @endif
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
            {{-- Left: Action Buttons --}}
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
                </div>

                {{-- Status Badge --}}
                <div class="hidden items-center lg:flex">
                    @if($is_active)
                        <span class="inline-flex h-[38px] items-center gap-1.5 rounded-lg bg-emerald-100 px-4 text-sm font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            </span>
                            Active
                        </span>
                    @else
                        <span class="inline-flex h-[38px] items-center gap-1.5 rounded-lg bg-amber-100 px-4 text-sm font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            <flux:icon name="archive-box" class="size-4" />
                            Archived
                        </span>
                    @endif
                </div>
            </div>

            {{-- Right: Chatter Icons --}}
            <div class="col-span-3">
                <x-ui.chatter-buttons :showMessage="false" />
            </div>
        </div>
    </div>

    {{-- Delete Modal (Only for archived records) --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-zinc-900/60" wire:click="$set('showDeleteModal', false)"></div>
        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon name="exclamation-triangle" class="size-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Delete Permanently</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">This action cannot be undone.</p>
                </div>
            </div>
            <p class="mb-4 text-sm text-zinc-600 dark:text-zinc-400">
                Are you sure you want to permanently delete this sales team? This will remove all associated data and cannot be recovered.
            </p>
            <div class="mb-6 rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
                <p class="text-xs text-red-700 dark:text-red-400">
                    <strong>Warning:</strong> Historical references to this team in orders and reports will be lost.
                </p>
            </div>
            <div class="flex justify-end gap-3">
                <button 
                    type="button"
                    wire:click="$set('showDeleteModal', false)"
                    class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                >
                    Cancel
                </button>
                <button 
                    type="button"
                    wire:click="delete"
                    wire:loading.attr="disabled"
                    wire:target="delete"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 disabled:opacity-50"
                >
                    <flux:icon name="trash" wire:loading.remove wire:target="delete" class="size-4" />
                    <flux:icon name="arrow-path" wire:loading wire:target="delete" class="size-4 animate-spin" />
                    <span wire:loading.remove wire:target="delete">Delete Permanently</span>
                    <span wire:loading wire:target="delete">Deleting...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Archive Modal --}}
    @if($showArchiveModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-zinc-900/60" wire:click="$set('showArchiveModal', false)"></div>
        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                    <flux:icon name="archive-box" class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Archive Sales Team</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">This team will be hidden from active lists.</p>
                </div>
            </div>
            <p class="mb-4 text-sm text-zinc-600 dark:text-zinc-400">
                Archiving this team will:
            </p>
            <ul class="mb-6 space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                <li class="flex items-start gap-2">
                    <flux:icon name="check" class="mt-0.5 size-4 text-zinc-400" />
                    <span>Hide from selection dropdowns and active lists</span>
                </li>
                <li class="flex items-start gap-2">
                    <flux:icon name="check" class="mt-0.5 size-4 text-zinc-400" />
                    <span>Preserve historical data and references</span>
                </li>
                <li class="flex items-start gap-2">
                    <flux:icon name="check" class="mt-0.5 size-4 text-zinc-400" />
                    <span>Allow restoration at any time</span>
                </li>
            </ul>
            <div class="flex justify-end gap-3">
                <button 
                    type="button"
                    wire:click="$set('showArchiveModal', false)"
                    class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                >
                    Cancel
                </button>
                <button 
                    type="button"
                    wire:click="archive"
                    wire:loading.attr="disabled"
                    wire:target="archive"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-amber-700 disabled:opacity-50"
                >
                    <flux:icon name="archive-box" wire:loading.remove wire:target="archive" class="size-4" />
                    <flux:icon name="arrow-path" wire:loading wire:target="archive" class="size-4 animate-spin" />
                    <span wire:loading.remove wire:target="archive">Archive</span>
                    <span wire:loading wire:target="archive">Archiving...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Main Form --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    {{-- Header Section --}}
                    <div class="p-5">
                        {{-- Big Title --}}
                        <div class="mb-6">
                            <input 
                                type="text"
                                wire:model="name"
                                placeholder="Sales Team Name"
                                class="w-full border-0 bg-transparent p-0 text-2xl font-semibold text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:placeholder-zinc-500"
                            />
                        </div>

                        {{-- Form Fields --}}
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                            {{-- Left Column --}}
                            <div class="space-y-4">
                                {{-- Team Leader (Searchable) --}}
                                <div>
                                    <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Team Leader</label>
                                    <div class="relative" x-data="{ open: false, search: '' }">
                                        <button 
                                            type="button"
                                            @click="open = !open; $nextTick(() => { if(open) $refs.leaderSearch.focus() })"
                                            class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-left text-sm transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                        >
                                            @if($leader_id)
                                                @php $leader = $users->find($leader_id); @endphp
                                                @if($leader)
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                            {{ strtoupper(substr($leader->name, 0, 2)) }}
                                                        </div>
                                                        <div>
                                                            <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $leader->name }}</p>
                                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $leader->email }}</p>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-zinc-400">Select leader...</span>
                                                @endif
                                            @else
                                                <span class="text-zinc-400">Select leader...</span>
                                            @endif
                                            <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                        </button>
                                        <div 
                                            x-show="open" 
                                            @click.outside="open = false; search = ''"
                                            x-transition
                                            class="absolute left-0 top-full z-[100] mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                        >
                                            <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                                <input 
                                                    type="text"
                                                    x-ref="leaderSearch"
                                                    x-model="search"
                                                    placeholder="Search users..."
                                                    class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                                    @keydown.escape="open = false; search = ''"
                                                />
                                            </div>
                                            <div class="max-h-60 overflow-auto py-1">
                                                <button 
                                                    type="button" 
                                                    wire:click="$set('leader_id', null)" 
                                                    @click="open = false; search = ''"
                                                    class="flex w-full items-center gap-3 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                                >
                                                    <span class="text-zinc-400">None</span>
                                                </button>
                                                @foreach($users as $user)
                                                    <button 
                                                        type="button" 
                                                        wire:click="$set('leader_id', {{ $user->id }})" 
                                                        @click="open = false; search = ''"
                                                        x-show="!search || '{{ strtolower($user->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($user->email) }}'.includes(search.toLowerCase())"
                                                        class="flex w-full items-center gap-3 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                                    >
                                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="truncate text-zinc-900 dark:text-zinc-100">{{ $user->name }}</p>
                                                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                                                        </div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Target Amount --}}
                                <div>
                                    <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Target Amount</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400">Rp</span>
                                        <input 
                                            type="number"
                                            wire:model="target_amount"
                                            placeholder="0"
                                            class="w-full rounded-lg border border-zinc-200 bg-white py-2.5 pl-10 pr-4 text-sm text-zinc-900 placeholder-zinc-400 transition-colors [appearance:textfield] hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Headers --}}
                    <div class="flex items-center border-y border-zinc-100 dark:border-zinc-800">
                        <button 
                            type="button"
                            @click="activeTab = 'details'"
                            :class="activeTab === 'details' ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                            class="px-5 py-3 text-sm font-medium transition-colors"
                        >
                            Details
                        </button>
                        <button 
                            type="button"
                            @click="activeTab = 'members'"
                            :class="activeTab === 'members' ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                            class="px-5 py-3 text-sm font-medium transition-colors"
                        >
                            Members
                            @if(count($member_ids) > 0)
                                <span class="ml-1.5 rounded-full bg-zinc-200 px-2 py-0.5 text-xs dark:bg-zinc-700">{{ count($member_ids) }}</span>
                            @endif
                        </button>
                    </div>

                    {{-- Tab Content: Details --}}
                    <div x-show="activeTab === 'details'" class="p-5">
                        <div class="space-y-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
                                <textarea 
                                    wire:model="description"
                                    rows="4"
                                    placeholder="Enter team description..."
                                    class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Content: Members --}}
                    <div x-show="activeTab === 'members'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="grid grid-cols-2 divide-x divide-zinc-100 dark:divide-zinc-800">
                            {{-- Left: Selected Members --}}
                            <div class="p-4">
                                <div class="mb-3 flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                        Team Members
                                        @if($this->selectedMembers->count() > 0)
                                            <span class="ml-1.5 rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ $this->selectedMembers->count() }}</span>
                                        @endif
                                    </h4>
                                </div>
                                
                                <div class="space-y-2">
                                    @forelse($this->selectedMembers as $member)
                                        <div class="group flex items-center justify-between rounded-lg border border-zinc-100 bg-zinc-50/50 px-3 py-2.5 transition-colors hover:border-zinc-200 hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800/50 dark:hover:border-zinc-700 dark:hover:bg-zinc-800" wire:key="selected-{{ $member->id }}">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-purple-600 text-xs font-medium text-white shadow-sm">
                                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $member->name }}</p>
                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $member->email }}</p>
                                                </div>
                                            </div>
                                            <button 
                                                type="button" 
                                                wire:click="removeMember({{ $member->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="removeMember({{ $member->id }})"
                                                class="rounded-lg p-1.5 text-zinc-400 opacity-0 transition-all hover:bg-red-50 hover:text-red-500 group-hover:opacity-100 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                                            >
                                                <flux:icon name="x-mark" wire:loading.remove wire:target="removeMember({{ $member->id }})" class="size-4" />
                                                <flux:icon name="arrow-path" wire:loading wire:target="removeMember({{ $member->id }})" class="size-4 animate-spin" />
                                            </button>
                                        </div>
                                    @empty
                                        <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-zinc-200 py-8 dark:border-zinc-700">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                                <flux:icon name="users" class="size-6 text-zinc-400" />
                                            </div>
                                            <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No members added yet</p>
                                            <p class="text-xs text-zinc-400 dark:text-zinc-500">Select from available users on the right</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Right: Available Members --}}
                            <div class="p-4">
                                <div class="mb-3">
                                    <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Available Users</h4>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Users without a team assignment</p>
                                </div>
                                
                                {{-- Search --}}
                                <div class="mb-3">
                                    <div class="relative">
                                        <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                        <input 
                                            type="text"
                                            wire:model.live.debounce.300ms="memberSearch"
                                            placeholder="Search by name or email..."
                                            class="w-full rounded-lg border border-zinc-200 bg-white py-2 pl-9 pr-4 text-sm placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"
                                        />
                                        @if($memberSearch)
                                            <button 
                                                type="button" 
                                                wire:click="$set('memberSearch', '')"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                                            >
                                                <flux:icon name="x-mark" class="size-4" />
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Available Users List --}}
                                <div class="space-y-1.5">
                                    @forelse($this->availableUsers as $user)
                                        <button 
                                            type="button"
                                            wire:click="addMember({{ $user->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="addMember({{ $user->id }})"
                                            class="group flex w-full items-center justify-between rounded-lg border border-transparent px-3 py-2.5 text-left transition-all hover:border-zinc-200 hover:bg-zinc-50 disabled:opacity-50 dark:hover:border-zinc-700 dark:hover:bg-zinc-800/50"
                                            wire:key="available-{{ $user->id }}"
                                        >
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $user->name }}</p>
                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 text-zinc-400 opacity-0 transition-opacity group-hover:opacity-100">
                                                <flux:icon name="plus" wire:loading.remove wire:target="addMember({{ $user->id }})" class="size-4" />
                                                <flux:icon name="arrow-path" wire:loading wire:target="addMember({{ $user->id }})" class="size-4 animate-spin" />
                                                <span wire:loading.remove wire:target="addMember({{ $user->id }})" class="text-xs">Add</span>
                                            </div>
                                        </button>
                                    @empty
                                        <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-zinc-200 py-6 dark:border-zinc-700">
                                            <flux:icon name="user-group" class="size-8 text-zinc-300 dark:text-zinc-600" />
                                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                                @if($memberSearch)
                                                    No users found matching "{{ $memberSearch }}"
                                                @else
                                                    All users are assigned to teams
                                                @endif
                                            </p>
                                        </div>
                                    @endforelse
                                </div>
                                
                                {{-- Pagination --}}
                                @if($this->availableUsers->hasPages())
                                    <div class="mt-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                        <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                            <span>Showing {{ $this->availableUsers->firstItem() }}-{{ $this->availableUsers->lastItem() }} of {{ $this->availableUsers->total() }}</span>
                                            <div class="flex items-center gap-1">
                                                @if($this->availableUsers->onFirstPage())
                                                    <span class="rounded px-2 py-1 text-zinc-300 dark:text-zinc-600">Previous</span>
                                                @else
                                                    <button type="button" wire:click="previousPage('availableMembers')" class="rounded px-2 py-1 hover:bg-zinc-100 dark:hover:bg-zinc-800">Previous</button>
                                                @endif
                                                
                                                @if($this->availableUsers->hasMorePages())
                                                    <button type="button" wire:click="nextPage('availableMembers')" class="rounded px-2 py-1 hover:bg-zinc-100 dark:hover:bg-zinc-800">Next</button>
                                                @else
                                                    <span class="rounded px-2 py-1 text-zinc-300 dark:text-zinc-600">Next</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Activity Timeline --}}
            <div class="lg:col-span-3">
                {{-- Chatter Forms --}}
                <x-ui.chatter-forms :showMessage="false" />

                {{-- Activity Timeline --}}
                @if($teamId)
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
                                {{-- Note Item - Compact --}}
                                <x-ui.note-item :note="$item['data']" />
                            @else
                                {{-- Activity Log Item --}}
                                <x-ui.activity-item :activity="$item['data']" emptyMessage="Sales team created" />
                            @endif
                        @empty
                            {{-- Team Created (fallback when no activities yet) --}}
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-ui.user-name :user="auth()->user()" />
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $createdAt ?? now()->format('H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Sales team created</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @else
                    {{-- Empty State for New Team --}}
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
