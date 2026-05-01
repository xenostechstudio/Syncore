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

                {{-- Form slot — this is what changes per page --}}
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

                    <div class="relative flex h-full flex-col justify-between p-14 xl:p-16">

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
                            <div class="flex items-end gap-4"
                                 style="animation: syncore-fade-up 0.7s ease-out both;">
                                <x-app-logo-icon class="mb-2 size-12 fill-current text-zinc-100 xl:size-14" />
                                <h1 class="font-bold leading-[0.92] tracking-[-0.045em] text-zinc-50"
                                    style="font-size: clamp(60px, 9vw, 112px);">
                                    Syncore
                                </h1>
                            </div>

                            <p class="mt-5 max-w-md text-lg leading-relaxed text-zinc-400 xl:text-xl"
                               style="animation: syncore-fade-up 0.8s ease-out 0.12s both;">
                                Run the work — sales, purchase, inventory, and the rest, in one calm system.
                            </p>

                            {{-- Modules manifest --}}
                            <div class="mt-12 max-w-md">
                                <div class="mb-4 flex items-center gap-3 font-mono text-[10px] uppercase tracking-[0.28em] text-zinc-600"
                                     style="animation: syncore-fade-up 0.5s ease-out 0.25s both;">
                                    <span class="h-px w-5 bg-zinc-700"></span>
                                    <span>Modules</span>
                                </div>
                                <ul class="grid grid-cols-2 gap-x-10 gap-y-3 font-mono text-sm text-zinc-500">
                                    @php
                                        $modules = ['sales', 'purchase', 'inventory', 'invoicing', 'delivery', 'accounting', 'hr', 'crm'];
                                    @endphp
                                    @foreach($modules as $i => $name)
                                        <li class="flex items-center gap-3"
                                            style="animation: syncore-fade-up 0.5s ease-out {{ 0.32 + $i * 0.055 }}s both;">
                                            <x-app-logo-icon class="size-[7px] shrink-0 fill-current text-zinc-700" />
                                            <span class="lowercase tracking-wide">{{ $name }}</span>
                                        </li>
                                    @endforeach
                                </ul>
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

        @fluxScripts
    </body>
</html>
