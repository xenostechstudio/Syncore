<div class="space-y-6">
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
        {{-- Payment Success Message --}}
        @if($paymentSuccess)
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <flux:icon name="check-circle" class="size-6 text-emerald-600" />
                    <div>
                        <p class="font-medium text-emerald-800">Payment Successful!</p>
                        <p class="text-sm text-emerald-600">Thank you for your payment. Your transaction has been completed.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Payment Failed Message --}}
        @if($paymentFailed)
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <flux:icon name="x-circle" class="size-6 text-red-600" />
                    <div>
                        <p class="font-medium text-red-800">Payment Failed</p>
                        <p class="text-sm text-red-600">Your payment could not be processed. Please try again or contact support.</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-4">
            {{-- Left Sidebar (25%) --}}
            <div class="space-y-4 lg:col-span-1">
                {{-- Invoice Summary & Payment Card --}}
                <div class="overflow-hidden rounded-xl border border-zinc-100 bg-white shadow-sm">
                    {{-- Invoice Summary --}}
                    <div class="bg-zinc-900 p-4 text-white">
                        <p class="text-xs font-medium uppercase tracking-wider text-white/60">Invoice · {{ $invoice->invoice_number }}</p>
                        <p class="mt-2 text-xl font-bold">Rp {{ number_format($invoice->total, 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-white/60">
                            Due {{ optional($invoice->due_date)->format('M d, Y') ?? 'Upon receipt' }}
                        </p>
                    </div>

                    <div class="p-5">
                        {{-- Amount Due --}}
                        @php
                            $amountDue = $invoice->total - ($invoice->paid_amount ?? 0);
                        @endphp
                        @if($amountDue > 0)
                            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                                <p class="text-xs font-medium text-amber-700">Amount Due</p>
                                <p class="text-lg font-bold text-amber-800">Rp {{ number_format($amountDue, 0, ',', '.') }}</p>
                            </div>
                        @else
                            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                                <div class="flex items-center gap-2">
                                    <flux:icon name="check-circle" class="size-4 text-emerald-600" />
                                    <p class="text-sm font-medium text-emerald-700">Paid in Full</p>
                                </div>
                            </div>
                        @endif

                    {{-- Payment Method Toggle --}}
                    @if($amountDue > 0)
                        <div class="mt-4">
                            <div class="rounded-lg border border-zinc-200 bg-zinc-100/80 p-1">
                                <div class="grid grid-cols-2 gap-1">
                                    <button
                                        type="button"
                                        wire:click="selectPaymentMethod('online')"
                                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-semibold transition-all focus:outline-none {{ $selectedPaymentMethod === 'online' ? 'bg-white text-zinc-900 shadow-sm ring-1 ring-zinc-900/5' : 'text-zinc-500 hover:text-zinc-700 hover:bg-white/50' }}"
                                    >
                                        <flux:icon name="credit-card" class="size-3.5" />
                                        Online
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="selectPaymentMethod('manual')"
                                        class="flex items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-semibold transition-all focus:outline-none {{ $selectedPaymentMethod === 'manual' ? 'bg-white text-zinc-900 shadow-sm ring-1 ring-zinc-900/5' : 'text-zinc-500 hover:text-zinc-700 hover:bg-white/50' }}"
                                    >
                                        <flux:icon name="building-library" class="size-3.5" />
                                        Transfer
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3">
                                @if($selectedPaymentMethod === 'online')
                                    <p class="mb-3 text-xs text-zinc-500">Pay securely via Xendit.</p>
                                    @if($paymentLink)
                                        <a href="{{ $paymentLink }}" target="_blank" class="inline-flex w-full items-center justify-center rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-zinc-800">
                                            <flux:icon name="arrow-top-right-on-square" class="mr-2 size-4" />
                                            Pay Now
                                        </a>
                                    @else
                                        <button type="button" wire:click="requestPaymentLink" class="inline-flex w-full items-center justify-center rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-zinc-800">
                                            <flux:icon name="sparkles" class="mr-2 size-4" />
                                            Generate Link
                                        </button>
                                    @endif
                                @else
                                    @php
                                        $bankName = 'Bank Mandiri';
                                        $bankAccountName = 'PT ' . ($company->company_name ?? '');
                                        $bankAccountNumberDisplay = '123 4567 890';
                                        $bankAccountNumberCopy = preg_replace('/\D+/', '', $bankAccountNumberDisplay);
                                        $amountDisplay = 'Rp ' . number_format($invoice->total, 0, ',', '.');

                                        $salesperson = $invoice->salesOrder?->user;
                                        $waRaw = (string) ($salesperson?->phone ?? '');
                                        $wa = preg_replace('/\D+/', '', $waRaw);
                                        $proofMessage = 'Hi, I would like to send payment proof for invoice ' . ($invoice->invoice_number ?? '') . ' (' . $amountDisplay . ').';
                                        $proofEmail = (string) ($company->company_email ?? '');
                                        $proofSubject = 'Payment Proof - ' . ($invoice->invoice_number ?? 'Invoice');
                                    @endphp
                                    <div class="space-y-2">
                                        <div class="rounded-lg border border-zinc-200 bg-white p-2.5">
                                            <p class="text-xs text-zinc-500">{{ $bankName }}</p>
                                            <p class="text-sm font-semibold text-zinc-900">{{ $bankAccountNumberDisplay }}</p>
                                            <p class="text-xs text-zinc-500">{{ $bankAccountName }}</p>
                                        </div>
                                        <a
                                            href="{{ !empty($wa) ? 'https://wa.me/' . $wa . '?text=' . urlencode($proofMessage) : (!empty($proofEmail) ? 'mailto:' . $proofEmail . '?subject=' . urlencode($proofSubject) . '&body=' . urlencode($proofMessage) : '#') }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700"
                                        >
                                            <flux:icon name="paper-airplane" class="mr-2 size-4" />
                                            Send Proof
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($statusMessage)
                        <div class="mt-3 rounded-lg p-2.5 {{ $statusIsError ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">
                            <p class="text-xs">{{ $statusMessage }}</p>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="mt-4 flex gap-2">
                        <button type="button" onclick="window.print()" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-50">
                            <flux:icon name="printer" class="size-4" />
                            Print
                        </button>
                        <button type="button" onclick="window.print()" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-50">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            Download
                        </button>
                    </div>

                    {{-- Salesperson --}}
                    @php
                        $salesperson = $invoice->salesOrder?->user;
                        $initials = (string) \Illuminate\Support\Str::of($salesperson?->name ?? '')
                            ->trim()
                            ->explode(' ')
                            ->take(2)
                            ->map(fn ($word) => \Illuminate\Support\Str::substr((string) $word, 0, 1))
                            ->implode('');
                        $initials = strtoupper($initials);
                        $waRaw = (string) ($salesperson?->phone ?? '');
                        $wa = preg_replace('/\D+/', '', $waRaw);
                    @endphp
                    <div class="mt-4 border-t border-zinc-100 pt-4">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-400">Salesperson</p>
                        <div class="mt-2 flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-sm font-semibold text-zinc-600">
                                {{ $initials ?: '—' }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-zinc-900">{{ $salesperson?->name ?? '—' }}</p>
                                @if(!empty($wa))
                                    <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="text-xs text-emerald-600 hover:text-emerald-700">
                                        Chat via WhatsApp
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            {{-- Right Content: Customer Info & Invoice Items (75%) --}}
            <div class="overflow-hidden rounded-xl border border-zinc-100 bg-white shadow-sm lg:col-span-3">
                {{-- Heading --}}
                <div class="border-b border-zinc-100 px-5 py-4">
                    <div class="flex items-center justify-between">
                        <h1 class="text-lg font-semibold text-zinc-900">Invoice - {{ $invoice->invoice_number }}</h1>
                        <p class="text-sm text-zinc-500">{{ ($invoice->invoice_date ?? $invoice->created_at)->format('M d, Y · H:i') }}</p>
                    </div>
                </div>

                {{-- Customer & Company Header --}}
                <div class="grid gap-6 border-b border-zinc-100 p-5 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-400">Billed To</p>
                        <p class="mt-2 text-sm font-semibold text-zinc-900">{{ $invoice->customer->name ?? 'Customer' }}</p>
                        <p class="text-sm text-zinc-500">{{ $invoice->customer->email ?? '-' }}</p>
                        <p class="text-sm text-zinc-500">{{ $invoice->customer->address ?? '' }}</p>
                    </div>
                    <div class="sm:text-right">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-400">From</p>
                        <p class="mt-2 text-sm font-semibold text-zinc-900">{{ $company->company_name }}</p>
                        <p class="text-sm text-zinc-500">{{ $company->company_email }}</p>
                        <p class="text-sm text-zinc-500">{{ $company->company_address }}</p>
                    </div>
                </div>

                {{-- Invoice Items Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[600px]">
                        <thead>
                            <tr class="border-b border-zinc-100 bg-zinc-50/50">
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Product</th>
                                <th class="w-20 px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Qty</th>
                                <th class="w-28 px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Price</th>
                                <th class="w-24 px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Tax</th>
                                <th class="w-28 px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-50">
                            @forelse($invoice->items as $item)
                                @php
                                    $lineTax = 0;
                                    if (($invoice->subtotal ?? 0) > 0) {
                                        $lineTax = (float) $invoice->tax * ((float) $item->total / (float) $invoice->subtotal);
                                    }
                                @endphp
                                <tr class="transition-colors hover:bg-zinc-50/50">
                                    <td class="px-5 py-3">
                                        <span class="text-sm font-medium text-zinc-900">{{ $item->product->name ?? '-' }}</span>
                                        @if($item->description)
                                            <p class="text-xs text-zinc-400">{{ $item->description }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-zinc-900">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-zinc-900">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-zinc-500">Rp {{ number_format($lineTax, 0, ',', '.') }}</td>
                                    <td class="px-5 py-3 text-right text-sm font-medium text-zinc-900">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-sm text-zinc-400">No items on this invoice.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        {{-- Summary --}}
                        <tfoot>
                            <tr class="border-t border-zinc-100">
                                <td colspan="3" class="px-5 py-2"></td>
                                <td colspan="2" class="px-5 py-2">
                                    <div class="ml-auto w-72 space-y-2">
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
                                            <span class="font-medium text-zinc-900">Amount Due</span>
                                            <span class="text-lg font-bold text-zinc-900">Rp {{ number_format($invoice->total - ($invoice->paid_amount ?? 0), 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Notes --}}
                @if($invoice->notes)
                    <div class="border-t border-zinc-100 p-5">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-400">Notes</p>
                        <p class="mt-1 text-sm text-zinc-600">{{ $invoice->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Payment History --}}
        @if($payments->count() > 0)
            <div class="overflow-hidden rounded-xl border border-zinc-100 bg-white shadow-sm">
                <div class="border-b border-zinc-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-zinc-900">Payment History</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[500px]">
                        <thead>
                            <tr class="border-b border-zinc-100 bg-zinc-50/50">
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Reference</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Method</th>
                                <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-50">
                            @foreach($payments as $payment)
                                <tr class="transition-colors hover:bg-zinc-50/50">
                                    <td class="px-5 py-3 text-sm text-zinc-900">{{ $payment->payment_date->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600">{{ $payment->payment_number }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-700">
                                            {{ $payment->payment_method }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right text-sm font-medium text-emerald-600">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>
