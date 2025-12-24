<div>
    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">
                {{ session('error') }}
            </x-ui.alert>
        @endif
    </div>

    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Payments</span>
            </div>

            <div class="flex flex-1 items-center justify-center">
                <x-ui.searchbox-dropdown placeholder="Search payments...">
                    <div class="p-3 text-sm text-zinc-600 dark:text-zinc-400">
                        Search by reference or invoice number
                    </div>
                </x-ui.searchbox-dropdown>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $payments->firstItem() ?? 0 }}-{{ $payments->lastItem() ?? 0 }}/{{ $payments->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button
                            type="button"
                            wire:click="goToPreviousPage"
                            @disabled($payments->onFirstPage())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button
                            type="button"
                            wire:click="goToNextPage"
                            @disabled(!$payments->hasMorePages())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                {{-- Stats Toggle --}}
                <button 
                    type="button"
                    wire:click="toggleStats"
                    class="flex h-8 w-8 items-center justify-center rounded-md transition-colors {{ $showStats ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100' : 'text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300' }}"
                    title="{{ $showStats ? 'Hide statistics' : 'Show statistics' }}"
                >
                    <flux:icon name="chart-bar" class="size-4" />
                </button>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div>
        {{-- Statistics Cards --}}
        @if($showStats && $statistics && !$payments->isEmpty())
            <div class="-mx-4 -mt-6 mb-6 border-b border-zinc-200 bg-white px-4 py-4 sm:-mx-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-950">
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="banknotes" class="size-4 text-emerald-500 dark:text-emerald-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Total Received</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['total_count']) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Rp {{ number_format($statistics['total_amount'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="document-text" class="size-4 text-blue-500 dark:text-blue-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Invoices Paid</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['invoices_count']) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Unique invoices</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="building-library" class="size-4 text-violet-500 dark:text-violet-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Bank Transfer</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['bank_transfer']) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Rp {{ number_format($statistics['bank_transfer_amount'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="currency-dollar" class="size-4 text-amber-500 dark:text-amber-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Cash</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['cash']) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Rp {{ number_format($statistics['cash_amount'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if($payments->isEmpty())
            {{-- Empty State --}}
            <div class="-mx-4 -mt-6 -mb-6 flex min-h-[70vh] items-center justify-center bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                <div class="-mt-16 flex flex-col items-center gap-4 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="banknotes" class="size-8 text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-base font-medium text-zinc-900 dark:text-zinc-100">No payments found</p>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Payments will appear here when invoices are paid</p>
                    </div>
                </div>
            </div>
        @else
        <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                    <tr>
                        <th scope="col" class="px-4 py-3 pl-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pl-6 lg:pl-8 dark:text-zinc-400">Reference</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invoice</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Customer</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Payment Date</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Method</th>
                        <th scope="col" class="px-4 py-3 pr-4 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pr-6 lg:pr-8 dark:text-zinc-400">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-950">
                    @foreach($payments as $payment)
                        <tr class="group transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-900">
                            <td class="whitespace-nowrap px-4 py-3 pl-4 font-medium text-zinc-900 sm:pl-6 lg:pl-8 dark:text-zinc-100">
                                {{ $payment->reference ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $payment->invoice->invoice_number ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $payment->invoice->customer->name ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $payment->payment_date?->format('M d, Y') ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                @php
                                    $methodConfig = match($payment->payment_method) {
                                        'bank_transfer' => ['icon' => 'building-library', 'label' => 'Bank Transfer', 'color' => 'text-violet-600 dark:text-violet-400'],
                                        'cash' => ['icon' => 'currency-dollar', 'label' => 'Cash', 'color' => 'text-amber-600 dark:text-amber-400'],
                                        'credit_card' => ['icon' => 'credit-card', 'label' => 'Credit Card', 'color' => 'text-blue-600 dark:text-blue-400'],
                                        default => ['icon' => 'banknotes', 'label' => ucfirst($payment->payment_method ?? '-'), 'color' => 'text-zinc-600 dark:text-zinc-400'],
                                    };
                                @endphp
                                <div class="flex items-center gap-1.5 {{ $methodConfig['color'] }}">
                                    <flux:icon name="{{ $methodConfig['icon'] }}" class="size-4" />
                                    <span>{{ $methodConfig['label'] }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 pr-4 text-right text-sm font-medium text-emerald-600 sm:pr-6 lg:pr-8 dark:text-emerald-400">
                                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950">
                    <tr>
                        <td colspan="5" class="py-3 pl-4 pr-4 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pl-6 lg:pl-8 dark:text-zinc-400">Total</td>
                        <td class="px-4 py-3 pr-4 text-right text-sm font-bold text-emerald-600 sm:pr-6 lg:pr-8 dark:text-emerald-400">
                            Rp {{ number_format($payments->sum('amount'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>
</div>
