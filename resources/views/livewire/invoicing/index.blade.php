<div class="flex flex-col gap-6">
    <x-slot:header>
        <div class="flex items-center">
            <h1 class="text-base font-light text-zinc-600 dark:text-zinc-400">Overview</h1>
        </div>
    </x-slot:header>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        {{-- Total Invoices --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Total Invoices</span>
                <svg class="size-4 text-zinc-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                </svg>
            </div>
            <p class="mt-2 text-2xl font-light text-zinc-900 dark:text-zinc-100">0</p>
            <p class="mt-1 text-xs font-light text-zinc-400 dark:text-zinc-500">0 this month</p>
        </div>

        {{-- Total Revenue --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Total Revenue</span>
                <svg class="size-4 text-zinc-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"/>
                </svg>
            </div>
            <p class="mt-2 text-2xl font-light text-zinc-900 dark:text-zinc-100">Rp 0</p>
            <p class="mt-1 text-xs font-light text-emerald-600 dark:text-emerald-400">Rp 0 paid</p>
        </div>

        {{-- Pending Invoices --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Pending</span>
                <svg class="size-4 text-zinc-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z"/>
                </svg>
            </div>
            <p class="mt-2 text-2xl font-light text-zinc-900 dark:text-zinc-100">0</p>
            <p class="mt-1 text-xs font-light text-amber-600 dark:text-amber-400">Awaiting payment</p>
        </div>

        {{-- Overdue --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Overdue</span>
                <svg class="size-4 text-zinc-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            <p class="mt-2 text-2xl font-light text-zinc-900 dark:text-zinc-100">0</p>
            <p class="mt-1 text-xs font-light text-red-600 dark:text-red-400">Needs attention</p>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('invoicing.invoices.index') }}" wire:navigate class="group flex items-center gap-4 rounded-lg border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400">
                <svg class="size-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="font-medium text-zinc-900 dark:text-zinc-100">Invoices</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Manage customer invoices</p>
            </div>
            <flux:icon name="chevron-right" class="size-5 text-zinc-300 transition-transform group-hover:translate-x-1 dark:text-zinc-600" />
        </a>

        <a href="{{ route('invoicing.payments.index') }}" wire:navigate class="group flex items-center gap-4 rounded-lg border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                <svg class="size-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="font-medium text-zinc-900 dark:text-zinc-100">Payments</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Track payment records</p>
            </div>
            <flux:icon name="chevron-right" class="size-5 text-zinc-300 transition-transform group-hover:translate-x-1 dark:text-zinc-600" />
        </a>

        <a href="{{ route('invoicing.reports') }}" wire:navigate class="group flex items-center gap-4 rounded-lg border border-zinc-200 bg-white p-5 transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-950 dark:text-violet-400">
                <svg class="size-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="font-medium text-zinc-900 dark:text-zinc-100">Reports</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Financial analytics</p>
            </div>
            <flux:icon name="chevron-right" class="size-5 text-zinc-300 transition-transform group-hover:translate-x-1 dark:text-zinc-600" />
        </a>
    </div>
</div>
