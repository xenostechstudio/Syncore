<div>
    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group --}}
            <div class="flex items-center gap-3">
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Financial Reports</span>
            </div>

            {{-- Right Group: Filters --}}
            <div class="flex items-center gap-3">
                <select wire:model.live="period" class="rounded-lg border-zinc-200 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_quarter">This Quarter</option>
                    <option value="this_year">This Year</option>
                    <option value="last_year">Last Year</option>
                    <option value="custom">Custom Range</option>
                </select>
                @if($period === 'custom')
                    <input type="date" wire:model.live="startDate" class="rounded-lg border-zinc-200 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                    <span class="text-zinc-500">to</span>
                    <input type="date" wire:model.live="endDate" class="rounded-lg border-zinc-200 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                @endif
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2">
                    <flux:icon name="document-text" class="size-4 text-zinc-500 dark:text-zinc-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Total Invoiced</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($summary['total_invoiced'] / 1000000, 1) }}M</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $summary['invoice_count'] }} invoices</p>
            </div>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/20">
                <div class="flex items-center gap-2">
                    <flux:icon name="check-circle" class="size-4 text-emerald-600 dark:text-emerald-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Collected</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-emerald-700 dark:text-emerald-400">Rp {{ number_format($summary['total_collected'] / 1000000, 1) }}M</p>
                <p class="text-xs text-emerald-600 dark:text-emerald-400">{{ number_format($summary['collection_rate'], 1) }}% collection rate</p>
            </div>
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                <div class="flex items-center gap-2">
                    <flux:icon name="clock" class="size-4 text-amber-600 dark:text-amber-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">Outstanding</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-amber-700 dark:text-amber-400">Rp {{ number_format($summary['outstanding'] / 1000000, 1) }}M</p>
            </div>
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                <div class="flex items-center gap-2">
                    <flux:icon name="exclamation-triangle" class="size-4 text-red-600 dark:text-red-400" />
                    <p class="text-xs font-semibold uppercase tracking-widest text-red-600 dark:text-red-400">Overdue</p>
                </div>
                <p class="mt-2 text-2xl font-semibold text-red-700 dark:text-red-400">Rp {{ number_format($summary['overdue'] / 1000000, 1) }}M</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- AR Aging Report --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Accounts Receivable Aging</h2>
                </div>
                <div class="p-5">
                    <div class="space-y-3">
                        @php
                            $agingLabels = ['current' => 'Current', '1_30' => '1-30 Days', '31_60' => '31-60 Days', '61_90' => '61-90 Days', 'over_90' => 'Over 90 Days'];
                            $agingColors = ['current' => 'emerald', '1_30' => 'blue', '31_60' => 'amber', '61_90' => 'orange', 'over_90' => 'red'];
                        @endphp
                        @foreach($agingReport as $key => $data)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="h-3 w-3 rounded-full bg-{{ $agingColors[$key] }}-500"></div>
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $agingLabels[$key] }}</span>
                                    <span class="text-xs text-zinc-400">({{ $data['count'] }})</span>
                                </div>
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($data['amount'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Payments by Method --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Payments by Method</h2>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($paymentsByMethod as $payment)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $payment['payment_method'] }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $payment['payment_count'] }} payments</p>
                            </div>
                            <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Rp {{ number_format($payment['total_amount'], 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-zinc-400">No payments in this period</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
