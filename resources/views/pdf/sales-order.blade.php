<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $order->status === 'draft' || $order->status === 'confirmed' ? 'Quotation' : 'Sales Order' }} {{ $order->order_number }}</title>
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
        .customer-section { margin-bottom: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 8px; }
        .customer-name { font-size: 16px; font-weight: bold; color: #111; margin-bottom: 4px; }
        .customer-details { color: #666; }
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
        .status-confirmed { background: #dbeafe; color: #1d4ed8; }
        .status-processing { background: #fef3c7; color: #d97706; }
        .status-delivered { background: #d1fae5; color: #059669; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #999; font-size: 10px; }
        .validity { margin-top: 30px; padding: 15px; background: #fffbeb; border-radius: 8px; border-left: 4px solid #f59e0b; }
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
                        {{ $order->status === 'draft' || $order->status === 'confirmed' ? 'QUOTATION' : 'SALES ORDER' }}
                    </div>
                    <div class="doc-number"># {{ $order->order_number }}</div>
                    <div class="doc-meta">
                        <p><strong>Date:</strong> {{ $order->order_date->format('M d, Y') }}</p>
                        @if($order->expected_delivery_date)
                        <p><strong>Valid Until:</strong> {{ $order->expected_delivery_date->format('M d, Y') }}</p>
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

        {{-- Customer --}}
        <div class="customer-section">
            <div class="section-title">Customer</div>
            <div class="customer-name">{{ $order->customer->name }}</div>
            <div class="customer-details">
                @if($order->customer->email){{ $order->customer->email }}<br>@endif
                @if($order->customer->phone){{ $order->customer->phone }}<br>@endif
                @if($order->customer->address){{ $order->customer->address }}@endif
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

        {{-- Validity Notice --}}
        @if($order->status === 'draft' || $order->status === 'confirmed')
        <div class="validity">
            <strong>Quotation Validity:</strong> This quotation is valid for 30 days from the date of issue.
        </div>
        @endif

        {{-- Terms --}}
        @if($order->terms)
        <div style="margin-top: 30px;">
            <div class="section-title">Terms & Conditions</div>
            <p style="color: #666; white-space: pre-line;">{{ $order->terms }}</p>
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>{{ $company['name'] }} @if($company['website'])• {{ $company['website'] }}@endif</p>
        </div>
    </div>
</body>
</html>
