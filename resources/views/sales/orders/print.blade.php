<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <style>
            @page {
                size: A4;
                margin: 16mm;
            }

            @media print {
                .no-print {
                    display: none !important;
                }

                body {
                    background: #ffffff !important;
                }
            }
        </style>
    </head>
    <body class="bg-white text-zinc-900">
        @php
            $company = \App\Models\Settings\CompanyProfile::getProfile();
        @endphp

        <div class="no-print mx-auto max-w-4xl px-6 pt-6">
            <div class="flex items-center justify-end gap-2">
                <a
                    href="{{ route('sales.orders.edit', $order->id) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50"
                >
                    <flux:icon name="arrow-left" class="size-4" />
                    Back
                </a>
                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800"
                >
                    <flux:icon name="printer" class="size-4" />
                    Print
                </button>
            </div>
        </div>

        <div class="mx-auto max-w-4xl px-6 pb-10 pt-6">
            <div class="flex items-start justify-between gap-6">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ $company->company_name ?? 'Syncore' }}</div>
                    <div class="mt-1 text-sm text-zinc-600">
                        @if(!empty($company->company_address))
                            <div>{{ $company->company_address }}</div>
                        @endif
                        @if(!empty($company->company_city) || !empty($company->company_country))
                            <div>{{ trim(($company->company_city ?? '') . ' ' . ($company->company_country ?? '')) }}</div>
                        @endif
                        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                            @if(!empty($company->company_email))
                                <span>{{ $company->company_email }}</span>
                            @endif
                            @if(!empty($company->company_phone))
                                <span>{{ $company->company_phone }}</span>
                            @endif
                            @if(!empty($company->company_website))
                                <span>{{ $company->company_website }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-2xl font-semibold tracking-tight">Sales Order</div>
                    <div class="mt-1 text-sm text-zinc-600">{{ $order->order_number }}</div>
                    <div class="mt-2 inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700">
                        Status: {{ $order->status }}
                    </div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-6">
                <div class="rounded-xl border border-zinc-200 p-4">
                    <div class="text-sm font-medium text-zinc-900">Customer</div>
                    <div class="mt-2 space-y-1 text-sm text-zinc-700">
                        <div class="font-medium">{{ $order->customer->name ?? '-' }}</div>
                        @if(!empty($order->customer?->email))
                            <div class="text-zinc-600">{{ $order->customer->email }}</div>
                        @endif
                        @if(!empty($order->customer?->phone))
                            <div class="text-zinc-600">{{ $order->customer->phone }}</div>
                        @endif
                        @if(!empty($order->customer?->address))
                            <div class="text-zinc-600">{{ $order->customer->address }}</div>
                        @endif
                    </div>
                </div>

                <div class="rounded-xl border border-zinc-200 p-4">
                    <div class="text-sm font-medium text-zinc-900">Order Details</div>
                    <dl class="mt-2 space-y-1 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-zinc-600">Order Date</dt>
                            <dd class="font-medium text-zinc-900">{{ $order->order_date?->format('Y-m-d') ?? '-' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-zinc-600">Expected Delivery</dt>
                            <dd class="font-medium text-zinc-900">{{ $order->expected_delivery_date?->format('Y-m-d') ?? '-' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-zinc-600">Salesperson</dt>
                            <dd class="font-medium text-zinc-900">{{ $order->user?->name ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-xl border border-zinc-200">
                <table class="min-w-full divide-y divide-zinc-200">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Product</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500">Qty</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500">Unit Price</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500">Discount</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500">Tax</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 bg-white">
                        @forelse($order->items as $index => $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-zinc-600">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-900">
                                    <div class="font-medium">{{ $item->product?->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-zinc-700">{{ number_format((float) $item->quantity, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-zinc-700">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-zinc-700">Rp {{ number_format((float) $item->discount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-zinc-700">{{ $item->tax?->formatted_rate ?? '-' }}</td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-zinc-900">Rp {{ number_format((float) $item->total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-zinc-500">No items</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex items-start justify-end">
                <div class="w-full max-w-sm space-y-2 rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-zinc-600">Subtotal</span>
                        <span class="font-medium text-zinc-900">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-zinc-600">Tax</span>
                        <span class="font-medium text-zinc-900">Rp {{ number_format((float) $order->tax, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-zinc-600">Discount</span>
                        <span class="font-medium text-zinc-900">Rp {{ number_format((float) $order->discount, 0, ',', '.') }}</span>
                    </div>
                    <div class="my-2 h-px bg-zinc-200"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-zinc-900">Total</span>
                        <span class="text-sm font-semibold text-zinc-900">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            @if(!empty($order->notes) || !empty($order->terms))
                <div class="mt-6 grid grid-cols-2 gap-6">
                    @if(!empty($order->notes))
                        <div class="rounded-xl border border-zinc-200 p-4">
                            <div class="text-sm font-medium text-zinc-900">Notes</div>
                            <div class="mt-2 whitespace-pre-line text-sm text-zinc-700">{{ $order->notes }}</div>
                        </div>
                    @endif

                    @if(!empty($order->terms))
                        <div class="rounded-xl border border-zinc-200 p-4">
                            <div class="text-sm font-medium text-zinc-900">Terms</div>
                            <div class="mt-2 whitespace-pre-line text-sm text-zinc-700">{{ $order->terms }}</div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="mt-10 border-t border-zinc-200 pt-4 text-xs text-zinc-500">
                Generated at {{ now()->format('Y-m-d H:i') }}
            </div>
        </div>

        @fluxScripts
    </body>
</html>
