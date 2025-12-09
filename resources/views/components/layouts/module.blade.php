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
                                    class="inline-flex h-full items-center gap-1.5 px-3 text-sm font-normal transition-colors whitespace-nowrap {{ $isActive ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }}"
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
                </nav>

                {{-- Spacer --}}
                <div class="flex-1"></div>

                {{-- Right Side: Profile --}}
                <div class="flex items-center pl-4">
                    <x-ui.profile-dropdown />
                </div>
            </div>

            {{-- Optional Header/Control Panel Slot --}}
            @if (isset($header))
                <div class="border-b border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950 lg:px-6">
                    {{ $header }}
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
