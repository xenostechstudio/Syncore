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
        {{-- Sticky Header with Navigation --}}
        <header class="sticky top-0 z-50 bg-white dark:bg-zinc-950">
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
                    {{-- Icon Container with Swap Effect --}}
                    <div class="relative flex h-8 w-8 items-center justify-center">
                        {{-- Module Icon (default) --}}
                        <flux:icon 
                            :name="$moduleMeta['icon']" 
                            class="absolute size-5 text-zinc-600 transition-all duration-200 dark:text-zinc-400"
                            x-bind:class="hovering ? 'opacity-0 scale-75' : 'opacity-100 scale-100'"
                        />
                        {{-- Back Arrow (on hover) --}}
                        <flux:icon 
                            name="arrow-left" 
                            class="absolute size-5 text-zinc-900 transition-all duration-200 dark:text-zinc-100"
                            x-bind:class="hovering ? 'opacity-100 scale-100' : 'opacity-0 scale-75'"
                        />
                    </div>
                    {{-- Module Name --}}
                    <span class="text-md font-medium text-zinc-900 whitespace-nowrap transition-colors group-hover:text-zinc-600 dark:text-zinc-100 dark:group-hover:text-zinc-300">{{ $displayName }}</span>
                </a>

                {{-- Middle: Navigation --}}
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
                    class="relative hidden h-full items-center md:flex"
                >
                    <div x-ref="navContainer" class="relative flex h-full items-center gap-1">
                        {{-- Animated Underline Indicator --}}
                        <div 
                            class="absolute bottom-0 h-0.5 bg-zinc-900 transition-all duration-300 ease-out dark:bg-zinc-100"
                            :class="indicatorVisible ? 'opacity-100' : 'opacity-0'"
                            :style="`left: ${indicatorLeft}px; width: ${indicatorWidth}px;`"
                        ></div>

                        @foreach($navItems as $index => $nav)
                            @php
                                // Handle multiple patterns separated by |
                                $patterns = explode('|', $nav['pattern']);
                                $isActive = collect($patterns)->contains(fn($p) => request()->routeIs(trim($p)));
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
                                    class="inline-flex h-full items-center gap-1.5 px-3 text-sm font-normal transition-colors whitespace-nowrap {{ $isActive ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }}"
                                >
                                    <flux:icon :name="$nav['icon']" class="size-4" />
                                    <span>{{ $nav['label'] }}</span>
                                </a>
                                
                                {{-- Hover Submenu --}}
                                @if($hasChildren)
                                    <div class="invisible absolute left-0 top-full z-50 min-w-[180px] rounded-lg border border-zinc-200 bg-white py-1 opacity-0 shadow-lg transition-all group-hover:visible group-hover:opacity-100 dark:border-zinc-700 dark:bg-zinc-900">
                                        @foreach($nav['children'] as $child)
                                            @php
                                                $childRouteExists = \Route::has($child['route']);
                                                $childPatterns = explode('|', $child['pattern']);
                                                $childIsActive = collect($childPatterns)->contains(fn($p) => request()->routeIs(trim($p)));
                                            @endphp
                                            <a 
                                                href="{{ $childRouteExists ? route($child['route']) : '#' }}" 
                                                wire:navigate
                                                class="block px-4 py-2 text-sm transition-colors {{ $childIsActive ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100' }}"
                                            >
                                                {{ $child['label'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </nav>

                {{-- Spacer --}}
                <div class="flex-1"></div>

                {{-- Right Side: Company Name, Notification, Profile --}}
                <div class="flex items-center gap-4 pl-4">
                    {{-- Company Name --}}
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ \App\Models\Settings\CompanyProfile::getCompanyName() }}
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

            {{-- Optional Header/Control Panel Slot --}}
            @if (isset($header))
                <div class="flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 dark:border-zinc-800 dark:bg-zinc-950 lg:px-6">
                    <div class="w-full">
                        {{ $header }}
                    </div>
                </div>
            @endif
        </header>

        {{-- Main Content --}}
        <main class="mx-auto w-full flex-1 px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <x-ui.footer />

        @fluxScripts
    </body>
</html>
