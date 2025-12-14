<div
    x-data="{ showSendMessage: false, showLogNote: false, showScheduleActivity: false, activeTab: 'access', showChangePasswordModal: @js($errors->has('password') || $errors->has('password_confirmation') || $errors->has('current_password')), showCancelModal: false }"
    x-on:open-change-password-modal.window="activeTab = 'security'; showChangePasswordModal = true"
>
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('settings.users.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        User
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $userId ? $name : 'New User' }}
                        </span>

                        <flux:dropdown position="bottom" align="start">
                            <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                <flux:icon name="cog-6-tooth" class="size-4" />
                            </button>

                            <flux:menu class="w-40">
                                @if($userId)
                                    <button
                                        type="button"
                                        wire:click="delete"
                                        wire:confirm="Are you sure you want to delete this user?"
                                        class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                    >
                                        <flux:icon name="trash" class="size-4" />
                                        <span>Delete</span>
                                    </button>
                                @else
                                    <div class="px-2 py-1.5 text-sm text-zinc-500 dark:text-zinc-400">
                                        No actions
                                    </div>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:header>

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

                    <button 
                        type="button"
                        @click="showCancelModal = true"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        <flux:icon name="x-mark" class="size-4" />
                        Cancel
                    </button>
                    @if($userId)
                        <button 
                            type="button"
                            wire:click="delete"
                            wire:confirm="Are you sure you want to delete this user?"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20"
                        >
                            <flux:icon name="trash" class="size-4" />
                            Delete
                        </button>
                    @endif
                </div>

                <div class="hidden items-center lg:flex">
                    @if($is_active)
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <flux:icon name="check-circle" class="mr-1 size-3" />
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                            Inactive
                        </span>
                    @endif
                </div>
            </div>

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

    <x-ui.confirm-modal show="showCancelModal">
        <x-slot:icon>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                <flux:icon name="exclamation-triangle" class="size-6" />
            </div>
        </x-slot:icon>

        <x-slot:title>
            Discard changes?
        </x-slot:title>

        <x-slot:description>
            If you leave this page, any unsaved changes to this user will be lost.
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
                href="{{ route('settings.users.index') }}"
                wire:navigate
                @click="showCancelModal = false"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600"
            >
                Discard & leave
            </a>
        </x-slot:actions>
    </x-ui.confirm-modal>

    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    {{-- Profile Header Section --}}
                    <div class="p-5">
                        <div class="flex items-start gap-6">
                            {{-- Profile Image Placeholder --}}
                            <div class="relative flex-shrink-0">
                                <div class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                    @if($userId && $name)
                                        <span class="text-4xl font-medium text-zinc-400 dark:text-zinc-500">
                                            {{ strtoupper(substr($name, 0, 2)) }}
                                        </span>
                                    @else
                                        <flux:icon name="user" class="size-12 text-zinc-300 dark:text-zinc-600" />
                                    @endif
                                </div>
                                <button type="button" class="absolute -bottom-1 -right-1 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-zinc-100 text-zinc-500 transition-colors hover:bg-zinc-200 dark:border-zinc-900 dark:bg-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-600" title="Change photo">
                                    <flux:icon name="camera" class="size-4" />
                                </button>
                            </div>

                            {{-- Name, Email, Phone --}}
                            <div class="flex-1 space-y-3">
                                {{-- Full Name (Big Input) --}}
                                <div>
                                    <input 
                                        type="text"
                                        wire:model="name"
                                        placeholder="Full Name"
                                        class="w-full rounded-lg border border-transparent bg-transparent px-3 py-2 text-3xl font-bold text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-200 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-700 dark:focus:border-zinc-700"
                                    />
                                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Email --}}
                                <div class="flex items-center gap-3">
                                    <flux:icon name="envelope" class="size-5 flex-shrink-0 text-zinc-400" />
                                    <input 
                                        type="email"
                                        wire:model="email"
                                        placeholder="Email address for login"
                                        class="flex-1 border-0 border-b border-transparent bg-transparent px-0 py-1 text-sm text-zinc-700 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-300 dark:placeholder-zinc-500 dark:hover:border-zinc-700"
                                    />
                                </div>
                                @error('email') <p class="ml-8 text-xs text-red-500">{{ $message }}</p> @enderror

                                {{-- Phone --}}
                                <div class="flex items-center gap-3">
                                    <flux:icon name="phone" class="size-5 flex-shrink-0 text-zinc-400" />
                                    <input 
                                        type="tel"
                                        wire:model="phone"
                                        placeholder="Phone number"
                                        class="flex-1 border-0 border-b border-transparent bg-transparent px-0 py-1 text-sm text-zinc-700 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-300 dark:placeholder-zinc-500 dark:hover:border-zinc-700"
                                    />
                                </div>
                                @error('phone') <p class="ml-8 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Tab Headers --}}
                    <div class="mx-5 mb-4 border-b border-zinc-200 dark:border-zinc-800">
                        <nav class="-mb-px flex space-x-4 text-sm">
                            <button 
                                type="button"
                                @click="activeTab = 'access'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                :class="activeTab === 'access' 
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                Access Rights
                            </button>
                            <button 
                                type="button"
                                @click="activeTab = 'preferences'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                :class="activeTab === 'preferences' 
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                Preferences
                            </button>
                            <button 
                                type="button"
                                @click="activeTab = 'calendar'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                :class="activeTab === 'calendar' 
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                Calendar
                            </button>
                            <button 
                                type="button"
                                @click="activeTab = 'security'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                :class="activeTab === 'security' 
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                Security
                            </button>
                        </nav>
                    </div>

                    {{-- Tab Content: Access Rights --}}
                    <div x-show="activeTab === 'access'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="px-5 pb-5">
                            <div class="grid gap-8 lg:grid-cols-2">
                                {{-- User Type --}}
                                <div class="space-y-4">
                                    <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">User Type</h3>
                                    <div class="space-y-3">
                                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-200 p-3 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/50">
                                            <input type="radio" wire:model="user_type" value="internal" class="text-zinc-900 focus:ring-zinc-500 dark:bg-zinc-700" />
                                            <div>
                                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Internal User</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Full access to internal applications</p>
                                            </div>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-200 p-3 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/50">
                                            <input type="radio" wire:model="user_type" value="portal" class="text-zinc-900 focus:ring-zinc-500 dark:bg-zinc-700" />
                                            <div>
                                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Portal User</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Limited access to portal features</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                {{-- Permissions --}}
                                <div class="space-y-4">
                                    <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Permissions</h3>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <label class="text-sm text-zinc-700 dark:text-zinc-300">Sales</label>
                                            <select wire:model="permissions.sales" class="rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                <option value="">No Access</option>
                                                <option value="user">User</option>
                                                <option value="manager">Manager</option>
                                                <option value="admin">Administrator</option>
                                            </select>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <label class="text-sm text-zinc-700 dark:text-zinc-300">Purchase</label>
                                            <select wire:model="permissions.purchase" class="rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                <option value="">No Access</option>
                                                <option value="user">User</option>
                                                <option value="manager">Manager</option>
                                                <option value="admin">Administrator</option>
                                            </select>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <label class="text-sm text-zinc-700 dark:text-zinc-300">Inventory</label>
                                            <select wire:model="permissions.inventory" class="rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                <option value="">No Access</option>
                                                <option value="user">User</option>
                                                <option value="manager">Manager</option>
                                                <option value="admin">Administrator</option>
                                            </select>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <label class="text-sm text-zinc-700 dark:text-zinc-300">Accounting</label>
                                            <select wire:model="permissions.accounting" class="rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                <option value="">No Access</option>
                                                <option value="user">User</option>
                                                <option value="manager">Manager</option>
                                                <option value="admin">Administrator</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Status --}}
                                <div class="space-y-4">
                                    <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</h3>
                                    <label class="flex cursor-pointer items-center gap-3">
                                        <input 
                                            type="checkbox"
                                            wire:model="is_active"
                                            class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700"
                                        />
                                        <span class="text-sm text-zinc-700 dark:text-zinc-300">Active User</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Content: Preferences --}}
                    <div x-show="activeTab === 'preferences'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="px-5 pb-5">
                            <div class="space-y-8">
                                {{-- Localization --}}
                                <div class="space-y-4">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                        <div class="lg:w-72">
                                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Localization</h3>
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Set the language and timezone used in the interface.</p>
                                        </div>

                                        <div class="flex-1 space-y-4">
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Language</label>
                                                <select wire:model="language" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                    <option value="en">English</option>
                                                    <option value="id">Indonesian</option>
                                                    <option value="es">Spanish</option>
                                                    <option value="fr">French</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Timezone</label>
                                                <select wire:model="timezone" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                    <option value="Asia/Jakarta">Asia/Jakarta (GMT+7)</option>
                                                    <option value="Asia/Singapore">Asia/Singapore (GMT+8)</option>
                                                    <option value="America/New_York">America/New York (EST)</option>
                                                    <option value="Europe/London">Europe/London (GMT)</option>
                                                    <option value="UTC">UTC</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Signature --}}
                                <div class="space-y-4">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                        <div class="lg:w-72">
                                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Email Signature</h3>
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Signature appended to emails sent from the system.</p>
                                        </div>

                                        <div class="flex-1">
                                            <textarea 
                                                wire:model="signature"
                                                rows="4"
                                                placeholder="Your email signature..."
                                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Content: Calendar --}}
                    <div x-show="activeTab === 'calendar'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="px-5 pb-5">
                            <div class="grid gap-8 lg:grid-cols-2">
                                {{-- Working Hours --}}
                                <div class="space-y-4">
                                    <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Working Hours</h3>
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-4">
                                            <label class="w-24 text-sm text-zinc-600 dark:text-zinc-400">Start Time</label>
                                            <input type="time" wire:model="working_hours.start" class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <label class="w-24 text-sm text-zinc-600 dark:text-zinc-400">End Time</label>
                                            <input type="time" wire:model="working_hours.end" class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                        </div>
                                    </div>
                                </div>

                                {{-- Working Days --}}
                                <div class="space-y-4">
                                    <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Working Days</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                                                <input type="checkbox" wire:model="working_days" value="{{ strtolower($day) }}" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                                <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $day }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Out of Office --}}
                                <div class="space-y-4 lg:col-span-2">
                                    <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Out of Office</h3>
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-4">
                                            <label class="w-24 text-sm text-zinc-600 dark:text-zinc-400">Date</label>
                                            <input
                                                type="date"
                                                wire:model="out_of_office_date"
                                                placeholder="None planned"
                                                class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            />
                                            <span class="text-sm text-zinc-500 dark:text-zinc-400">None planned</span>
                                        </div>

                                        <div class="space-y-2">
                                            <label class="text-sm text-zinc-600 dark:text-zinc-400">Your out of office message</label>
                                            <textarea
                                                wire:model="out_of_office_message"
                                                rows="3"
                                                placeholder="Your out of office message"
                                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Content: Security --}}
                    <div x-show="activeTab === 'security'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="px-5 pb-5">
                            <div class="space-y-8">
                                {{-- Change Password --}}
                                <div class="space-y-4">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                        <div class="lg:w-72">
                                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Change Password</h3>
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Update this user’s password.</p>
                                        </div>

                                        <div class="flex-1">
                                            <button 
                                                type="button" 
                                                @click="showChangePasswordModal = true"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                                            >
                                                <flux:icon name="key" class="size-3.5" />
                                                Change Password
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Two-Factor Authentication --}}
                                <div class="space-y-4">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                        <div class="lg:w-72">
                                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Two-Factor Authentication</h3>
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Add an extra layer of security by enabling 2FA.</p>
                                        </div>

                                        <div class="flex-1">
                                            <button type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                                                <flux:icon name="shield-check" class="size-3.5" />
                                                Enable 2FA
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Active Sessions / Devices --}}
                                <div class="space-y-4">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                        <div class="lg:w-72">
                                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Active Sessions</h3>
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Devices currently logged in to this account.</p>
                                        </div>

                                        <div class="flex-1">
                                            <div class="mt-3 space-y-3">
                                                {{-- Current Device --}}
                                                <div class="flex items-center gap-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                                                        <flux:icon name="computer-desktop" class="size-5 text-emerald-600 dark:text-emerald-400" />
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2">
                                                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Current Device</p>
                                                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                                                        </div>
                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Chrome on macOS • Last active: Now</p>
                                                    </div>
                                                </div>

                                                {{-- Other Device Example --}}
                                                <div class="flex items-center gap-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                                        <flux:icon name="device-phone-mobile" class="size-5 text-zinc-500 dark:text-zinc-400" />
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">iPhone 14 Pro</p>
                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Safari on iOS • Last active: 2 hours ago</p>
                                                    </div>
                                                    <button type="button" class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                                        Revoke
                                                    </button>
                                                </div>

                                                <div class="pt-1">
                                                    <button type="button" class="inline-flex items-center rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
                                                        Log out all
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div x-show="showSendMessage" x-collapse class="mb-4">
                    <div class="flex gap-3">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                            <flux:icon name="chat-bubble-left" class="size-4" />
                        </div>
                        <div class="flex-1">
                            <textarea rows="3" placeholder="Send a message to followers..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-blue-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"></textarea>
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
                            <textarea rows="3" placeholder="Log an internal note..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-amber-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"></textarea>
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Attach file">
                                        <flux:icon name="paper-clip" class="size-4" />
                                    </button>
                                    <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Mention">
                                        <flux:icon name="at-symbol" class="size-4" />
                                    </button>
                                </div>
                                <button type="button" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-amber-700">
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

                @if($userId)
                    <div class="flex items-center gap-3 py-2">
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Today</span>
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    </div>

                    <div class="space-y-4">
                        @if(isset($activityLog) && count($activityLog) > 0)
                            @foreach($activityLog as $activity)
                                <div class="flex gap-3">
                                    <div class="relative flex-shrink-0">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-200 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                            {{ strtoupper(substr($activity['user'] ?? 'U', 0, 2)) }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $activity['user'] }}</span>
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $activity['time'] }}</span>
                                        </div>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $activity['message'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @else
                    <div class="py-8 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon name="chat-bubble-left-right" class="size-6 text-zinc-400" />
                        </div>
                        <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No activity yet</p>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500">Activity will appear here once the user is saved</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div 
        x-show="showChangePasswordModal" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showChangePasswordModal = false"></div>

        <div 
            class="relative z-10 w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-xl dark:bg-zinc-900"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="showChangePasswordModal = false"
        >
            <div class="px-6 pb-4 pt-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Change Password</h3>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Set a new password for this user.</p>
                    </div>
                    <button type="button" @click="showChangePasswordModal = false" class="rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <div class="mt-6 space-y-4">
                    @if($userId)
                        <div>
                            <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Current Password</label>
                            <input type="password" wire:model="current_password" placeholder="Enter current password" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                        </div>
                    @endif

                    <div>
                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">{{ $userId ? 'New Password' : 'Password' }}</label>
                        <input type="password" wire:model="password" placeholder="Minimum 8 characters" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                        @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Confirm Password</label>
                        <input type="password" wire:model="password_confirmation" placeholder="Confirm password" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/50">
                <button 
                    type="button"
                    @click="showChangePasswordModal = false"
                    class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                >
                    Cancel
                </button>

                <button 
                    type="button"
                    @click="showChangePasswordModal = false"
                    class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Done
                </button>
            </div>
        </div>
    </div>
</div>
