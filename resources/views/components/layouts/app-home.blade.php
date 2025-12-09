<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="flex min-h-screen flex-col bg-zinc-50 dark:bg-zinc-900">
        {{-- Minimal Header - No background --}}
        <header class="sticky top-0 z-50">
            <div class="flex h-16 items-center justify-between px-6">
                {{-- Left: Logo only --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 transition-opacity hover:opacity-70" wire:navigate>
                        <x-app-logo-icon class="h-6 w-6 text-zinc-900 dark:text-zinc-100" />
                    </a>
                </div>

                {{-- Right Side: Company Name, Notification, Profile --}}
                <div class="flex items-center gap-4">
                    {{-- Company Name --}}
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ config('app.company_name', 'Syncore') }}
                    </span>

                    {{-- Notification Icon --}}
                    <button type="button" class="relative rounded-full p-2 text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200">
                        <flux:icon name="bell" variant="solid" class="size-5" />
                        {{-- Notification Badge --}}
                        <span class="absolute right-1.5 top-1.5 flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-red-500"></span>
                        </span>
                    </button>

                    {{-- Profile --}}
                    <x-ui.profile-dropdown />
                </div>
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
