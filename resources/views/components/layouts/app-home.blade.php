<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="flex min-h-screen flex-col bg-zinc-50 dark:bg-zinc-900">
        {{-- Full-width Header - h-16 to match module scrolled state visual --}}
        <header class="sticky top-0 z-50 border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950">
            <div class="flex h-16 items-center justify-between px-6">
                <div class="flex items-center gap-3">
                    {{-- Logo --}}
                    <a href="{{ route('home') }}" class="flex items-center gap-2 transition-opacity hover:opacity-70" wire:navigate>
                        <x-app-logo-icon class="h-5 w-5 text-zinc-900 dark:text-zinc-100" />
                        <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Syncore</span>
                    </a>
                </div>

                {{-- Right Side: Profile --}}
                <x-ui.profile-dropdown />
            </div>
        </header>

        {{-- Main Content --}}
        <main class="mx-auto w-full max-w-7xl flex-1 px-6 py-6">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <x-ui.footer />

        @fluxScripts
    </body>
</html>
