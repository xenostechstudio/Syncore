<div>
    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('accounting.journal-entries.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    {{ __('accounting.new_entry') }}
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">{{ __('accounting.overview') }}</span>
            </div>

            {{-- Right Group --}}
            <div class="flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                <flux:icon name="calendar" class="size-4" />
                <span>{{ $currentPeriod?->name ?? 'No Period' }}</span>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Two Column Layout --}}
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column --}}
            <div class="space-y-6 lg:col-span-4 lg:sticky lg:top-20 lg:h-fit">
                {{-- Quick Stats --}}
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Quick Stats</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Active Accounts</span>
                            <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ number_format($accountCount) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Draft Entries</span>
                            <span class="text-sm font-normal text-amber-600 dark:text-amber-400">{{ number_format($pendingEntries) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Posted Entries</span>
                            <span class="text-sm font-normal text-emerald-600 dark:text-emerald-400">{{ number_format($postedEntries ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Quick Actions</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <a href="{{ route('accounting.accounts.index') }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <flux:icon name="list-bullet" class="size-4 text-zinc-400" />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">Chart of Accounts</span>
                            </div>
                            <flux:icon name="chevron-right" class="size-4 text-zinc-400" />
                        </a>
                        <a href="{{ route('accounting.journal-entries.index') }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <flux:icon name="document-text" class="size-4 text-zinc-400" />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">Journal Entries</span>
                            </div>
                            <flux:icon name="chevron-right" class="size-4 text-zinc-400" />
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6 lg:col-span-8">
                {{-- Summary Cards --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="building-library" class="size-4 text-blue-500 dark:text-blue-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Assets</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($totalAssets / 1000000, 1) }}M</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Total asset value</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="credit-card" class="size-4 text-red-500 dark:text-red-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Liabilities</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($totalLiabilities / 1000000, 1) }}M</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Total liabilities</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="scale" class="size-4 text-violet-500 dark:text-violet-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Equity</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($totalEquity / 1000000, 1) }}M</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Owner's equity</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="arrow-trending-up" class="size-4 text-emerald-500 dark:text-emerald-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Revenue</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalRevenue / 1000000, 1) }}M</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Total revenue</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="arrow-trending-down" class="size-4 text-amber-500 dark:text-amber-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Expenses</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($totalExpenses / 1000000, 1) }}M</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Total expenses</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="banknotes" class="size-4 {{ $netIncome >= 0 ? 'text-emerald-500 dark:text-emerald-400' : 'text-red-500 dark:text-red-400' }}" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Net Income</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold {{ $netIncome >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">Rp {{ number_format($netIncome / 1000000, 1) }}M</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Revenue - Expenses</p>
                    </div>
                </div>

                {{-- Recent Journal Entries --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent Journal Entries</h2>
                        <a href="{{ route('accounting.journal-entries.index') }}" wire:navigate class="text-xs text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300">
                            View all →
                        </a>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($recentEntries as $entry)
                            <a href="{{ route('accounting.journal-entries.edit', $entry->id) }}" wire:navigate class="flex items-center justify-between px-5 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $entry->entry_number }}</span>
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $entry->status === 'posted' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                            {{ ucfirst($entry->status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $entry->entry_date->format('M d, Y') }} · {{ Str::limit($entry->description ?? $entry->reference ?? '-', 40) }}</p>
                                </div>
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($entry->total_debit, 0, ',', '.') }}</span>
                            </a>
                        @empty
                            <div class="px-5 py-8 text-center text-sm text-zinc-400">No journal entries yet</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
