<div x-data="{
    showSendMessage: false, showLogNote: false, showScheduleActivity: false,
    showChangePasswordModal: false, showCancelModal: false,
    showTwoFactorModal: false, twoFactorCode: '',
    activeTab: 'access'
}"
     x-on:open-change-password-modal.window="activeTab = 'access'; showChangePasswordModal = true"
     x-on:show-two-factor-qr-modal.window="showTwoFactorModal = true">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('settings.users.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        {{ __('settings.users') }}
                    </span>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                        {{ $userId ? $name : __('common.new') . ' ' . __('settings.users') }}
                    </span>
                </div>
            </div>
        </div>
    </x-slot:header>

    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
        @endif

        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">{{ session('error') }}</x-ui.alert>
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

    {{-- Action bar --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
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
                        <span wire:loading.remove wire:target="save">{{ __('common.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('common.loading') }}</span>
                    </button>

                    <button
                        type="button"
                        @click="showCancelModal = true"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        <flux:icon name="x-mark" class="size-4" />
                        {{ __('common.cancel') }}
                    </button>

                    @if($userId)
                        <button
                            type="button"
                            wire:click="delete"
                            wire:confirm="{{ __('common.confirm_delete') }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20"
                        >
                            <flux:icon name="trash" class="size-4" />
                            {{ __('common.delete') }}
                        </button>
                    @endif
                </div>

                <x-ui.status-badge :status="$is_active ? 'active' : 'inactive'" />
            </div>

            <div class="col-span-3">
                <x-ui.chatter-buttons :showMessage="false" />
            </div>
        </div>
    </div>

    <x-ui.confirm-modal show="showCancelModal">
        <x-slot:icon>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                <flux:icon name="exclamation-triangle" class="size-6" />
            </div>
        </x-slot:icon>

        <x-slot:title>Discard changes?</x-slot:title>

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
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">

                    {{-- Profile --}}
                    <div class="border-b border-zinc-100 p-5 dark:border-zinc-800">
                        <div class="flex items-start gap-5">
                            <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                @if($userId && $name)
                                    <span class="text-2xl font-medium text-zinc-400 dark:text-zinc-500">
                                        {{ strtoupper(substr($name, 0, 2)) }}
                                    </span>
                                @else
                                    <flux:icon name="user" class="size-8 text-zinc-300 dark:text-zinc-600" />
                                @endif
                            </div>

                            <div class="grid flex-1 gap-3 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        wire:model="name"
                                        placeholder="e.g. Jane Doe"
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    />
                                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        wire:model="email"
                                        placeholder="user@example.com"
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    />
                                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">Phone</label>
                                    <input
                                        type="tel"
                                        wire:model="phone"
                                        placeholder="+62 ..."
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    />
                                    @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Nav --}}
                    <div class="border-b border-zinc-100 px-5 dark:border-zinc-800">
                        <nav class="-mb-px flex gap-1 text-sm">
                            <button
                                type="button"
                                @click="activeTab = 'access'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2.5 pt-2 font-medium transition-colors"
                                :class="activeTab === 'access'
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100'
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                Access
                            </button>
                            <button
                                type="button"
                                @click="activeTab = 'preferences'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2.5 pt-2 font-medium transition-colors"
                                :class="activeTab === 'preferences'
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100'
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                Preferences
                            </button>
                            <button
                                type="button"
                                @click="activeTab = 'schedule'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2.5 pt-2 font-medium transition-colors"
                                :class="activeTab === 'schedule'
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100'
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                Schedule
                            </button>
                            @if($isSelf)
                                <button
                                    type="button"
                                    @click="activeTab = 'security'"
                                    class="whitespace-nowrap border-b-2 px-3 pb-2.5 pt-2 font-medium transition-colors"
                                    :class="activeTab === 'security'
                                        ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100'
                                        : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                                >
                                    Security
                                </button>
                            @endif
                        </nav>
                    </div>

                    {{-- Access --}}
                    <div x-show="activeTab === 'access'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="p-5">
                        <div class="space-y-6">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                <div class="lg:w-72">
                                    <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Role</h4>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Defines what this user can access.</p>
                                </div>

                                <div class="flex-1 space-y-3">
                                    @if($availableRoles->isEmpty())
                                        <div class="rounded-lg border border-dashed border-zinc-300 px-4 py-3 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                            No roles available. <a href="{{ route('settings.roles.create') }}" wire:navigate class="text-blue-600 hover:underline dark:text-blue-400">Create a role</a> first.
                                        </div>
                                    @else
                                        <select
                                            wire:model.live="selectedRole"
                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        >
                                            <option value="">No role assigned</option>
                                            @foreach($availableRoles as $role)
                                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                                            @endforeach
                                        </select>

                                        @if($selectedRole)
                                            @php
                                                $currentRole = $availableRoles->firstWhere('name', $selectedRole);
                                                $permissionCount = $currentRole?->permissions->count() ?? 0;
                                            @endphp
                                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                                                <div class="flex items-center gap-2">
                                                    <flux:icon name="shield-check" class="size-4 text-zinc-500 dark:text-zinc-400" />
                                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ ucfirst($selectedRole) }}</span>
                                                </div>
                                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ $permissionCount }} permission{{ $permissionCount !== 1 ? 's' : '' }} assigned.
                                                    @if($currentRole)
                                                        <a href="{{ route('settings.roles.edit', $currentRole->id) }}" wire:navigate class="text-blue-600 hover:underline dark:text-blue-400">View role →</a>
                                                    @endif
                                                </p>
                                            </div>
                                        @else
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                Users without a role will have limited access.
                                                <a href="{{ route('settings.roles.index') }}" wire:navigate class="text-blue-600 hover:underline dark:text-blue-400">Manage roles</a>
                                            </p>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                <div class="lg:w-72">
                                    <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Status</h4>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Inactive users cannot log in.</p>
                                </div>

                                <div class="flex-1">
                                    <label class="inline-flex cursor-pointer items-center gap-2.5">
                                        <input
                                            type="checkbox"
                                            wire:model="is_active"
                                            class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700"
                                        />
                                        <span class="text-sm text-zinc-700 dark:text-zinc-300">Active user</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                <div class="lg:w-72">
                                    <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Password</h4>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $userId ? 'Update the password used to sign in.' : "You'll be prompted on save." }}
                                    </p>
                                </div>

                                <div class="flex-1">
                                    <button
                                        type="button"
                                        @click="showChangePasswordModal = true"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                                    >
                                        <flux:icon name="key" class="size-3.5" />
                                        {{ $userId ? 'Change password' : 'Set password' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Preferences --}}
                    <div x-show="activeTab === 'preferences'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="p-5">
                        <div class="space-y-6">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                <div class="lg:w-72">
                                    <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Localization</h4>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Language and timezone used in the interface.</p>
                                </div>

                                <div class="grid flex-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">Language</label>
                                        <select wire:model="language" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            <option value="en">English</option>
                                            <option value="id">Indonesian</option>
                                            <option value="es">Spanish</option>
                                            <option value="fr">French</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">Timezone</label>
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

                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                <div class="lg:w-72">
                                    <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Email Signature</h4>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Appended to outgoing emails.</p>
                                </div>

                                <div class="flex-1">
                                    <textarea
                                        wire:model="signature"
                                        rows="3"
                                        placeholder="Best regards, ..."
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Schedule --}}
                    <div x-show="activeTab === 'schedule'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="p-5">
                        <div class="space-y-6">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                <div class="lg:w-72">
                                    <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Working Hours</h4>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Used by activity scheduling.</p>
                                </div>

                                <div class="flex-1 space-y-3">
                                    <div class="flex items-end gap-2">
                                        <div class="flex-1">
                                            <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">Start</label>
                                            <input type="time" wire:model="working_hours_start" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                        </div>
                                        <span class="pb-2.5 text-zinc-400">—</span>
                                        <div class="flex-1">
                                            <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">End</label>
                                            <input type="time" wire:model="working_hours_end" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-zinc-700 dark:text-zinc-300">Working Days</label>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach(['Mon' => 'mon', 'Tue' => 'tue', 'Wed' => 'wed', 'Thu' => 'thu', 'Fri' => 'fri', 'Sat' => 'sat', 'Sun' => 'sun'] as $label => $value)
                                                <button
                                                    type="button"
                                                    wire:click="toggleWorkingDay('{{ $value }}')"
                                                    class="flex h-9 w-12 items-center justify-center rounded-lg border text-xs font-medium transition-all {{ in_array($value, $working_days) ? 'border-zinc-900 bg-zinc-900 text-white dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900' : 'border-zinc-200 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800' }}"
                                                >
                                                    {{ $label }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                <div class="lg:w-72">
                                    <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Out of Office</h4>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Shown on this user's avatar across the app.</p>
                                </div>

                                <div class="flex-1 space-y-3">
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">Start Date</label>
                                            <input
                                                type="date"
                                                wire:model="out_of_office_start"
                                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            />
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">End Date</label>
                                            <input
                                                type="date"
                                                wire:model="out_of_office_end"
                                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">Auto-reply Message</label>
                                        <textarea
                                            wire:model="out_of_office_message"
                                            rows="2"
                                            placeholder="I'm currently out of office..."
                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        ></textarea>
                                    </div>

                                    @if($out_of_office_start || $out_of_office_end || $out_of_office_message)
                                        <div class="flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2 dark:bg-amber-900/20">
                                            <div class="flex items-center gap-2 text-xs text-amber-700 dark:text-amber-400">
                                                <flux:icon name="calendar-days" class="size-4" />
                                                <span>
                                                    @if($out_of_office_start && $out_of_office_end)
                                                        Out from {{ \Carbon\Carbon::parse($out_of_office_start)->format('M d') }} to {{ \Carbon\Carbon::parse($out_of_office_end)->format('M d, Y') }}
                                                    @elseif($out_of_office_start)
                                                        Out starting {{ \Carbon\Carbon::parse($out_of_office_start)->format('M d, Y') }}
                                                    @else
                                                        Auto-reply set
                                                    @endif
                                                </span>
                                            </div>
                                            <button
                                                type="button"
                                                wire:click="resetOutOfOffice"
                                                class="text-xs font-medium text-amber-700 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300"
                                            >
                                                Reset
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Security (self-only) --}}
                    @if($isSelf)
                        <div x-show="activeTab === 'security'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="p-5">
                            <div class="space-y-6">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                    <div class="lg:w-72">
                                        <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Two-Factor Authentication</h4>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Add a second factor for sign-in.</p>
                                    </div>

                                    <div class="flex-1">
                                        @if($twoFactorEnabled)
                                            <div class="space-y-3">
                                                <div class="flex items-center gap-3 rounded-lg bg-emerald-50 px-3 py-2 dark:bg-emerald-900/20">
                                                    <flux:icon name="shield-check" class="size-4 flex-shrink-0 text-emerald-600 dark:text-emerald-400" />
                                                    <div class="text-xs">
                                                        <p class="font-medium text-emerald-700 dark:text-emerald-400">2FA enabled</p>
                                                        <p class="text-emerald-600/80 dark:text-emerald-500">Your account is protected.</p>
                                                    </div>
                                                </div>

                                                <div class="flex flex-wrap items-center gap-2">
                                                    <button
                                                        type="button"
                                                        wire:click="showRecoveryCodes"
                                                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                                                    >
                                                        <flux:icon name="key" class="size-3.5" />
                                                        Recovery codes
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="disableTwoFactor"
                                                        wire:confirm="Disable two-factor authentication? Your account will be less secure."
                                                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20"
                                                    >
                                                        <flux:icon name="shield-exclamation" class="size-3.5" />
                                                        Disable 2FA
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="enableTwoFactor"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                                            >
                                                <flux:icon name="shield-check" class="size-3.5" />
                                                Enable 2FA
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                    <div class="lg:w-72">
                                        <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Active Sessions</h4>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Devices currently signed in.</p>
                                    </div>

                                    <div class="flex-1 space-y-2">
                                        @forelse($sessions as $session)
                                            <div class="flex items-center gap-3 rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                                                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full {{ $session['is_current'] ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-zinc-100 dark:bg-zinc-800' }}">
                                                    @if($session['is_mobile'])
                                                        <flux:icon name="device-phone-mobile" class="size-4 {{ $session['is_current'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-500 dark:text-zinc-400' }}" />
                                                    @else
                                                        <flux:icon name="computer-desktop" class="size-4 {{ $session['is_current'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-500 dark:text-zinc-400' }}" />
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $session['device'] }}</p>
                                                        @if($session['is_current'])
                                                            <span class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">This device</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $session['browser'] }} · {{ $session['ip'] }} · {{ $session['last_active'] }}</p>
                                                </div>
                                                @if(!$session['is_current'])
                                                    <button
                                                        type="button"
                                                        wire:click="revokeSession('{{ $session['id'] }}')"
                                                        wire:confirm="Log out this device?"
                                                        class="text-xs font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                                    >
                                                        Revoke
                                                    </button>
                                                @endif
                                            </div>
                                        @empty
                                            <p class="rounded-lg border border-dashed border-zinc-200 px-3 py-2 text-xs text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">No active sessions found.</p>
                                        @endforelse

                                        @if(count($sessions) > 1)
                                            <button
                                                type="button"
                                                wire:click="revokeAllSessions"
                                                wire:confirm="Log out all other devices? You'll stay signed in here."
                                                class="inline-flex items-center rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20"
                                            >
                                                Log out all other devices
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($userId && ($createdAt || $updatedAt))
                        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-zinc-100 px-5 py-3 text-xs text-zinc-400 dark:border-zinc-800 dark:text-zinc-500">
                            @if($createdAt)
                                <span>Created {{ $createdAt }}</span>
                            @endif
                            @if($updatedAt)
                                <span>Updated {{ $updatedAt }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-3">
                <x-ui.chatter-forms :showMessage="false" />

                @if($userId)
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

                    <div class="space-y-3">
                        @forelse($activities as $item)
                            @if($item['type'] === 'note')
                                <x-ui.note-item :note="$item['data']" />
                            @else
                                <x-ui.activity-item :activity="$item['data']" emptyMessage="User created" />
                            @endif
                        @empty
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-ui.user-name :user="auth()->user()" />
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ now()->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">User created</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @else
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

    @include('livewire.settings.users._change-password-modal')
    @include('livewire.settings.users._two-factor-modal')
</div>
