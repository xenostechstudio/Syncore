<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Note {{ $delivery->delivery_number }}</title>
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
        .info-grid { display: table; width: 100%; margin-bottom: 30px; }
        .info-box { display: table-cell; width: 50%; padding: 20px; background: #f9f9f9; vertical-align: top; }
        .info-box:first-child { border-radius: 8px 0 0 8px; }
        .info-box:last-child { border-radius: 0 8px 8px 0; border-left: 1px solid #eee; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 8px; }
        .customer-name { font-size: 16px; font-weight: bold; color: #111; margin-bottom: 4px; }
        .customer-details { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f5f5f5; padding: 12px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #666; border-bottom: 2px solid #e5e5e5; }
        th.right, td.right { text-align: right; }
        th.center, td.center { text-align: center; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .product-name { font-weight: 500; color: #111; }
        .product-sku { font-size: 11px; color: #666; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-ready { background: #dbeafe; color: #1d4ed8; }
        .status-shipped { background: #fef3c7; color: #d97706; }
        .status-delivered { background: #d1fae5; color: #059669; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #999; font-size: 10px; }
        .signature-section { margin-top: 50px; display: table; width: 100%; }
        .signature-box { display: table-cell; width: 45%; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .signature-box:first-child { margin-right: 10%; }
        .signature-line { border-bottom: 1px solid #333; height: 60px; margin-bottom: 8px; }
        .signature-label { font-size: 10px; color: #666; text-transform: uppercase; }
        .reference-box { margin-top: 20px; padding: 15px; background: #f0fdf4; border-radius: 8px; border-left: 4px solid #22c55e; }
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
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right; border: none; padding: 0;">
                    <div class="doc-title">DELIVERY NOTE</div>
                    <div class="doc-number"># {{ $delivery->delivery_number }}</div>
                    <div class="doc-meta">
                        <p><strong>Date:</strong> {{ $delivery->delivery_date?->format('M d, Y') ?? $delivery->created_at->format('M d, Y') }}</p>
                        @if($delivery->scheduled_date)
                        <p><strong>Scheduled:</strong> {{ $delivery->scheduled_date->format('M d, Y') }}</p>
                        @endif
                        <p>
                            <span class="status-badge status-{{ $delivery->status }}">
                                {{ ucfirst($delivery->status) }}
                            </span>
                        </p>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Customer & Shipping Info --}}
        <table style="margin-bottom: 30px;">
            <tr>
                <td style="width: 50%; vertical-align: top; border: none; padding: 0 10px 0 0;">
                    <div style="padding: 20px; background: #f9f9f9; border-radius: 8px; height: 100%;">
                        <div class="section-title">Customer</div>
                        <div class="customer-name">{{ $delivery->salesOrder->customer->name }}</div>
                        <div class="customer-details">
                            @if($delivery->salesOrder->customer->email){{ $delivery->salesOrder->customer->email }}<br>@endif
                            @if($delivery->salesOrder->customer->phone){{ $delivery->salesOrder->customer->phone }}@endif
                        </div>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; border: none; padding: 0 0 0 10px;">
                    <div style="padding: 20px; background: #f9f9f9; border-radius: 8px; height: 100%;">
                        <div class="section-title">Shipping Address</div>
                        <div class="customer-details">
                            {{ $delivery->shipping_address ?? $delivery->salesOrder->customer->address ?? 'N/A' }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Reference Info --}}
        <div class="reference-box">
            <strong>Sales Order:</strong> {{ $delivery->salesOrder->order_number }}
            @if($delivery->warehouse)
            <span style="margin-left: 30px;"><strong>Warehouse:</strong> {{ $delivery->warehouse->name }}</span>
            @endif
            @if($delivery->tracking_number)
            <span style="margin-left: 30px;"><strong>Tracking:</strong> {{ $delivery->tracking_number }}</span>
            @endif
        </div>

        {{-- Items Table --}}
        <table style="margin-top: 30px;">
            <thead>
                <tr>
                    <th style="width: 10%;">#</th>
                    <th style="width: 50%;">Product</th>
                    <th class="center" style="width: 20%;">Ordered Qty</th>
                    <th class="center" style="width: 20%;">Delivered Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($delivery->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div class="product-name">{{ $item->product->name ?? $item->description }}</div>
                        @if($item->product?->sku)
                            <div class="product-sku">SKU: {{ $item->product->sku }}</div>
                        @endif
                    </td>
                    <td class="center">{{ number_format($item->ordered_quantity ?? $item->quantity) }}</td>
                    <td class="center">{{ number_format($item->delivered_quantity ?? $item->quantity) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Notes --}}
        @if($delivery->notes)
        <div style="margin-top: 30px;">
            <div class="section-title">Notes</div>
            <p style="color: #666;">{{ $delivery->notes }}</p>
        </div>
        @endif

        {{-- Signature Section --}}
        <table class="signature-section">
            <tr>
                <td style="width: 45%; vertical-align: top; border: none; padding: 0;">
                    <div style="padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                        <div class="section-title">Delivered By</div>
                        <div class="signature-line"></div>
                        <p style="font-size: 11px; color: #666;">Name: _______________________</p>
                        <p style="font-size: 11px; color: #666; margin-top: 8px;">Date: _______________________</p>
                    </div>
                </td>
                <td style="width: 10%; border: none;"></td>
                <td style="width: 45%; vertical-align: top; border: none; padding: 0;">
                    <div style="padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                        <div class="section-title">Received By</div>
                        <div class="signature-line"></div>
                        <p style="font-size: 11px; color: #666;">Name: _______________________</p>
                        <p style="font-size: 11px; color: #666; margin-top: 8px;">Date: _______________________</p>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Footer --}}
        <div class="footer">
            <p>{{ $company['name'] }} @if($company['website'])• {{ $company['website'] }}@endif</p>
        </div>
    </div>
</body>
</html>
