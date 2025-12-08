{{-- Vercel-style Profile Dropdown --}}
<flux:dropdown position="bottom" align="end">
    {{-- Trigger: Rounded Avatar --}}
    <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-zinc-600 to-zinc-800 text-xs font-medium text-white transition-opacity hover:opacity-80 focus:outline-none dark:from-zinc-500 dark:to-zinc-700">
        {{ auth()->user()->initials() }}
    </button>

    <flux:menu class="w-72">
        {{-- User Info Header --}}
        <div class="flex items-center justify-between px-3 py-2.5">
            <div>
                <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name }}</p>
                <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</p>
            </div>
        </div>

        <flux:menu.separator />

        {{-- Theme Row --}}
        <div class="flex items-center justify-between px-3 py-2" x-data>
            <span class="text-sm font-light text-zinc-600 dark:text-zinc-400">Theme</span>
            <div class="flex items-center gap-0.5 rounded-full border border-zinc-200 p-0.5 dark:border-zinc-700">
                <button 
                    type="button"
                    @click="$flux.appearance = 'system'"
                    :class="$flux.appearance === 'system' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300'"
                    class="rounded-full p-1.5 transition-colors"
                    title="System"
                >
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                    </svg>
                </button>
                <button 
                    type="button"
                    @click="$flux.appearance = 'light'"
                    :class="$flux.appearance === 'light' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300'"
                    class="rounded-full p-1.5 transition-colors"
                    title="Light"
                >
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                    </svg>
                </button>
                <button 
                    type="button"
                    @click="$flux.appearance = 'dark'"
                    :class="$flux.appearance === 'dark' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300'"
                    class="rounded-full p-1.5 transition-colors"
                    title="Dark"
                >
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Language Row --}}
        <div class="flex items-center justify-between px-3 py-2">
            <span class="text-sm font-light text-zinc-600 dark:text-zinc-400">Language</span>
            <form id="locale-form-en" method="POST" action="{{ route('locale.switch') }}" class="hidden">
                @csrf <input type="hidden" name="locale" value="en">
            </form>
            <form id="locale-form-id" method="POST" action="{{ route('locale.switch') }}" class="hidden">
                @csrf <input type="hidden" name="locale" value="id">
            </form>
            <div class="flex items-center gap-0.5 rounded-full border border-zinc-200 p-0.5 dark:border-zinc-700">
                <button 
                    type="button"
                    onclick="document.getElementById('locale-form-en').submit()"
                    class="{{ app()->getLocale() === 'en' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300' }} rounded-full px-2.5 py-1 text-xs font-light transition-colors"
                >
                    EN
                </button>
                <button 
                    type="button"
                    onclick="document.getElementById('locale-form-id').submit()"
                    class="{{ app()->getLocale() === 'id' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300' }} rounded-full px-2.5 py-1 text-xs font-light transition-colors"
                >
                    ID
                </button>
            </div>
        </div>

        <flux:menu.separator />

        {{-- Menu Items with icons on right --}}
        <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center justify-between px-3 py-2 text-sm font-light text-zinc-600 transition-colors hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100">
            <span>Settings</span>
            <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </a>

        <flux:menu.separator />

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="flex w-full items-center justify-between px-3 py-2 text-sm font-light text-zinc-600 transition-colors hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100">
                <span>Log out</span>
                <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                </svg>
            </button>
        </form>
    </flux:menu>
</flux:dropdown>
