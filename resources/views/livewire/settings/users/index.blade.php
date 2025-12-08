<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-normal text-zinc-900 dark:text-zinc-100">Users</h1>
        <flux:button variant="primary" icon="plus" href="{{ route('settings.users.create') }}" wire:navigate>
            Add User
        </flux:button>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col gap-4 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search users..."
                    class="w-64 rounded-lg border border-zinc-200 bg-white py-2 pl-10 pr-4 text-sm font-light text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:focus:border-zinc-600"
                />
            </div>

            {{-- Status Filter --}}
            <select 
                wire:model.live="status"
                class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-light text-zinc-600 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
            >
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="pending">Pending</option>
            </select>

            {{-- Sort --}}
            <select 
                wire:model.live="sort"
                class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-light text-zinc-600 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
            >
                <option value="latest">Latest</option>
                <option value="oldest">Oldest</option>
                <option value="name">Name A-Z</option>
            </select>
        </div>

        {{-- View Toggle --}}
        <x-ui.view-toggle :view="$view" />
    </div>

    {{-- Users List/Grid --}}
    @if($view === 'list')
        <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($users as $user)
                    <div class="flex items-center gap-4 px-5 py-4 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 text-sm font-normal text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="truncate text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $user->name }}</p>
                            <p class="truncate text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                        </div>
                        <div class="hidden sm:block">
                            @if($user->email_verified_at)
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-light text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-light text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">
                                    Pending
                                </span>
                            @endif
                        </div>
                        <div class="hidden text-right sm:block">
                            <p class="text-xs font-light text-zinc-400 dark:text-zinc-500">Joined {{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('settings.users.edit', $user->id) }}" wire:navigate class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-300">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                </svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center">
                        <svg class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                        <p class="mt-4 text-sm font-light text-zinc-500 dark:text-zinc-400">No users found</p>
                    </div>
                @endforelse
            </div>
        </div>
    @else
        {{-- Grid View --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($users as $user)
                <div class="rounded-lg border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-zinc-100 text-lg font-normal text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <h3 class="mt-3 truncate text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ $user->name }}</h3>
                        <p class="mt-0.5 truncate text-xs font-light text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                        <div class="mt-3">
                            @if($user->email_verified_at)
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-light text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-light text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">
                                    Pending
                                </span>
                            @endif
                        </div>
                        <p class="mt-2 text-xs font-light text-zinc-400 dark:text-zinc-500">Joined {{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-lg border border-zinc-200 bg-white px-5 py-12 text-center dark:border-zinc-800 dark:bg-zinc-900">
                    <svg class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <p class="mt-4 text-sm font-light text-zinc-500 dark:text-zinc-400">No users found</p>
                </div>
            @endforelse
        </div>
    @endif

    {{-- Pagination --}}
    @if($users->hasPages())
        <div class="flex justify-center">
            {{ $users->links() }}
        </div>
    @endif
</div>
