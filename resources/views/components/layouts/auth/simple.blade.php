<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500&display=swap" rel="stylesheet" />
        <style>
            @keyframes syncore-fade-up {
                from { opacity: 0; transform: translateY(14px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            @keyframes syncore-pulse {
                0%, 100% { opacity: 0.5; transform: scale(1); }
                50%      { opacity: 0.15; transform: scale(2.4); }
            }
            /* Animations only run on initial load.  Once the body gets
               data-syncore-ready (after first paint, or on any subsequent
               wire:navigate), persisted elements that get re-inserted
               into the DOM won't re-animate. */
            .syncore-anim {
                animation: syncore-fade-up 0.6s ease-out var(--syncore-delay, 0s) both;
            }
            body[data-syncore-ready] .syncore-anim {
                animation: none !important;
                opacity: 1;
                transform: none;
            }
        </style>
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased selection:bg-zinc-100 selection:text-zinc-900">

        <main class="grid min-h-screen lg:grid-cols-2">

            {{-- ───────────────  Form pane (left)  ─────────────── --}}
            <section class="relative flex flex-col bg-zinc-900 px-6 py-8 sm:px-10 lg:px-14">

                @persist('syncore-auth-form-header')
                    <header class="flex items-center justify-between">
                        <a href="{{ route('home') }}" wire:navigate class="group inline-flex items-center gap-2 text-zinc-100 transition">
                            <x-app-logo-icon class="size-5 fill-current text-zinc-100 transition group-hover:rotate-[8deg]" />
                            <span class="text-md font-medium tracking-tight">Syncore</span>
                        </a>
                        <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-zinc-600">
                            {{ strtoupper(app()->getLocale()) }} / Auth
                        </span>
                    </header>
                @endpersist

                {{-- Form slot — the only thing that changes between login/register/forgot --}}
                <div class="flex flex-1 items-center justify-center">
                    <div class="w-full max-w-[22rem] py-12">
                        {{ $slot }}
                    </div>
                </div>

                @persist('syncore-auth-form-footer')
                    <footer class="flex flex-wrap items-center justify-between gap-3 text-xs text-zinc-600">
                        <span class="font-mono uppercase tracking-[0.18em]">{{ now()->year }} · Syncore</span>
                        <div class="flex items-center gap-4 font-mono uppercase tracking-[0.18em]">
                            <a href="#" class="transition hover:text-zinc-300">Privacy</a>
                            <span class="text-zinc-800">/</span>
                            <a href="#" class="transition hover:text-zinc-300">Terms</a>
                            <span class="text-zinc-800">/</span>
                            <a href="mailto:hello@syncore.app" class="transition hover:text-zinc-300">Help</a>
                        </div>
                    </footer>
                @endpersist
            </section>

            {{-- ───────────────  Brand pane (right) — persists across page swaps  ─────────────── --}}
            @persist('syncore-auth-brand-pane')
                <aside class="relative hidden overflow-hidden bg-zinc-950 lg:block">

                    {{-- Hairline grid texture --}}
                    <div class="pointer-events-none absolute inset-0 opacity-[0.06]"
                         style="background-image:
                                    linear-gradient(to right, white 1px, transparent 1px),
                                    linear-gradient(to bottom, white 1px, transparent 1px);
                                background-size: 64px 64px;
                                mask-image: radial-gradient(ellipse 80% 70% at 30% 40%, black 30%, transparent 75%);"></div>

                    {{-- Soft warm spotlight --}}
                    <div class="pointer-events-none absolute inset-0"
                         style="background: radial-gradient(circle at 28% 22%, rgba(250,240,220,0.06) 0%, transparent 55%);"></div>

                    {{-- Hairline border on the seam --}}
                    <div class="pointer-events-none absolute inset-y-0 left-0 w-px bg-gradient-to-b from-transparent via-zinc-800 to-transparent"></div>

                    <div class="relative flex h-full flex-col justify-between p-12 xl:p-16">

                        {{-- Top stamp --}}
                        <div class="flex items-start justify-between">
                            <div class="font-mono text-[10px] uppercase tracking-[0.28em] text-zinc-600">
                                Syncore <span class="text-zinc-800">·</span> Operations Suite
                            </div>
                            <div class="text-right font-mono text-[10px] uppercase tracking-[0.22em] text-zinc-700">
                                <div>v1.0</div>
                                <div class="mt-1">build · {{ substr(md5(config('app.key', 'syncore')), 0, 7) }}</div>
                            </div>
                        </div>

                        {{-- Center block --}}
                        <div>
                            <div class="syncore-anim flex items-end gap-4">
                                <x-app-logo-icon class="mb-2 size-12 fill-current text-zinc-100 xl:size-14" />
                                <h1 class="font-bold leading-[0.92] tracking-[-0.045em] text-zinc-50"
                                    style="font-size: clamp(60px, 9vw, 112px);">
                                    Syncore
                                </h1>
                            </div>

                            <p class="syncore-anim mt-5 max-w-md text-lg leading-relaxed text-zinc-400 xl:text-xl"
                               style="--syncore-delay: 0.12s;">
                                Run the work — sales, purchase, inventory, and the rest, in one calm system.
                            </p>

                            {{-- Modules manifest --}}
                            <div class="mt-10 max-w-md">
                                <div class="syncore-anim mb-4 flex items-center gap-3 font-mono text-[10px] uppercase tracking-[0.28em] text-zinc-600"
                                     style="--syncore-delay: 0.25s;">
                                    <span class="h-px w-5 bg-zinc-700"></span>
                                    <span>Modules</span>
                                </div>
                                <ul class="grid grid-cols-2 gap-x-10 gap-y-3 font-mono text-sm text-zinc-500">
                                    @php
                                        $modules = ['sales', 'purchase', 'inventory', 'invoicing', 'delivery', 'accounting', 'hr', 'crm'];
                                    @endphp
                                    @foreach($modules as $i => $name)
                                        <li class="syncore-anim flex items-center gap-3"
                                            style="--syncore-delay: {{ 0.32 + $i * 0.055 }}s;">
                                            <x-app-logo-icon class="size-[7px] shrink-0 fill-current text-zinc-700" />
                                            <span class="lowercase tracking-wide">{{ $name }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            {{-- App download --}}
                            <div class="mt-10 max-w-md">
                                <div class="syncore-anim mb-4 flex items-center gap-3 font-mono text-[10px] uppercase tracking-[0.28em] text-zinc-600"
                                     style="--syncore-delay: 0.85s;">
                                    <span class="h-px w-5 bg-zinc-700"></span>
                                    <span>Get the app</span>
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    <a href="#" class="syncore-anim group inline-flex items-center gap-2.5 rounded-md border border-zinc-800 bg-zinc-900/40 px-3.5 py-2 transition hover:border-zinc-700 hover:bg-zinc-900"
                                       style="--syncore-delay: 0.92s;"
                                       aria-label="Download Syncore on the App Store">
                                        <svg class="size-5 text-zinc-200 transition group-hover:text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                                        </svg>
                                        <div class="text-left">
                                            <div class="font-mono text-[9px] uppercase tracking-[0.18em] leading-none text-zinc-500">Download on the</div>
                                            <div class="mt-0.5 text-sm font-medium leading-none text-zinc-100">App Store</div>
                                        </div>
                                    </a>
                                    <a href="#" class="syncore-anim group inline-flex items-center gap-2.5 rounded-md border border-zinc-800 bg-zinc-900/40 px-3.5 py-2 transition hover:border-zinc-700 hover:bg-zinc-900"
                                       style="--syncore-delay: 1.0s;"
                                       aria-label="Get Syncore on Google Play">
                                        <svg class="size-5 text-zinc-200 transition group-hover:text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M3.609 1.814 13.792 12 3.61 22.186c-.181-.181-.342-.396-.474-.642C3.04 21.279 3 21.05 3 20.81V3.19c0-.24.04-.469.137-.734.131-.246.292-.461.473-.642zM14.5 12.7l2.732-2.732 3.34 1.892c.84.476.84 1.602 0 2.078l-3.34 1.892L14.5 13.114V12.7zm-.708-.708 7.08-4.013-4.348-2.464a1.502 1.502 0 0 0-1.467-.018L4.337 1.5l9.455 9.456v1.036zm0 1.05L4.337 22.5l10.72-3.997c.453-.247.967-.241 1.418.012l4.348-2.464-7.031-4.001z"/>
                                        </svg>
                                        <div class="text-left">
                                            <div class="font-mono text-[9px] uppercase tracking-[0.18em] leading-none text-zinc-500">Get it on</div>
                                            <div class="mt-0.5 text-sm font-medium leading-none text-zinc-100">Google Play</div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Bottom: status row --}}
                        <div class="flex items-end justify-between">
                            <div class="flex items-center gap-3 font-mono text-[11px] uppercase tracking-[0.22em] text-zinc-500">
                                <span class="relative flex size-2">
                                    <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400"
                                          style="animation: syncore-pulse 2.6s cubic-bezier(0.4, 0, 0.6, 1) infinite;"></span>
                                    <span class="relative inline-flex size-2 rounded-full bg-emerald-400"></span>
                                </span>
                                <span>Operational</span>
                            </div>
                            <span class="font-mono text-[10px] uppercase tracking-[0.22em] text-zinc-700">
                                All systems · {{ now()->format('Y.m.d') }}
                            </span>
                        </div>
                    </div>
                </aside>
            @endpersist
        </main>

        <script data-navigate-once>
            // Mark the body as ready after the first staggered intro completes.
            // Once set, the .syncore-anim CSS rule disables the animation, so
            // when persisted elements get re-inserted during wire:navigate
            // they don't re-trigger their CSS animations.
            setTimeout(() => document.body.setAttribute('data-syncore-ready', ''), 1500);
            document.addEventListener('livewire:navigated', () => {
                document.body.setAttribute('data-syncore-ready', '');
            });
        </script>

        @fluxScripts
    </body>
</html>
