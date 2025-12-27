<div>
    <x-slot:header>
        <div class="flex items-center gap-3">
            <a
                href="{{ route('settings.users.create') }}"
                wire:navigate
                class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                Invite User
            </a>
            <h1 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Overview</h1>
            
            {{-- Actions Menu --}}
            <flux:dropdown position="bottom" align="start">
                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="cog-6-tooth" class="size-5" />
                </button>

                <flux:menu class="w-48">
                    <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                        <flux:icon name="arrow-down-tray" class="size-4" />
                        <span>Import users</span>
                    </button>
                    <a href="{{ route('export.users') }}" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                        <flux:icon name="arrow-up-tray" class="size-4" />
                        <span>Export all</span>
                    </a>
                </flux:menu>
            </flux:dropdown>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-xs text-zinc-400 dark:text-zinc-500">
                {{ $totalUsers }} users · {{ $totalRoles }} roles
            </span>
        </div>
    </x-slot:header>

    {{-- Two Column Layout --}}
    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Left Column: Stats & Quick Info --}}
        <div class="space-y-6 lg:col-span-4">
            {{-- Statistics Card --}}
            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Statistics</h3>
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-4 py-3 dark:border-zinc-800">
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">System Overview</span>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Total Users</span>
                        <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ number_format($totalUsers) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Active Users</span>
                        <span class="text-sm font-normal text-emerald-600 dark:text-emerald-400">{{ number_format($activeUsers) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Pending Verification</span>
                        <span class="text-sm font-normal text-amber-600 dark:text-amber-400">{{ number_format($totalUsers - $activeUsers) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Roles</span>
                        <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ number_format($totalRoles) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Timezone</span>
                        <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ config('app.timezone') }}</span>
                    </div>
                </div>
            </div>

            {{-- Recent Users Card --}}
            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Recent Users</h3>
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between px-4 py-3">
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Last 7 Days</span>
                    <a href="{{ route('settings.users.create') }}" wire:navigate class="rounded-md bg-zinc-900 px-2 py-1 text-xs font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        Add User
                    </a>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($recentUsers->take(5) as $user)
                        <a href="{{ route('settings.users.edit', $user->id) }}" wire:navigate class="flex items-center justify-between px-4 py-2 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <span class="text-sm font-light text-zinc-600 dark:text-zinc-300">{{ $user->name }}</span>
                            </div>
                            <span class="text-xs font-light text-zinc-400 dark:text-zinc-500">{{ $user->created_at->diffForHumans(null, true) }}</span>
                        </a>
                    @empty
                        <div class="px-5 py-6 text-center text-sm font-light text-zinc-400">No users yet</div>
                    @endforelse
                </div>
                <div class="border-t border-zinc-100 px-5 py-3 dark:border-zinc-800">
                    <a href="{{ route('settings.users.index') }}" wire:navigate class="text-xs font-light text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                        View all users →
                    </a>
                </div>
            </div>
        </div>

        {{-- Right Column: Quick Access --}}
        <div class="space-y-4 lg:col-span-8">
            {{-- Module Configuration --}}
            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Module Configuration</h3>
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden">
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <a href="{{ route('settings.modules.sales-order') }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                <flux:icon name="shopping-cart" class="size-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Sales Order</p>
                                <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Order settings, quotations, pricing</p>
                            </div>
                        </div>
                        <flux:icon name="chevron-right" class="size-4 text-zinc-300 dark:text-zinc-600" />
                    </a>
                    <a href="{{ route('settings.modules.purchase-order') }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/20">
                                <flux:icon name="truck" class="size-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Purchase Order</p>
                                <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Vendor management, approval workflow</p>
                            </div>
                        </div>
                        <flux:icon name="chevron-right" class="size-4 text-zinc-300 dark:text-zinc-600" />
                    </a>
                    <a href="{{ route('settings.modules.invoice') }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                                <flux:icon name="document-text" class="size-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Invoice</p>
                                <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Payment gateway, tax configuration</p>
                            </div>
                        </div>
                        <flux:icon name="chevron-right" class="size-4 text-zinc-300 dark:text-zinc-600" />
                    </a>
                </div>
            </div>

            {{-- General Settings --}}
            <h3 class="mt-6 text-sm font-medium text-zinc-900 dark:text-zinc-100">General Settings</h3>
            <div class="grid gap-3 sm:grid-cols-2">
                <a href="{{ route('settings.users.index') }}" wire:navigate class="flex items-center gap-4 rounded-lg border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="users" class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Users</p>
                        <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $totalUsers }} users</p>
                    </div>
                </a>
                <a href="{{ route('settings.roles.index') }}" wire:navigate class="flex items-center gap-4 rounded-lg border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="shield-check" class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Roles & Permissions</p>
                        <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $totalRoles }} roles</p>
                    </div>
                </a>
                <a href="{{ route('settings.company.index') }}" wire:navigate class="flex items-center gap-4 rounded-lg border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="building-office" class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Company</p>
                        <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Business profile</p>
                    </div>
                </a>
                <a href="{{ route('settings.localization.index') }}" wire:navigate class="flex items-center gap-4 rounded-lg border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="globe-alt" class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Localization</p>
                        <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Language & region</p>
                    </div>
                </a>
                <a href="{{ route('settings.email.index') }}" wire:navigate class="flex items-center gap-4 rounded-lg border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="envelope" class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Email</p>
                        <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">SMTP configuration</p>
                    </div>
                </a>
                <a href="{{ route('settings.audit-trail.index') }}" wire:navigate class="flex items-center gap-4 rounded-lg border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="clipboard-document-list" class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Audit Trail</p>
                        <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Activity logs</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
