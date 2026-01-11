<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $order->status === 'purchase_order' ? 'Purchase Order' : 'Request for Quotation' }} {{ $order->reference }}</title>
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
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-sent { background: #dbeafe; color: #1d4ed8; }
        .status-purchase_order { background: #d1fae5; color: #059669; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #999; font-size: 10px; }
        .shipping-info { margin-top: 30px; padding: 15px; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #0ea5e9; }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <table style="margin-bottom: 40px;">
            <tr>
                <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                    <div class="company-name">{{ $company['name'] }}</div>
                    <div class="company-details">
                        @if($company['address']){{ $company['address'] }}<br>@endif
                        @if($company['phone'])Phone: {{ $company['phone'] }}<br>@endif
                        @if($company['email'])Email: {{ $company['email'] }}<br>@endif
                        @if($company['tax_id'])Tax ID: {{ $company['tax_id'] }}@endif
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right; border: none; padding: 0;">
                    <div class="doc-title">
                        {{ $order->status === 'purchase_order' ? 'PURCHASE ORDER' : 'REQUEST FOR QUOTATION' }}
                    </div>
                    <div class="doc-number"># {{ $order->reference }}</div>
                    <div class="doc-meta">
                        <p><strong>Date:</strong> {{ $order->order_date?->format('M d, Y') ?? $order->created_at->format('M d, Y') }}</p>
                        @if($order->expected_date)
                        <p><strong>Expected Date:</strong> {{ $order->expected_date->format('M d, Y') }}</p>
                        @endif
                        <p>
                            <span class="status-badge status-{{ $order->status }}">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </p>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Supplier --}}
        <div class="supplier-section">
            <div class="section-title">Supplier</div>
            <div class="supplier-name">{{ $order->supplier->name }}</div>
            <div class="supplier-details">
                @if($order->supplier->email){{ $order->supplier->email }}<br>@endif
                @if($order->supplier->phone){{ $order->supplier->phone }}<br>@endif
                @if($order->supplier->address){{ $order->supplier->address }}@endif
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
                @foreach($order->items as $item)
                <tr>
                    <td>
                        <div class="product-name">{{ $item->product->name ?? $item->description }}</div>
                        @if($item->product?->sku)
                            <div class="product-sku">SKU: {{ $item->product->sku }}</div>
                        @endif
                    </td>
                    <td class="right">{{ number_format($item->quantity) }}</td>
                    <td class="right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals">
            <div class="totals-row">
                <span>Subtotal</span>
                <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($order->tax > 0)
            <div class="totals-row">
                <span>Tax</span>
                <span>Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($order->discount > 0)
            <div class="totals-row">
                <span>Discount</span>
                <span>-Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="totals-row total">
                <span>Total</span>
                <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Shipping Info --}}
        @if($order->shipping_address)
        <div class="shipping-info">
            <strong>Shipping Address:</strong><br>
            {{ $order->shipping_address }}
        </div>
        @endif

        {{-- Terms --}}
        @if($order->terms)
        <div style="margin-top: 30px;">
            <div class="section-title">Terms & Conditions</div>
            <p style="color: #666; white-space: pre-line;">{{ $order->terms }}</p>
        </div>
        @endif

        {{-- Notes --}}
        @if($order->notes)
        <div style="margin-top: 20px;">
            <div class="section-title">Notes</div>
            <p style="color: #666;">{{ $order->notes }}</p>
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <p>{{ $company['name'] }} @if($company['website'])• {{ $company['website'] }}@endif</p>
        </div>
    </div>
</body>
</html>
