<div class="space-y-6">
    @if($expired)
        <div class="rounded-2xl border border-red-100 bg-white/90 p-8 text-center shadow-sm">
            <flux:icon name="exclamation-triangle" class="mx-auto mb-4 size-10 text-red-500" />
            <h1 class="text-2xl font-semibold text-red-700">Link Expired</h1>
            <p class="mt-2 text-sm text-zinc-500">Please request a new quotation link from {{ $company->company_email ?? 'our team' }}.</p>
        </div>
    @elseif(! $order)
        <div class="rounded-2xl border border-zinc-100 bg-white/90 p-8 text-center shadow-sm">
            <flux:icon name="question-mark-circle" class="mx-auto mb-4 size-10 text-zinc-400" />
            <h1 class="text-2xl font-semibold text-zinc-700">Order not found</h1>
            <p class="mt-2 text-sm text-zinc-500">The link may be incorrect or revoked. Please contact {{ $company->company_email ?? 'support' }}.</p>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-4">
            {{-- Left Sidebar (25%) --}}
            <div class="space-y-4 lg:col-span-1">
                {{-- Confirm Order & Salesperson Card --}}
                <div class="overflow-hidden rounded-xl border border-zinc-100 bg-white shadow-sm">
                    {{-- Order Summary --}}
                    <div class="bg-zinc-900 p-4 text-white">
                        <p class="text-xs font-medium uppercase tracking-wider text-white/60">{{ $order->status === 'draft' || $order->status === 'confirmed' ? 'Quotation' : 'Sales Order' }} · {{ $order->order_number }}</p>
                        <p class="mt-2 text-xl font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-white/60">
                            @if($order->expected_delivery_date)
                                Expected: {{ $order->expected_delivery_date->format('M d, Y') }}
                            @else
                                Date: {{ $order->order_date->format('M d, Y') }}
                            @endif
                        </p>
                    </div>

                    <div class="p-5">
                        {{-- Confirm Order Button (only for quotations) --}}
                        @if(in_array($order->status, ['draft', 'confirmed']))
                        <div class="mt-4">
                            <p class="text-xs text-zinc-500">Ready to proceed? Confirm this quotation.</p>
                            <button 
                                type="button" 
                                wire:click="confirmOrder"
                                wire:loading.attr="disabled"
                                class="mt-3 inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700 disabled:opacity-50"
                            >
                                <flux:icon name="check-circle" class="mr-2 size-4" wire:loading.remove wire:target="confirmOrder" />
                                <flux:icon name="arrow-path" class="mr-2 size-4 animate-spin" wire:loading wire:target="confirmOrder" />
                                <span>Confirm Order</span>
                            </button>
                            {{-- Actions --}}
                            <div class="mt-3 flex gap-2">
                                <button type="button" onclick="window.print()" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-50">
                                    <flux:icon name="printer" class="size-4" />
                                    Print
                                </button>
                                <button type="button" onclick="window.print()" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-50">
                                    <flux:icon name="arrow-down-tray" class="size-4" />
                                    Download
                                </button>
                            </div>
                        </div>
                    @endif

                    @if($order->status === 'processing')
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                            <div class="flex items-center gap-2">
                                <flux:icon name="check-circle" class="size-4 text-emerald-600" />
                                <p class="text-xs font-medium text-emerald-700">Order Confirmed</p>
                            </div>
                        </div>
                        {{-- Actions --}}
                        <div class="mt-3 flex gap-2">
                            <button type="button" onclick="window.print()" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-50">
                                <flux:icon name="printer" class="size-4" />
                                Print
                            </button>
                            <button type="button" onclick="window.print()" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-50">
                                <flux:icon name="arrow-down-tray" class="size-4" />
                                Download
                            </button>
                        </div>
                    @endif

                    @if($statusMessage)
                        <div class="mt-3 rounded-lg p-2.5 {{ $statusIsError ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">
                            <p class="text-xs">{{ $statusMessage }}</p>
                        </div>
                    @endif

                    {{-- Salesperson --}}
                    @php
                        $salesperson = $order->user;
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

            {{-- Right Content: Customer Info & Order Items (75%) --}}
            <div class="overflow-hidden rounded-xl border border-zinc-100 bg-white shadow-sm lg:col-span-3">
                {{-- Heading --}}
                <div class="border-b border-zinc-100 px-5 py-4">
                    <div class="flex items-center justify-between">
                        <h1 class="text-lg font-semibold text-zinc-900">{{ $order->status === 'draft' || $order->status === 'confirmed' ? 'Quotation' : 'Sales Order' }} - {{ $order->order_number }}</h1>
                        <p class="text-sm text-zinc-500">{{ ($order->order_date ?? $order->created_at)->format('M d, Y · H:i') }}</p>
                    </div>
                </div>
                {{-- Customer & Company Header --}}
                <div class="grid gap-6 border-b border-zinc-100 p-5 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-400">Customer</p>
                        <p class="mt-2 text-sm font-semibold text-zinc-900">{{ $order->customer->name ?? 'Customer' }}</p>
                        <p class="text-sm text-zinc-500">{{ $order->customer->email ?? '-' }}</p>
                        <p class="text-sm text-zinc-500">{{ $order->customer->address ?? '' }}</p>
                    </div>
                    <div class="sm:text-right">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-400">From</p>
                        <p class="mt-2 text-sm font-semibold text-zinc-900">{{ $company->company_name }}</p>
                        <p class="text-sm text-zinc-500">{{ $company->company_email }}</p>
                        <p class="text-sm text-zinc-500">{{ $company->company_address }}</p>
                    </div>
                </div>

                {{-- Order Items Table --}}
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
                            @forelse($order->items as $item)
                                @php
                                    $lineTax = 0;
                                    if ($item->tax) {
                                        if ($item->tax->type === 'percentage') {
                                            $lineTax = $item->total * ($item->tax->rate / 100);
                                        } else {
                                            $lineTax = $item->tax->rate;
                                        }
                                    }
                                @endphp
                                <tr class="transition-colors hover:bg-zinc-50/50">
                                    <td class="px-5 py-3">
                                        <span class="text-sm font-medium text-zinc-900">{{ $item->product->name ?? '-' }}</span>
                                        @if($item->product->sku ?? null)
                                            <p class="text-xs text-zinc-400">{{ $item->product->sku }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-zinc-900">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-zinc-900">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-zinc-500">Rp {{ number_format($lineTax, 0, ',', '.') }}</td>
                                    <td class="px-5 py-3 text-right text-sm font-medium text-zinc-900">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-sm text-zinc-400">No items on this order.</td>
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
                                            <span class="text-zinc-900">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-zinc-500">Taxes</span>
                                            <span class="text-zinc-900">Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                                        </div>
                                        @if($order->discount > 0)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="text-zinc-500">Discount</span>
                                                <span class="text-red-600">- Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
                                            </div>
                                        @endif
                                        <div class="flex items-center justify-between border-t border-zinc-200 pt-2">
                                            <span class="font-medium text-zinc-900">Total</span>
                                            <span class="text-lg font-semibold text-zinc-900">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Notes & Terms --}}
                @if($order->notes || $order->terms)
                    <div class="space-y-3 border-t border-zinc-100 p-5">
                        @if($order->notes)
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-zinc-400">Notes</p>
                                <p class="mt-1 text-sm text-zinc-600">{{ $order->notes }}</p>
                            </div>
                        @endif
                        @if($order->terms)
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-zinc-400">Terms & Conditions</p>
                                <p class="mt-1 text-sm text-zinc-600">{{ $order->terms }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Need Help Section --}}
        <div class="rounded-xl border border-zinc-100 bg-white p-5 text-center shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-zinc-400">Need help?</p>
            <p class="mt-2 text-sm text-zinc-500">Contact {{ $company->company_email }} or call {{ $company->company_phone ?? '-' }} for assistance.</p>
        </div>
    @endif
</div>
