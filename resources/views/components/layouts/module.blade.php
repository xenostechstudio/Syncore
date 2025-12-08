@props([
    'module' => null,
    'moduleName' => null,
])

@php
    $moduleKey = $module ?? 'Inventory';
    $moduleMeta = \App\Navigation\ModuleNavigation::getModuleMeta($moduleKey);
    $navItems = \App\Navigation\ModuleNavigation::get($moduleKey);
    $displayName = $moduleName ?? $moduleMeta['name'] ?? $moduleKey;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="flex min-h-screen flex-col bg-zinc-50 dark:bg-zinc-900">
        {{-- Vercel-style Header with scroll behavior --}}
        <header 
            x-data="{ scrolled: false }"
            x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 50 })"
            :class="scrolled ? 'h-14' : 'h-[104px]'"
            class="sticky top-0 z-50 border-b border-zinc-200 bg-white transition-all duration-200 dark:border-zinc-800 dark:bg-zinc-950"
        >
            <div class="flex h-full flex-col px-6">
                {{-- Top Row: Logo, Module Name, Profile (hidden when scrolled) --}}
                <div 
                    :class="scrolled ? 'opacity-0 h-0 overflow-hidden' : 'opacity-100 h-14'"
                    class="flex items-center justify-between transition-all duration-200"
                >
                    <div class="flex items-center gap-3">
                        {{-- Logo & Home --}}
                        <a href="{{ route('home') }}" class="flex items-center transition-opacity hover:opacity-70" wire:navigate>
                            <x-app-logo-icon class="h-6 w-6 text-zinc-900 dark:text-zinc-100" />
                        </a>

                        {{-- Separator --}}
                        <span class="text-xl text-zinc-300 dark:text-zinc-700">/</span>

                        {{-- Module Icon & Name --}}
                        <div class="flex items-center gap-2.5">
                            <flux:icon :name="$moduleMeta['icon']" class="size-5 text-zinc-600 dark:text-zinc-400" />
                            <span class="text-base font-normal text-zinc-900 dark:text-zinc-100">{{ $displayName }}</span>
                        </div>
                    </div>

                    {{-- Right Side: Profile --}}
                    <x-ui.profile-dropdown />
                </div>

                {{-- Bottom Row: Navigation (becomes main row when scrolled) --}}
                <nav 
                    x-data="{ 
                        indicatorLeft: 0,
                        indicatorWidth: 0,
                        indicatorVisible: false,
                        activeItem: null,
                        init() {
                            this.setActiveItem();
                            
                            // Initial update
                            this.$nextTick(() => {
                                this.updateIndicator();
                                this.indicatorVisible = true;
                            });

                            // Listen for Livewire navigation
                            document.addEventListener('livewire:navigated', () => {
                                this.setActiveItem();
                                this.$nextTick(() => {
                                    this.updateIndicator();
                                });
                            });
                        },
                        setActiveItem() {
                            this.activeItem = this.$refs.navContainer?.querySelector('[data-active=\'true\']');
                        },
                        updateIndicator() {
                            if (this.activeItem) {
                                this.indicatorLeft = this.activeItem.offsetLeft;
                                this.indicatorWidth = this.activeItem.offsetWidth;
                            }
                        },
                        handleClick(el) {
                            this.activeItem = el;
                            this.updateIndicator();
                        }
                    }"
                    class="relative -mb-px flex h-14 flex-1 items-center"
                >
                    {{-- Logo (only visible when scrolled) --}}
                    <a 
                        href="{{ route('home') }}" 
                        wire:navigate
                        :class="scrolled ? 'opacity-100 w-auto mr-6' : 'opacity-0 w-0 mr-0 overflow-hidden'"
                        class="flex h-full items-center transition-all duration-200"
                    >
                        <x-app-logo-icon class="h-6 w-6 text-zinc-900 dark:text-zinc-100" />
                    </a>

                    {{-- Navigation Items Container --}}
                    <div x-ref="navContainer" class="relative flex h-full items-center gap-1">
                        {{-- Animated Underline Indicator --}}
                        <div 
                            class="absolute bottom-0 h-0.5 bg-zinc-900 transition-all duration-300 ease-out dark:bg-zinc-100"
                            :class="indicatorVisible ? 'opacity-100' : 'opacity-0'"
                            :style="`left: ${indicatorLeft}px; width: ${indicatorWidth}px;`"
                        ></div>

                        @foreach($navItems as $index => $nav)
                            @php
                                $isActive = request()->routeIs($nav['pattern']);
                                $hasChildren = isset($nav['children']) && count($nav['children']) > 0;
                                $routeExists = \Route::has($nav['route']);
                            @endphp
                            
                            {{-- Navigation Item with hover submenu --}}
                            <div 
                                class="group relative flex h-full items-center"
                                @click="handleClick($el)"
                                data-active="{{ $isActive ? 'true' : 'false' }}"
                            >
                                <a 
                                    href="{{ $routeExists ? route($nav['route']) : '#' }}" 
                                    wire:navigate
                                    class="inline-flex h-full items-center gap-1.5 px-3 text-sm font-normal transition-colors {{ $isActive ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }}"
                                >
                                    <flux:icon :name="$nav['icon']" class="size-4" />
                                    <span>{{ $nav['label'] }}</span>
                                </a>
                                
                                {{-- Hover Submenu --}}
                                @if($hasChildren)
                                    <div class="invisible absolute left-0 top-full z-50 min-w-[200px] rounded-lg border border-zinc-200 bg-white py-1 opacity-0 shadow-lg transition-all group-hover:visible group-hover:opacity-100 dark:border-zinc-700 dark:bg-zinc-900">
                                        @foreach($nav['children'] as $child)
                                            @php
                                                $childRouteExists = \Route::has($child['route']);
                                                $childIsActive = request()->routeIs($child['pattern']);
                                            @endphp
                                            <a 
                                                href="{{ $childRouteExists ? route($child['route']) : '#' }}" 
                                                wire:navigate
                                                class="flex items-center gap-2 px-4 py-2.5 text-sm transition-colors {{ $childIsActive ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100' }}"
                                            >
                                                @if(str_contains(strtolower($child['label']), 'create'))
                                                    <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                    </svg>
                                                @else
                                                    <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                    </svg>
                                                @endif
                                                {{ $child['label'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Spacer --}}
                    <div class="flex-1"></div>

                    {{-- Profile (only visible when scrolled) --}}
                    <div 
                        :class="scrolled ? 'opacity-100' : 'opacity-0 pointer-events-none'" 
                        class="flex h-full items-center transition-opacity duration-200"
                    >
                        <x-ui.profile-dropdown />
                    </div>
                </nav>
            </div>
        </header>

        {{-- Main Content --}}
        <main class="mx-auto w-full flex-1 px-6 py-6 sm:px-8 lg:px-16 xl:px-20 2xl:px-32">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <x-ui.footer />

        @fluxScripts
    </body>
</html>
