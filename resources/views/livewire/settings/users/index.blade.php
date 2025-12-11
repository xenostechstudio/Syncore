<div>
    {{-- Header --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="primary" icon="plus" href="{{ route('settings.users.create') }}" wire:navigate>
                    Add User
                </flux:button>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">
                    Users
                </span>
                <flux:dropdown position="bottom" align="start">
                    <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="cog-6-tooth" class="size-5" />
                    </button>

                    <flux:menu class="w-48">
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>Import users</span>
                        </button>
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>Export all</span>
                        </button>
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Toolbar --}}
            <div class="flex flex-1 items-center justify-center">
                <div class="relative flex h-9 w-full max-w-lg items-center overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search users..." 
                        class="h-full w-full border-0 bg-transparent pl-9 pr-3 text-sm outline-none focus:ring-0" 
                    />
                </div>
            </div>

            <div class="flex items-center gap-3">
                {{-- Pagination --}}
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }}/{{ $users->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button 
                            type="button"
                            wire:click="goToPreviousPage"
                            @disabled($users->onFirstPage())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button 
                            type="button"
                            wire:click="goToNextPage"
                            @disabled(!$users->hasMorePages())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                {{-- View Toggle --}}
                <x-ui.view-toggle :view="$view" />
            </div>
        </div>
    </div>

    {{-- Users List/Grid --}}
    <div>
        @if($view === 'list')
            <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">User</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Joined</th>
                            <th scope="col" class="w-10 py-3 pr-4 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pr-6 lg:pr-8 dark:text-zinc-400"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($users as $user)
                            <tr 
                                onclick="window.location.href='{{ route('settings.users.edit', $user->id) }}'"
                                class="cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            >
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ $user->initials() }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</span>
                                            <span class="max-w-xs truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    @if($user->email_verified_at)
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $user->created_at->format('M d, Y') }}</span>
                                </td>
                                <td class="py-4 pr-4 text-right sm:pr-6 lg:pr-8" onclick="event.stopPropagation()">
                                    <a href="{{ route('settings.users.edit', $user->id) }}" wire:navigate class="inline-flex rounded-md p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-300">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                            <svg class="size-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No users found</p>
                                            <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Try adjusting your search or filters</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            {{-- Grid View --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse($users as $user)
                    <div class="rounded-lg border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                        <div class="flex flex-col items-center text-center">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-zinc-100 text-lg font-normal text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $user->initials() }}
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
    </div>

    {{-- Pagination --}}
    <div class="hidden"></div>
</div>
