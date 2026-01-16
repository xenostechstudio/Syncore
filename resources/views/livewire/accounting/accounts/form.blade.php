<div x-data="{ showLogNote: false, showSendMessage: false, showScheduleActivity: false }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            {{-- Left Group: Back Button, Title, Gear Dropdown --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('accounting.accounts.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        Chart of Accounts
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $accountId ? ($code ?: 'Edit Account') : 'New Account' }}
                        </span>

                        @if($accountId)
                            <flux:dropdown position="bottom" align="start">
                                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="cog-6-tooth" class="size-4" />
                                </button>

                                <flux:menu class="w-40">
                                    <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                        <flux:icon name="document-duplicate" class="size-4" />
                                        <span>Duplicate</span>
                                    </button>
                                    @unless($account?->is_system)
                                        <flux:menu.separator />
                                        <button type="button" wire:click="delete" wire:confirm="Delete this account?" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                            <flux:icon name="trash" class="size-4" />
                                            <span>Delete</span>
                                        </button>
                                    @endunless
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
            {{-- Left: Action Buttons (col-span-9 to align with card below) --}}
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
                @if($accountId)
                    <div class="flex items-center gap-2">
                        @if($isActive)
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                Inactive
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Right: Chatter Icons (col-span-3 to align with right column below) --}}
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

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        {{-- Two Column Layout: Form Left, Sidebar Right --}}
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Main Form --}}
            <div class="lg:col-span-9">
                {{-- Account Details Card --}}
                <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    {{-- Card Content --}}
                    <div class="p-5">
                        {{-- Title inside card --}}
                        <h1 class="mb-5 text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $accountId ? $code : 'New' }}
                        </h1>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Account Code <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="code" placeholder="e.g., 1000" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600">
                                @error('code')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Account Type <span class="text-red-500">*</span></label>
                                <select wire:model="accountType" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600">
                                    <option value="asset">Asset</option>
                                    <option value="liability">Liability</option>
                                    <option value="equity">Equity</option>
                                    <option value="revenue">Revenue</option>
                                    <option value="expense">Expense</option>
                                </select>
                                @error('accountType')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Account Name <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="name" placeholder="e.g., Cash on Hand" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600">
                                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Parent Account</label>
                                <select wire:model="parentId" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600">
                                    <option value="">None (Top Level)</option>
                                    @foreach($parentAccounts as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->code }} - {{ $parent->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Description</label>
                                <textarea wire:model="description" rows="3" placeholder="Optional description..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Settings Section --}}
                    <div class="border-t border-zinc-100 p-5 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Active Status</label>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Enable or disable this account</p>
                            </div>
                            <button 
                                type="button" 
                                wire:click="$toggle('isActive')"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $isActive ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-zinc-200 dark:bg-zinc-700' }}"
                            >
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out dark:bg-zinc-900 {{ $isActive ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Sidebar --}}
            <div class="lg:col-span-3 space-y-6">
                {{-- Account Info (only for existing accounts) --}}
                @if($accountId && $account)
                    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Account Info</h3>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Balance</span>
                                <span class="font-medium {{ ($account->balance ?? 0) >= 0 ? 'text-zinc-900 dark:text-zinc-100' : 'text-red-600 dark:text-red-400' }}">
                                    Rp {{ number_format(abs($account->balance ?? 0), 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Created</span>
                                <span class="text-zinc-900 dark:text-zinc-100">{{ $account->created_at->format('M d, Y') }}</span>
                            </div>
                            @if($account->is_system)
                                <div class="mt-2 rounded-lg bg-amber-50 p-3 text-xs text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">
                                    <flux:icon name="exclamation-triangle" class="inline size-4" />
                                    This is a system account and cannot be deleted.
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Log Note Panel --}}
                <div 
                    x-show="showLogNote" 
                    x-transition
                    class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Log Note</h3>
                    </div>
                    <div class="p-4">
                        <textarea rows="3" placeholder="Write a note..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                        <div class="mt-3 flex justify-end">
                            <button type="button" class="rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                                Log
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Send Message Panel --}}
                <div 
                    x-show="showSendMessage" 
                    x-transition
                    class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Send Message</h3>
                    </div>
                    <div class="p-4">
                        <textarea rows="3" placeholder="Write a message..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                        <div class="mt-3 flex justify-end">
                            <button type="button" class="rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                                Send
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Schedule Activity Panel --}}
                <div 
                    x-show="showScheduleActivity" 
                    x-transition
                    class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Schedule Activity</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Activity Type</label>
                            <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                <option>To Do</option>
                                <option>Call</option>
                                <option>Meeting</option>
                                <option>Email</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Due Date</label>
                            <input type="date" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Summary</label>
                            <textarea rows="2" placeholder="Activity summary..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" class="rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                                Schedule
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
