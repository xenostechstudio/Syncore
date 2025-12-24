{{-- Vercel-style Footer - Compact --}}
<footer class="border-t border-zinc-200/50 bg-white/60 backdrop-blur-sm dark:border-zinc-600 dark:bg-zinc-900/60">
    <div class="flex h-12 items-center justify-between px-6">
        {{-- Left: Logo & Copyright --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="text-zinc-400 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100" wire:navigate>
                <x-app-logo-icon class="h-4 w-4" />
            </a>
            <span class="text-xs text-zinc-500 dark:text-zinc-400">&copy; {{ date('Y') }} Syncore</span>
        </div>

        {{-- Right: Theme Toggle --}}
        <div class="flex items-center gap-0.5 rounded-full border border-zinc-200/80 bg-white/50 p-0.5 dark:border-zinc-600/50 dark:bg-zinc-800/50" x-data>
            <button 
                type="button"
                @click="$flux.appearance = 'light'"
                :class="$flux.appearance === 'light' ? 'bg-zinc-200 text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-400 hover:text-zinc-600 dark:text-zinc-400 dark:hover:text-zinc-200'"
                class="rounded-full p-1 transition-colors"
                title="Light"
            >
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </button>
            <button 
                type="button"
                @click="$flux.appearance = 'system'"
                :class="$flux.appearance === 'system' ? 'bg-zinc-200 text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-400 hover:text-zinc-600 dark:text-zinc-400 dark:hover:text-zinc-200'"
                class="rounded-full p-1 transition-colors"
                title="System"
            >
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </button>
            <button 
                type="button"
                @click="$flux.appearance = 'dark'"
                :class="$flux.appearance === 'dark' ? 'bg-zinc-200 text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-400 hover:text-zinc-600 dark:text-zinc-400 dark:hover:text-zinc-200'"
                class="rounded-full p-1 transition-colors"
                title="Dark"
            >
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>
        </div>
    </div>
</footer>
