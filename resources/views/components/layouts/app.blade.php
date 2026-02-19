@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="flex min-h-screen flex-col bg-zinc-50 dark:bg-zinc-900">
        {{-- Minimal Header --}}
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
                    {{-- Global Search Button --}}
                    <button 
                        type="button"
                        x-data
                        @click="$dispatch('openGlobalSearch')"
                        class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-500 transition-colors hover:border-zinc-300 hover:text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-300"
                    >
                        <flux:icon name="magnifying-glass" class="size-4" />
                        <span class="hidden sm:inline">{{ __('common.search') }}</span>
                        <kbd class="hidden rounded bg-zinc-100 px-1.5 py-0.5 text-xs font-medium text-zinc-400 sm:inline-block dark:bg-zinc-700 dark:text-zinc-500">⌘K</kbd>
                    </button>

                    {{-- Language Switcher --}}
                    <livewire:components.language-switcher />

                    {{-- Company Name --}}
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ \App\Models\Settings\CompanyProfile::getCompanyName() }}
                    </span>

                    {{-- Notification Icon --}}
                    <livewire:components.notification-dropdown />

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

        {{-- Global Search --}}
        <livewire:components.global-search />

        @fluxScripts
    </body>
</html>
