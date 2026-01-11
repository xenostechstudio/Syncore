@props([
    'title' => 'General Setup',
])

@php
    $moduleMeta = \App\Navigation\ModuleNavigation::getModuleMeta('Settings');
    $navItems = \App\Navigation\ModuleNavigation::get('Settings');
    
    // Module Configuration items
    $moduleConfigItems = [
        [
            'label' => 'Sales Order',
            'route' => 'settings.modules.sales-order',
            'icon' => 'shopping-cart',
            'pattern' => 'settings.modules.sales-order*',
        ],
        [
            'label' => 'Purchase Order',
            'route' => 'settings.modules.purchase-order',
            'icon' => 'truck',
            'pattern' => 'settings.modules.purchase-order*',
        ],
        [
            'label' => 'Invoice',
            'route' => 'settings.modules.invoice',
            'icon' => 'document-text',
            'pattern' => 'settings.modules.invoice*',
        ],
    ];

    // General Settings items
    $generalSettingsItems = [
        [
            'label' => 'Users',
            'route' => 'settings.users.index',
            'icon' => 'users',
            'pattern' => 'settings.users*',
        ],
        [
            'label' => 'Roles & Permissions',
            'route' => 'settings.roles.index',
            'icon' => 'shield-check',
            'pattern' => 'settings.roles*',
        ],
        [
            'label' => 'Company',
            'route' => 'settings.company.index',
            'icon' => 'building-office',
            'pattern' => 'settings.company*',
        ],
        [
            'label' => 'Localization',
            'route' => 'settings.localization.index',
            'icon' => 'globe-alt',
            'pattern' => 'settings.localization*',
        ],
        [
            'label' => 'Email',
            'route' => 'settings.email.index',
            'icon' => 'envelope',
            'pattern' => 'settings.email*',
        ],
        [
            'label' => 'Audit Trail',
            'route' => 'settings.audit-trail.index',
            'icon' => 'clipboard-document-list',
            'pattern' => 'settings.audit-trail*',
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="flex h-screen flex-col overflow-hidden bg-zinc-50 dark:bg-zinc-900">
        {{-- Fixed Header with Navigation --}}
        <header class="flex-shrink-0 bg-white dark:bg-zinc-950">
            <div class="flex h-14 items-center px-4 lg:px-6">
                {{-- Left Side: Module Icon & Name with Back Navigation --}}
                <a 
                    href="{{ route('home') }}" 
                    wire:navigate
                    class="group flex items-center gap-2.5 pr-6 transition-colors"
                    x-data="{ hovering: false }"
                    @mouseenter="hovering = true"
                    @mouseleave="hovering = false"
                >
                    <div class="relative flex h-8 w-8 items-center justify-center">
                        <flux:icon 
                            :name="$moduleMeta['icon']" 
                            class="absolute size-5 text-zinc-600 transition-all duration-200 dark:text-zinc-400"
                            x-bind:class="hovering ? 'opacity-0 scale-75' : 'opacity-100 scale-100'"
                        />
                        <flux:icon 
                            name="arrow-left" 
                            class="absolute size-5 text-zinc-900 transition-all duration-200 dark:text-zinc-100"
                            x-bind:class="hovering ? 'opacity-100 scale-100' : 'opacity-0 scale-75'"
                        />
                    </div>
                    <span class="text-md font-medium text-zinc-900 whitespace-nowrap transition-colors group-hover:text-zinc-600 dark:text-zinc-100 dark:group-hover:text-zinc-300">{{ $moduleMeta['name'] }}</span>
                </a>

                {{-- Middle: Top Navigation (Overview only) --}}
                <nav class="relative hidden h-full items-center md:flex">
                    <div class="relative flex h-full items-center gap-1">
                        @php
                            $isOverviewActive = request()->routeIs('settings.index');
                        @endphp
                        <a 
                            href="{{ route('settings.index') }}" 
                            wire:navigate
                            class="inline-flex h-full items-center gap-1.5 border-b-2 px-3 text-sm font-normal transition-colors whitespace-nowrap {{ $isOverviewActive ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }}"
                        >
                            <flux:icon name="chart-bar-square" class="size-4" />
                            <span>Overview</span>
                        </a>
                    </div>
                </nav>

                {{-- Spacer --}}
                <div class="flex-1"></div>

                {{-- Right Side: Company Name, Notification, Profile --}}
                <div class="flex items-center gap-4 pl-4">
                    {{-- Global Search Button --}}
                    <button 
                        type="button"
                        x-data
                        @click="$dispatch('openGlobalSearch')"
                        class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-500 transition-colors hover:border-zinc-300 hover:text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-300"
                    >
                        <flux:icon name="magnifying-glass" class="size-4" />
                        <span class="hidden sm:inline">Search</span>
                        <kbd class="hidden rounded bg-zinc-100 px-1.5 py-0.5 text-xs font-medium text-zinc-400 sm:inline-block dark:bg-zinc-700 dark:text-zinc-500">⌘K</kbd>
                    </button>

                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ \App\Models\Settings\CompanyProfile::getCompanyName() }}
                    </span>

                    <livewire:components.notification-dropdown />

                    <x-ui.profile-dropdown />
                </div>
            </div>

        </header>

        {{-- Header Bar (below top nav, full width) --}}
        @if(isset($header))
        <div class="flex min-h-[60px] flex-shrink-0 items-center justify-between border-b border-zinc-200 bg-white px-4 py-3 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
            {{ $header }}
        </div>
        @endif

        {{-- Main Content with Sidebar --}}
        <div class="flex flex-1 overflow-hidden">
            {{-- Sidebar --}}
            <aside class="hidden w-56 flex-shrink-0 overflow-y-auto border-r border-zinc-200 bg-white lg:block dark:border-zinc-800 dark:bg-zinc-950">
                <div class="p-4">
                    <nav class="flex flex-col gap-1">
                        {{-- Module Configuration --}}
                        @foreach($moduleConfigItems as $item)
                            @php
                                $patterns = explode('|', $item['pattern']);
                                $isActive = collect($patterns)->contains(fn($p) => request()->routeIs(trim($p)));
                            @endphp
                            <a 
                                href="{{ route($item['route']) }}" 
                                wire:navigate
                                @class([
                                    'flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors',
                                    'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' => $isActive,
                                    'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100' => !$isActive,
                                ])
                            >
                                <flux:icon :name="$item['icon']" class="size-4" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach

                        {{-- Separator --}}
                        <div class="my-2 border-t border-zinc-200 dark:border-zinc-800"></div>

                        {{-- General Settings --}}
                        @foreach($generalSettingsItems as $item)
                            @php
                                $patterns = explode('|', $item['pattern']);
                                $isActive = collect($patterns)->contains(fn($p) => request()->routeIs(trim($p)));
                            @endphp
                            <a 
                                href="{{ route($item['route']) }}" 
                                wire:navigate
                                @class([
                                    'flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors',
                                    'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' => $isActive,
                                    'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100' => !$isActive,
                                ])
                            >
                                <flux:icon :name="$item['icon']" class="size-4" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            </aside>

            {{-- Mobile Sidebar --}}
            <div class="lg:hidden" x-data="{ open: false }">
                <button 
                    @click="open = !open" 
                    class="fixed bottom-4 left-4 z-40 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-900 text-white shadow-lg dark:bg-zinc-100 dark:text-zinc-900"
                >
                    <flux:icon name="bars-3" class="size-6" />
                </button>
                
                {{-- Overlay --}}
                <div 
                    x-show="open" 
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="open = false"
                    class="fixed inset-0 z-40 bg-zinc-900/50"
                    x-cloak
                ></div>
                
                {{-- Mobile Sidebar Panel --}}
                <div 
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="-translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="-translate-x-full"
                    class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-zinc-950"
                    x-cloak
                >
                    <div class="flex h-14 items-center justify-between border-b border-zinc-200 px-4 dark:border-zinc-800">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Menu</span>
                        <button @click="open = false" class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                            <flux:icon name="x-mark" class="size-5" />
                        </button>
                    </div>
                    <nav class="flex flex-col gap-1 p-4">
                        {{-- Module Configuration --}}
                        @foreach($moduleConfigItems as $item)
                            @php
                                $patterns = explode('|', $item['pattern']);
                                $isActive = collect($patterns)->contains(fn($p) => request()->routeIs(trim($p)));
                            @endphp
                            <a 
                                href="{{ route($item['route']) }}" 
                                wire:navigate
                                @click="open = false"
                                @class([
                                    'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-colors',
                                    'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' => $isActive,
                                    'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100' => !$isActive,
                                ])
                            >
                                <flux:icon :name="$item['icon']" class="size-5" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach

                        {{-- Separator --}}
                        <div class="my-2 border-t border-zinc-200 dark:border-zinc-800"></div>

                        {{-- General Settings --}}
                        @foreach($generalSettingsItems as $item)
                            @php
                                $patterns = explode('|', $item['pattern']);
                                $isActive = collect($patterns)->contains(fn($p) => request()->routeIs(trim($p)));
                            @endphp
                            <a 
                                href="{{ route($item['route']) }}" 
                                wire:navigate
                                @click="open = false"
                                @class([
                                    'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-colors',
                                    'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' => $isActive,
                                    'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100' => !$isActive,
                                ])
                            >
                                <flux:icon :name="$item['icon']" class="size-5" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>

            {{-- Content Area --}}
            <main class="flex-1 overflow-y-auto bg-white dark:bg-zinc-950">
                {{-- Page Content --}}
                <div class="px-4 py-6 sm:px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </main>
        </div>

        {{-- Global Search --}}
        <livewire:components.global-search />

        @fluxScripts
    </body>
</html>
