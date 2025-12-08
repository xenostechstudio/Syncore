<div class="space-y-6">
    {{-- Two Column Layout --}}
    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Left Column: Stats --}}
        <div class="space-y-6 lg:col-span-4">
            {{-- System Stats Card --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">System Overview</h2>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Total Users</span>
                        <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ number_format($totalUsers) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Active Users</span>
                        <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ number_format($activeUsers) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Roles</span>
                        <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ number_format($totalRoles) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Timezone</span>
                        <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ config('app.timezone') }}</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions Card --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Quick Actions</h2>
                </div>
                <div class="p-3">
                    <div class="space-y-1">
                        <a href="{{ route('settings.users.index') }}" wire:navigate class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-light text-zinc-600 transition-colors hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            Manage Users
                        </a>
                        <a href="{{ route('settings.roles.index') }}" wire:navigate class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-light text-zinc-600 transition-colors hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                            Roles & Permissions
                        </a>
                        <a href="{{ route('settings.localization.index') }}" wire:navigate class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-light text-zinc-600 transition-colors hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                            </svg>
                            Localization Settings
                        </a>
                        <a href="{{ route('settings.company.index') }}" wire:navigate class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-light text-zinc-600 transition-colors hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                            </svg>
                            Company Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Setup Cards --}}
        <div class="space-y-6 lg:col-span-8">
            {{-- Setup Cards Grid --}}
            <div class="grid gap-4 sm:grid-cols-2">
                {{-- Users Card --}}
                <a href="{{ route('settings.users.index') }}" wire:navigate class="group rounded-lg border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Users</h3>
                            <p class="mt-1 text-xs font-light text-zinc-500 dark:text-zinc-400">Manage user accounts, invitations, and access</p>
                            <p class="mt-2 text-xs text-zinc-400 dark:text-zinc-500">{{ $totalUsers }} users</p>
                        </div>
                        <svg class="size-4 text-zinc-300 transition-colors group-hover:text-zinc-500 dark:text-zinc-600 dark:group-hover:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>
                </a>

                {{-- Roles & Permissions Card --}}
                <a href="{{ route('settings.roles.index') }}" wire:navigate class="group rounded-lg border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Roles & Permissions</h3>
                            <p class="mt-1 text-xs font-light text-zinc-500 dark:text-zinc-400">Configure roles and access permissions</p>
                            <p class="mt-2 text-xs text-zinc-400 dark:text-zinc-500">{{ $totalRoles }} roles</p>
                        </div>
                        <svg class="size-4 text-zinc-300 transition-colors group-hover:text-zinc-500 dark:text-zinc-600 dark:group-hover:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>
                </a>

                {{-- Localization Card --}}
                <a href="{{ route('settings.localization.index') }}" wire:navigate class="group rounded-lg border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-900/20 dark:text-violet-400">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Localization</h3>
                            <p class="mt-1 text-xs font-light text-zinc-500 dark:text-zinc-400">Timezone, currency, date formats, and language</p>
                            <p class="mt-2 text-xs text-zinc-400 dark:text-zinc-500">{{ config('app.timezone') }}</p>
                        </div>
                        <svg class="size-4 text-zinc-300 transition-colors group-hover:text-zinc-500 dark:text-zinc-600 dark:group-hover:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>
                </a>

                {{-- Company Card --}}
                <a href="{{ route('settings.company.index') }}" wire:navigate class="group rounded-lg border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Company</h3>
                            <p class="mt-1 text-xs font-light text-zinc-500 dark:text-zinc-400">Company profile, logo, and business details</p>
                            <p class="mt-2 text-xs text-zinc-400 dark:text-zinc-500">Configure</p>
                        </div>
                        <svg class="size-4 text-zinc-300 transition-colors group-hover:text-zinc-500 dark:text-zinc-600 dark:group-hover:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>
                </a>
            </div>

            {{-- Recent Users --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Recent Users</h2>
                    <a href="{{ route('settings.users.index') }}" wire:navigate class="text-xs font-light text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300">
                        View all →
                    </a>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($recentUsers as $user)
                        <div class="flex items-center gap-4 px-5 py-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-sm font-normal text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="truncate text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $user->name }}</p>
                                <p class="truncate text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-light text-zinc-400 dark:text-zinc-500">{{ $user->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-zinc-400">
                            No users found
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
