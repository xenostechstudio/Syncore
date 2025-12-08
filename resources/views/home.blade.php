<x-layouts.app-home>
    <div class="space-y-8">
        {{-- Header --}}
        <div class="space-y-2">
            <h1 class="text-2xl font-normal text-zinc-900 dark:text-zinc-100">Core Modules</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Select a module to get started.</p>
        </div>

        {{-- Module Grid --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Inventory - Active --}}
            <a href="{{ route('inventory.index') }}" wire:navigate class="group relative flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                    <flux:icon.archive-box class="size-5" />
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Inventory</span>
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">Active</span>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Multi-warehouse stock control</p>
                </div>
                <div class="absolute right-4 top-4 opacity-0 transition-opacity group-hover:opacity-100">
                    <flux:icon.arrow-right class="size-4 text-zinc-400" />
                </div>
            </a>

            {{-- Sales Order - Active --}}
            <a href="{{ route('sales.index') }}" wire:navigate class="group relative flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400">
                    <flux:icon.shopping-cart class="size-5" />
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Sales Order</span>
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">Active</span>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Manage customer orders</p>
                </div>
                <div class="absolute right-4 top-4 opacity-0 transition-opacity group-hover:opacity-100">
                    <flux:icon.arrow-right class="size-4 text-zinc-400" />
                </div>
            </a>

            {{-- Purchase Order - Coming Soon --}}
            <div class="flex flex-col gap-4 rounded-xl border border-dashed border-zinc-200 bg-zinc-50/50 p-5 dark:border-zinc-800 dark:bg-zinc-900/50">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-500">
                    <flux:icon.document-text class="size-5" />
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Purchase Order</span>
                        <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-500">Soon</span>
                    </div>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500">Procurement management</p>
                </div>
            </div>

            {{-- Delivery Order - Active --}}
            <a href="{{ route('delivery.index') }}" wire:navigate class="group relative flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-950 dark:text-violet-400">
                    <flux:icon.truck class="size-5" />
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Delivery Order</span>
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">Active</span>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Shipment tracking</p>
                </div>
                <div class="absolute right-4 top-4 opacity-0 transition-opacity group-hover:opacity-100">
                    <flux:icon.arrow-right class="size-4 text-zinc-400" />
                </div>
            </a>

            {{-- General Setup - Active --}}
            <a href="{{ route('settings.index') }}" wire:navigate class="group relative flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                    <flux:icon.cog-6-tooth class="size-5" />
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">General Setup</span>
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">Active</span>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">System configuration</p>
                </div>
                <div class="absolute right-4 top-4 opacity-0 transition-opacity group-hover:opacity-100">
                    <flux:icon.arrow-right class="size-4 text-zinc-400" />
                </div>
            </a>
        </div>
    </div>
</x-layouts.app-home>
