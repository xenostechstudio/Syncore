<x-layouts.app-home>
    {{-- Abstract Gradient Background --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        {{-- Base gradient --}}
        <div class="absolute inset-0 bg-gradient-to-br from-violet-50 via-white to-cyan-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-950"></div>
        
        {{-- Animated gradient blobs --}}
        <div class="absolute -left-32 -top-32 h-[500px] w-[500px] rounded-full bg-gradient-to-br from-violet-400/30 via-purple-300/20 to-fuchsia-400/30 blur-3xl dark:from-violet-600/20 dark:via-purple-500/10 dark:to-fuchsia-600/20"></div>
        <div class="absolute -right-32 top-1/4 h-[600px] w-[600px] rounded-full bg-gradient-to-bl from-cyan-400/30 via-blue-300/20 to-indigo-400/30 blur-3xl dark:from-cyan-600/15 dark:via-blue-500/10 dark:to-indigo-600/15"></div>
        <div class="absolute -bottom-32 left-1/4 h-[500px] w-[500px] rounded-full bg-gradient-to-tr from-emerald-400/25 via-teal-300/20 to-cyan-400/25 blur-3xl dark:from-emerald-600/15 dark:via-teal-500/10 dark:to-cyan-600/15"></div>
        <div class="absolute right-1/4 top-1/2 h-[400px] w-[400px] rounded-full bg-gradient-to-tl from-amber-400/20 via-orange-300/15 to-rose-400/20 blur-3xl dark:from-amber-600/10 dark:via-orange-500/5 dark:to-rose-600/10"></div>
        
        {{-- Mesh overlay for depth --}}
        <div class="absolute inset-0 opacity-30 dark:opacity-20" style="background-image: radial-gradient(at 40% 20%, rgba(139, 92, 246, 0.15) 0px, transparent 50%), radial-gradient(at 80% 0%, rgba(6, 182, 212, 0.15) 0px, transparent 50%), radial-gradient(at 0% 50%, rgba(236, 72, 153, 0.1) 0px, transparent 50%), radial-gradient(at 80% 50%, rgba(16, 185, 129, 0.1) 0px, transparent 50%), radial-gradient(at 0% 100%, rgba(99, 102, 241, 0.15) 0px, transparent 50%), radial-gradient(at 80% 100%, rgba(244, 114, 182, 0.1) 0px, transparent 50%), radial-gradient(at 0% 0%, rgba(251, 191, 36, 0.1) 0px, transparent 50%);"></div>
    </div>

    {{-- Welcome Section --}}
    <div class="relative mb-8 pt-4">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
            Welcome back, {{ auth()->user()->name }}
        </h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Select a module to get started</p>
    </div>

    {{-- Module Grid --}}
    <div class="relative grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        {{-- Sales --}}
        @can('access.sales')
            <a href="{{ route('sales.index') }}" wire:navigate class="group relative flex items-center gap-4 rounded-2xl border border-white/40 bg-white/40 p-5 backdrop-blur-md transition-all hover:border-white/60 hover:bg-white/60 hover:shadow-lg hover:shadow-violet-500/10 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20 dark:hover:bg-white/10">
                <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow-lg shadow-violet-500/30">
                    <svg class="size-7" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Sales</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Orders & Customers</p>
                </div>
                <flux:icon name="chevron-right" class="size-5 text-zinc-300 transition-transform group-hover:translate-x-1 group-hover:text-zinc-500 dark:text-zinc-500 dark:group-hover:text-zinc-300" />
            </a>
        @endcan

        {{-- Invoicing --}}
        @can('access.invoicing')
            <a href="{{ route('invoicing.index') }}" wire:navigate class="group relative flex items-center gap-4 rounded-2xl border border-white/40 bg-white/40 p-5 backdrop-blur-md transition-all hover:border-white/60 hover:bg-white/60 hover:shadow-lg hover:shadow-emerald-500/10 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20 dark:hover:bg-white/10">
                <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30">
                    <svg class="size-7" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Invoicing</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Invoices & Payments</p>
                </div>
                <flux:icon name="chevron-right" class="size-5 text-zinc-300 transition-transform group-hover:translate-x-1 group-hover:text-zinc-500 dark:text-zinc-500 dark:group-hover:text-zinc-300" />
            </a>
        @endcan

        {{-- Inventory --}}
        @can('access.inventory')
            <a href="{{ route('inventory.index') }}" wire:navigate class="group relative flex items-center gap-4 rounded-2xl border border-white/40 bg-white/40 p-5 backdrop-blur-md transition-all hover:border-white/60 hover:bg-white/60 hover:shadow-lg hover:shadow-blue-500/10 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20 dark:hover:bg-white/10">
                <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-500/30">
                    <svg class="size-7" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 2H4c-1 0-2 .9-2 2v3.01c0 .72.43 1.34 1 1.69V20c0 1.1 1.1 2 2 2h14c.9 0 2-.9 2-2V8.7c.57-.35 1-.97 1-1.69V4c0-1.1-1-2-2-2zm-5 12H9v-2h6v2zm5-7H4V4h16v3z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Inventory</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Stock & Warehouses</p>
                </div>
                <flux:icon name="chevron-right" class="size-5 text-zinc-300 transition-transform group-hover:translate-x-1 group-hover:text-zinc-500 dark:text-zinc-500 dark:group-hover:text-zinc-300" />
            </a>
        @endcan

        {{-- Purchase --}}
        @can('access.purchase')
            <a href="{{ route('purchase.rfq.index') }}" wire:navigate class="group relative flex items-center gap-4 rounded-2xl border border-white/40 bg-white/40 p-5 backdrop-blur-md transition-all hover:border-white/60 hover:bg-white/60 hover:shadow-lg hover:shadow-amber-500/10 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20 dark:hover:bg-white/10">
                <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-amber-500/30">
                    <svg class="size-7" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6-2c1.1 0 2 .9 2 2h-4c0-1.1.9-2 2-2zm6 16H6V8h2v2c0 .55.45 1 1 1s1-.45 1-1V8h4v2c0 .55.45 1 1 1s1-.45 1-1V8h2v12z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Purchase</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">RFQ & Purchase Orders</p>
                </div>
                <flux:icon name="chevron-right" class="size-5 text-zinc-300 transition-transform group-hover:translate-x-1 group-hover:text-zinc-500 dark:text-zinc-500 dark:group-hover:text-zinc-300" />
            </a>
        @endcan

        {{-- Delivery --}}
        @can('access.delivery')
            <a href="{{ route('delivery.index') }}" wire:navigate class="group relative flex items-center gap-4 rounded-2xl border border-white/40 bg-white/40 p-5 backdrop-blur-md transition-all hover:border-white/60 hover:bg-white/60 hover:shadow-lg hover:shadow-cyan-500/10 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20 dark:hover:bg-white/10">
                <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/30">
                    <svg class="size-7" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Delivery</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Shipments & Tracking</p>
                </div>
                <flux:icon name="chevron-right" class="size-5 text-zinc-300 transition-transform group-hover:translate-x-1 group-hover:text-zinc-500 dark:text-zinc-500 dark:group-hover:text-zinc-300" />
            </a>
        @endcan

        {{-- Settings --}}
        @can('access.settings')
            <a href="{{ route('settings.index') }}" wire:navigate class="group relative flex items-center gap-4 rounded-2xl border border-white/40 bg-white/40 p-5 backdrop-blur-md transition-all hover:border-white/60 hover:bg-white/60 hover:shadow-lg hover:shadow-zinc-500/10 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20 dark:hover:bg-white/10">
                <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-zinc-600 to-zinc-800 text-white shadow-lg shadow-zinc-500/30 dark:from-zinc-500 dark:to-zinc-700">
                    <svg class="size-7" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19.14 12.94c.04-.31.06-.63.06-.94 0-.31-.02-.63-.06-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Settings</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Configuration</p>
                </div>
                <flux:icon name="chevron-right" class="size-5 text-zinc-300 transition-transform group-hover:translate-x-1 group-hover:text-zinc-500 dark:text-zinc-500 dark:group-hover:text-zinc-300" />
            </a>
        @endcan
    </div>
</x-layouts.app-home>
