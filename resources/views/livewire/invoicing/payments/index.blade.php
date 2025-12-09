<div>
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Payments</span>
            </div>

            {{-- Search --}}
            <div class="flex flex-1 items-center justify-center">
                <div class="relative w-full max-w-md">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search payments..."
                        class="w-full rounded-lg border border-zinc-200 bg-white py-2 pl-10 pr-4 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"
                    />
                    <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                </div>
            </div>

            <div></div>
        </div>
    </x-slot:header>

    {{-- Content --}}
    <div class="-mx-4 sm:-mx-6 lg:-mx-8">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Reference</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invoice</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Customer</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Payment Date</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Method</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-950">
                    @forelse($payments as $payment)
                        <tr class="group transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-900">
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
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
                                {{ ucfirst($payment->payment_method ?? '-') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No payments found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($payments->count() > 0)
                    <tfoot class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total</td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($payments->sum('amount'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Pagination --}}
        @if($payments->hasPages())
            <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-800 sm:px-6 lg:px-8">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
