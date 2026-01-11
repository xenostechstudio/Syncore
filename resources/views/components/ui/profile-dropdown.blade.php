{{-- Enhanced Profile Dropdown --}}
@php
    $user = auth()->user();
    $isOutOfOffice = $user->isOutOfOffice();
    // Cache user role to avoid repeated queries
    $userRole = cache()->remember("user_role_{$user->id}", 300, fn() => $user->roles->first()?->name);
@endphp

<flux:dropdown position="bottom" align="end">
    {{-- Trigger: Avatar --}}
    <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-zinc-600 to-zinc-800 text-xs font-semibold text-white ring-2 ring-transparent transition-all hover:ring-zinc-300 focus:outline-none focus:ring-zinc-400 dark:from-zinc-500 dark:to-zinc-700 dark:hover:ring-zinc-600 dark:focus:ring-zinc-500">
        {{ $user->initials() }}
    </button>

    <flux:menu class="w-80">
        {{-- User Profile Header --}}
        <div class="p-4">
            <div class="flex items-start gap-3">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-zinc-600 to-zinc-800 text-sm font-semibold text-white dark:from-zinc-500 dark:to-zinc-700">
                    {{ $user->initials() }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $user->name }}</p>
                    <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                    @if($userRole)
                        <span class="mt-1.5 inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                            {{ $userRole }}
                        </span>
                    @endif
                </div>
                {{-- Status indicator --}}
                <div class="flex-shrink-0">
                    @if($isOutOfOffice)
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            Away
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Online
                        </span>
                    @endif
                </div>
            </div>

            {{-- Out of Office Banner --}}
            @if($isOutOfOffice)
                <div class="mt-3 rounded-lg bg-amber-50 p-2.5 dark:bg-amber-900/20">
                    <div class="flex items-center gap-2">
                        <flux:icon name="calendar-days" class="size-4 text-amber-600 dark:text-amber-400" />
                        <span class="text-xs font-medium text-amber-700 dark:text-amber-300">Out of Office</span>
                    </div>
                    @if($user->out_of_office_message)
                        <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">{{ Str::limit($user->out_of_office_message, 60) }}</p>
                    @endif
                </div>
            @endif
        </div>

        <flux:menu.separator />

        {{-- Language Toggle --}}
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <flux:icon name="language" class="size-4 text-zinc-400" />
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Language</span>
                </div>
                <form id="locale-form-en" method="POST" action="{{ route('locale.switch') }}" class="hidden">
                    @csrf <input type="hidden" name="locale" value="en">
                </form>
                <form id="locale-form-id" method="POST" action="{{ route('locale.switch') }}" class="hidden">
                    @csrf <input type="hidden" name="locale" value="id">
                </form>
                <div class="flex items-center gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
                    <button 
                        type="button"
                        onclick="document.getElementById('locale-form-en').submit()"
                        class="{{ app()->getLocale() === 'en' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300' }} rounded-md px-2.5 py-1 text-xs font-medium transition-all"
                    >
                        EN
                    </button>
                    <button 
                        type="button"
                        onclick="document.getElementById('locale-form-id').submit()"
                        class="{{ app()->getLocale() === 'id' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300' }} rounded-md px-2.5 py-1 text-xs font-medium transition-all"
                    >
                        ID
                    </button>
                </div>
            </div>
        </div>

        <flux:menu.separator />

        {{-- Menu Links --}}
        <div class="px-2 py-2">
            <a href="{{ route('settings.users.edit', $user->id) }}" wire:navigate class="flex items-center gap-3 rounded-lg px-2 py-2 text-sm text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800">
                <flux:icon name="user-circle" class="size-4" />
                <span>My Profile</span>
            </a>
        </div>

        <flux:menu.separator />

        {{-- Logout --}}
        <div class="px-2 py-2">
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-2 py-2 text-sm text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                    <flux:icon name="arrow-right-start-on-rectangle" class="size-4" />
                    <span>Sign out</span>
                </button>
            </form>
        </div>
    </flux:menu>
</flux:dropdown>
