<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $rfq->status === 'rfq' ? 'Request for Quotation' : 'Purchase Order' }} {{ $rfq->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .container { padding: 40px; }
        .company-name { font-size: 24px; font-weight: bold; color: #111; margin-bottom: 8px; }
        .company-details { color: #666; font-size: 11px; }
        .doc-title { font-size: 28px; font-weight: bold; color: #111; margin-bottom: 8px; }
        .doc-number { font-size: 14px; color: #666; }
        .doc-meta { margin-top: 16px; }
        .doc-meta p { margin-bottom: 4px; }
        .doc-meta strong { color: #111; }
        .supplier-section { margin-bottom: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 8px; }
        .supplier-name { font-size: 16px; font-weight: bold; color: #111; margin-bottom: 4px; }
        .supplier-details { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f5f5f5; padding: 12px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #666; border-bottom: 2px solid #e5e5e5; }
        th.right, td.right { text-align: right; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .product-name { font-weight: 500; color: #111; }
        .product-sku { font-size: 11px; color: #666; }
        .totals { width: 300px; margin-left: auto; }
        .totals-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .totals-row.total { border-top: 2px solid #111; border-bottom: none; font-size: 16px; font-weight: bold; padding-top: 12px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .status-rfq { background: #dbeafe; color: #1d4ed8; }
        .status-sent { background: #fef3c7; color: #d97706; }
        .status-purchase_order { background: #d1fae5; color: #059669; }
        .status-cancelled { background: #f3f4f6; color: #6b7280; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #999; font-size: 10px; }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <table style="margin-bottom: 40px;">
            <tr>
                <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                    <div class="company-name">{{ $company['name'] ?? config('app.name') }}</div>
                    <div class="company-details">
                        @if($company['address'] ?? false){{ $company['address'] }}<br>@endif
                        @if($company['phone'] ?? false)Phone: {{ $company['phone'] }}<br>@endif
                        @if($company['email'] ?? false)Email: {{ $company['email'] }}<br>@endif
                        @if($company['tax_id'] ?? false)Tax ID: {{ $company['tax_id'] }}@endif
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right; border: none; padding: 0;">
                    <div class="doc-title">
                        {{ $rfq->status === 'rfq' || $rfq->status === 'sent' ? 'REQUEST FOR QUOTATION' : 'PURCHASE ORDER' }}
                    </div>
                    <div class="doc-number"># {{ $rfq->reference }}</div>
                    <div class="doc-meta">
                        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($rfq->order_date)->format('M d, Y') }}</p>
                        @if($rfq->expected_arrival)
                        <p><strong>Expected Arrival:</strong> {{ \Carbon\Carbon::parse($rfq->expected_arrival)->format('M d, Y') }}</p>
                        @endif
                        <p>
                            <span class="status-badge status-{{ $rfq->status }}">
                                {{ ucfirst(str_replace('_', ' ', $rfq->status)) }}
                            </span>
                        </p>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Supplier --}}
        <div class="supplier-section">
            <div class="section-title">Supplier</div>
            <div class="supplier-name">{{ $rfq->supplier?->name ?? $rfq->supplier_name ?? '-' }}</div>
            <div class="supplier-details">
                @if($rfq->supplier?->email){{ $rfq->supplier->email }}<br>@endif
                @if($rfq->supplier?->phone){{ $rfq->supplier->phone }}<br>@endif
                @if($rfq->supplier?->address){{ $rfq->supplier->address }}@endif
            </div>
        </div>

        {{-- Items Table --}}
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Product</th>
                    <th class="right" style="width: 15%;">Qty</th>
                    <th class="right" style="width: 20%;">Unit Price</th>
                    <th class="right" style="width: 25%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rfq->items ?? [] as $item)
                <tr>
                    <td>
                        <div class="product-name">{{ $item->product?->name ?? $item->description ?? '-' }}</div>
                        @if($item->product?->sku)
                            <div class="product-sku">SKU: {{ $item->product->sku }}</div>
                        @endif
                    </td>
                    <td class="right">{{ number_format($item->quantity) }}</td>
                    <td class="right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #999;">No items</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals">
            <div class="totals-row">
                <span>Subtotal</span>
                <span>Rp {{ number_format($rfq->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($rfq->tax > 0)
            <div class="totals-row">
                <span>Tax</span>
                <span>Rp {{ number_format($rfq->tax, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="totals-row total">
                <span>Total</span>
                <span>Rp {{ number_format($rfq->total, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Notes --}}
        @if($rfq->notes)
        <div style="margin-top: 30px;">
            <div class="section-title">Notes</div>
            <p style="color: #666; white-space: pre-line;">{{ $rfq->notes }}</p>
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>{{ $company['name'] ?? config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
