<div class="space-y-8">
    @if($expired)
        <div class="rounded-2xl border border-red-100 bg-white/90 p-8 text-center shadow-sm">
            <flux:icon name="exclamation-triangle" class="mx-auto mb-4 size-10 text-red-500" />
            <h1 class="text-2xl font-semibold text-red-700">Link Expired</h1>
            <p class="mt-2 text-sm text-zinc-500">Please request a new invoice link from {{ $company->company_email ?? 'our team' }}.</p>
        </div>
    @elseif(! $invoice)
        <div class="rounded-2xl border border-zinc-100 bg-white/90 p-8 text-center shadow-sm">
            <flux:icon name="question-mark-circle" class="mx-auto mb-4 size-10 text-zinc-400" />
            <h1 class="text-2xl font-semibold text-zinc-700">Invoice not found</h1>
            <p class="mt-2 text-sm text-zinc-500">The link may be incorrect or revoked. Please contact {{ $company->company_email ?? 'support' }}.</p>
        </div>
    @else
        <div class="rounded-2xl border border-zinc-100 bg-white p-4 shadow-sm sm:p-6">
            <div class="grid gap-4 sm:gap-6 lg:grid-cols-[1fr,360px] lg:items-start">
                <div class="grid gap-4 sm:gap-6 md:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Billed To</p>
                        <p class="mt-2 text-base font-semibold text-zinc-900">{{ $invoice->customer->name ?? 'Customer' }}</p>
                        <p class="text-sm text-zinc-500">{{ $invoice->customer->email ?? '-' }}</p>
                        <p class="text-sm text-zinc-500">{{ $invoice->customer->address ?? '' }}</p>
                    </div>
                    <div class="md:text-right">
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Company</p>
                        <p class="mt-2 text-base font-semibold text-zinc-900">{{ $company->company_name }}</p>
                        <p class="text-sm text-zinc-500">{{ $company->company_email }}</p>
                        <p class="text-sm text-zinc-500">{{ $company->company_address }}</p>
                    </div>
                </div>

                <div class="rounded-xl bg-zinc-900 px-5 py-4 text-white">
                    <p class="text-sm font-semibold">Invoice: {{ $invoice->invoice_number }}</p>
                    <p class="mt-1 text-2xl font-semibold">Rp {{ number_format($invoice->total, 0, ',', '.') }}</p>
                    <p class="mt-2 text-xs text-white/70">Due {{ optional($invoice->due_date)->format('M d, Y') ?? 'Upon receipt' }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:gap-6 lg:grid-cols-[1.35fr,1fr]">
            <div class="space-y-4 sm:space-y-6">
                <div class="overflow-hidden rounded-2xl border border-zinc-100 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[720px]">
                        <thead>
                            <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Description</th>
                                <th class="w-20 px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Qty</th>
                                <th class="w-32 px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Unit Price</th>
                                <th class="w-28 px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Tax</th>
                                <th class="w-32 px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                            @forelse($invoice->items as $item)
                                @php
                                    $lineTax = 0;
                                    if (($invoice->subtotal ?? 0) > 0) {
                                        $lineTax = (float) $invoice->tax * ((float) $item->total / (float) $invoice->subtotal);
                                    }
                                @endphp
                                <tr class="group transition-colors hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30">
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-medium text-zinc-900">{{ $item->product->name ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-zinc-600">{{ $item->description ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm text-zinc-900">{{ $item->quantity }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm text-zinc-900">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm text-zinc-600">Rp {{ number_format($lineTax, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-medium text-zinc-900">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-zinc-400">No lines on this invoice.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        </table>
                    </div>
                    <div class="border-t border-zinc-100 bg-zinc-50/50 p-5 dark:border-zinc-800 dark:bg-zinc-900/30">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div class="flex-1">
                                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Notes</p>
                                <p class="mt-1 text-sm text-zinc-600">{{ $invoice->notes ?? '—' }}</p>
                            </div>

                            <div class="w-full space-y-2 lg:w-72">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-500">Untaxed Amount</span>
                                    <span class="text-zinc-900">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-500">Taxes</span>
                                    <span class="text-zinc-900">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between border-t border-zinc-200 pt-2">
                                    <span class="font-medium text-zinc-900">Total</span>
                                    <span class="text-lg font-semibold text-zinc-900">Rp {{ number_format($invoice->total, 0, ',', '.') }}</span>
                                </div>
                                @if(($invoice->paid_amount ?? 0) > 0)
                                    <div class="flex items-center justify-between text-sm text-emerald-600">
                                        <span>Paid</span>
                                        <span class="font-semibold">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-500">Amount Due</span>
                                    <span class="text-lg font-semibold text-zinc-900">Rp {{ number_format($invoice->total - ($invoice->paid_amount ?? 0), 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4 sm:space-y-6">
                <div class="rounded-2xl border border-zinc-100 bg-white p-4 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Payment</p>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="window.print()" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-50 sm:flex-none">
                                <flux:icon name="printer" class="size-4" />
                                Print
                            </button>
                            <button type="button" onclick="window.print()" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-zinc-800 sm:flex-none">
                                <flux:icon name="arrow-down-tray" class="size-4" />
                                Download
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-zinc-200 bg-zinc-100/80 p-1">
                        <div class="grid grid-cols-2 gap-1">
                            <button
                                type="button"
                                wire:click="selectPaymentMethod('online')"
                                class="flex items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold transition-all focus:outline-none {{ $selectedPaymentMethod === 'online' ? 'bg-white text-zinc-900 shadow-sm ring-1 ring-zinc-900/5' : 'text-zinc-500 hover:text-zinc-700 hover:bg-white/50' }}"
                            >
                                <flux:icon name="credit-card" class="size-4" />
                                <span class="hidden sm:inline">Online</span>
                                <span class="sm:hidden">Online</span>
                            </button>
                            <button
                                type="button"
                                wire:click="selectPaymentMethod('manual')"
                                class="flex items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold transition-all focus:outline-none {{ $selectedPaymentMethod === 'manual' ? 'bg-white text-zinc-900 shadow-sm ring-1 ring-zinc-900/5' : 'text-zinc-500 hover:text-zinc-700 hover:bg-white/50' }}"
                            >
                                <flux:icon name="building-library" class="size-4" />
                                <span class="hidden sm:inline">Bank transfer</span>
                                <span class="sm:hidden">Transfer</span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 sm:mt-6">
                        @if($selectedPaymentMethod === 'online')
                            <div class="rounded-xl border border-zinc-200 bg-gradient-to-br from-zinc-50 to-zinc-100/50 p-4">
                                <div class="mb-3 flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-900 text-white">
                                        <flux:icon name="credit-card" class="size-4" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-zinc-900">Online Payment</p>
                                        <p class="text-xs text-zinc-500">VA, e-wallet, or card</p>
                                    </div>
                                </div>
                                <p class="mb-4 text-sm text-zinc-600">Pay securely via Xendit. You will be redirected to complete payment.</p>
                                @if($paymentLink)
                                    <a href="{{ $paymentLink }}" target="_blank" class="inline-flex w-full items-center justify-center rounded-xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-zinc-900/20 transition-colors hover:bg-zinc-800">
                                        <flux:icon name="arrow-top-right-on-square" class="mr-2 size-4" />
                                        Proceed to payment
                                    </a>
                                @else
                                    <button type="button" wire:click="requestPaymentLink" class="inline-flex w-full items-center justify-center rounded-xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-zinc-900/20 transition-colors hover:bg-zinc-800">
                                        <flux:icon name="sparkles" class="mr-2 size-4" />
                                        Generate payment link
                                    </button>
                                @endif
                                @if($statusMessage)
                                    <p class="mt-2 text-xs {{ $statusIsError ? 'text-red-500' : 'text-emerald-600' }}">{{ $statusMessage }}</p>
                                @endif
                            </div>
                        @else
                            @php
                                $bankName = 'Bank Mandiri';
                                $bankAccountName = 'PT ' . ($company->company_name ?? '');
                                $bankAccountNumberDisplay = '123 4567 890';
                                $bankAccountNumberCopy = preg_replace('/\D+/', '', $bankAccountNumberDisplay);
                                $amountDisplay = 'Rp ' . number_format($invoice->total, 0, ',', '.');
                                $amountCopy = (string) ($invoice->total ?? 0);

                                $salesperson = $invoice->salesOrder?->user;
                                $waRaw = (string) ($salesperson?->phone ?? '');
                                $wa = preg_replace('/\D+/', '', $waRaw);
                                $proofMessage = 'Hi, I would like to send payment proof for invoice ' . ($invoice->invoice_number ?? '') . ' (' . $amountDisplay . ').';
                                $proofEmail = (string) ($company->company_email ?? '');
                                $proofSubject = 'Payment Proof - ' . ($invoice->invoice_number ?? 'Invoice');
                            @endphp

                            <div class="rounded-xl border border-zinc-200 bg-gradient-to-br from-zinc-50 to-zinc-100/50 p-4">
                                <div class="mb-3 flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-700 text-white">
                                        <flux:icon name="building-library" class="size-4" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-zinc-900">Bank Transfer</p>
                                        <p class="text-xs text-zinc-500">Transfer & send proof</p>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="rounded-lg border border-zinc-200 bg-white p-3">
                                        <p class="text-xs font-medium text-zinc-500">Bank</p>
                                        <p class="font-semibold text-zinc-900">{{ $bankName }}</p>
                                        <p class="text-sm text-zinc-600">{{ $bankAccountName }}</p>
                                    </div>

                                    <div class="flex flex-col gap-2 rounded-lg border border-zinc-200 bg-white p-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-zinc-500">Account number</p>
                                            <p class="truncate font-mono text-lg font-bold text-zinc-900">{{ $bankAccountNumberDisplay }}</p>
                                        </div>
                                        <button
                                            type="button"
                                            x-data="{ copied: false }"
                                            x-on:click="navigator.clipboard.writeText('{{ $bankAccountNumberCopy }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-zinc-300 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-700 transition-colors hover:bg-zinc-100 sm:w-auto"
                                        >
                                            <flux:icon x-show="!copied" name="clipboard" class="size-4" />
                                            <flux:icon x-show="copied" x-cloak name="check" class="size-4 text-emerald-600" />
                                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                                        </button>
                                    </div>

                                    <div class="flex flex-col gap-2 rounded-lg border border-zinc-200 bg-white p-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-zinc-500">Amount to transfer</p>
                                            <p class="truncate text-lg font-bold text-zinc-900">{{ $amountDisplay }}</p>
                                        </div>
                                        <button
                                            type="button"
                                            x-data="{ copied: false }"
                                            x-on:click="navigator.clipboard.writeText('{{ $amountCopy }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-zinc-300 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-700 transition-colors hover:bg-zinc-100 sm:w-auto"
                                        >
                                            <flux:icon x-show="!copied" name="clipboard" class="size-4" />
                                            <flux:icon x-show="copied" x-cloak name="check" class="size-4 text-emerald-600" />
                                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                                        </button>
                                    </div>

                                    <p class="text-xs text-zinc-500">Include invoice number <span class="font-semibold">{{ $invoice->invoice_number }}</span> in your transfer note.</p>

                                    <a
                                        href="{{ !empty($wa) ? 'https://wa.me/' . $wa . '?text=' . urlencode($proofMessage) : (!empty($proofEmail) ? 'mailto:' . $proofEmail . '?subject=' . urlencode($proofSubject) . '&body=' . urlencode($proofMessage) : '#') }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition-colors hover:bg-emerald-700"
                                    >
                                        <flux:icon name="paper-airplane" class="mr-2 size-4" />
                                        Send Payment Proof
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 border-t border-zinc-100 pt-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Salesperson</p>
                        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            @php
                                $salesperson = $invoice->salesOrder?->user;
                                $initials = (string) \Illuminate\Support\Str::of($salesperson?->name ?? '')
                                    ->trim()
                                    ->explode(' ')
                                    ->take(2)
                                    ->map(fn ($word) => \Illuminate\Support\Str::substr((string) $word, 0, 1))
                                    ->implode('');
                                $initials = strtoupper($initials);
                            @endphp
                            <div class="flex min-w-0 items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-xs font-semibold text-zinc-700">
                                    {{ $initials ?: '—' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-zinc-900">{{ $salesperson?->name ?? '—' }}</p>
                                    <p class="text-xs text-zinc-500">{{ $salesperson?->email ?? '' }}</p>
                                </div>
                            </div>
                            @php
                                $waRaw = (string) ($salesperson?->phone ?? '');
                                $wa = preg_replace('/\D+/', '', $waRaw);
                            @endphp
                            @if(!empty($wa))
                                <a
                                    href="https://wa.me/{{ $wa }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg border border-emerald-200 bg-emerald-500/10 px-3 py-1.5 text-xs font-medium text-emerald-700 transition-colors hover:bg-emerald-500/20"
                                >
                                    <flux:icon name="chat-bubble-left-right" class="size-4" />
                                    WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-100 bg-white p-6 text-sm text-zinc-600 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-400">Need help?</p>
                    <p class="mt-3 text-sm text-zinc-600">Contact {{ $company->company_email }} or call {{ $company->company_phone ?? '-' }} for assistance.</p>
                </div>
            </div>
        </div>
    @endif
</div>
