<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Internal Transfer {{ $transfer->transfer_number }}</title>
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
        .warehouse-section { margin-bottom: 30px; }
        .warehouse-box { display: inline-block; width: 45%; padding: 20px; background: #f9f9f9; border-radius: 8px; vertical-align: top; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 8px; }
        .warehouse-name { font-size: 16px; font-weight: bold; color: #111; margin-bottom: 4px; }
        .warehouse-details { color: #666; }
        .arrow { display: inline-block; width: 8%; text-align: center; font-size: 24px; color: #999; vertical-align: middle; padding-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f5f5f5; padding: 12px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #666; border-bottom: 2px solid #e5e5e5; }
        th.right, td.right { text-align: right; }
        th.center, td.center { text-align: center; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .product-name { font-weight: 500; color: #111; }
        .product-sku { font-size: 11px; color: #666; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-in_transit { background: #dbeafe; color: #1d4ed8; }
        .status-completed { background: #d1fae5; color: #059669; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        .signature-section { margin-top: 60px; }
        .signature-box { display: inline-block; width: 200px; text-align: center; margin-right: 40px; }
        .signature-line { border-top: 1px solid #333; margin-top: 60px; padding-top: 8px; }
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
                        @if($company['phone'] ?? false)Phone: {{ $company['phone'] }}@endif
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right; border: none; padding: 0;">
                    <div class="doc-title">INTERNAL TRANSFER</div>
                    <div class="doc-number"># {{ $transfer->transfer_number }}</div>
                    <div class="doc-meta">
                        <p><strong>Date:</strong> {{ $transfer->transfer_date->format('M d, Y') }}</p>
                        @if($transfer->expected_arrival_date)
                        <p><strong>Expected Arrival:</strong> {{ $transfer->expected_arrival_date->format('M d, Y') }}</p>
                        @endif
                        <p>
                            <span class="status-badge status-{{ $transfer->status }}">
                                {{ ucfirst(str_replace('_', ' ', $transfer->status)) }}
                            </span>
                        </p>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Warehouses --}}
        <div class="warehouse-section">
            <div class="warehouse-box">
                <div class="section-title">From Warehouse</div>
                <div class="warehouse-name">{{ $transfer->sourceWarehouse?->name ?? '-' }}</div>
                <div class="warehouse-details">
                    @if($transfer->sourceWarehouse?->location){{ $transfer->sourceWarehouse->location }}@endif
                </div>
            </div>
            <div class="arrow">→</div>
            <div class="warehouse-box">
                <div class="section-title">To Warehouse</div>
                <div class="warehouse-name">{{ $transfer->destinationWarehouse?->name ?? '-' }}</div>
                <div class="warehouse-details">
                    @if($transfer->destinationWarehouse?->location){{ $transfer->destinationWarehouse->location }}@endif
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Product</th>
                    <th class="center" style="width: 25%;">Quantity to Transfer</th>
                    <th class="center" style="width: 25%;">Received</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transfer->items as $item)
                <tr>
                    <td>
                        <div class="product-name">{{ $item->product?->name ?? '-' }}</div>
                        @if($item->product?->sku)
                            <div class="product-sku">SKU: {{ $item->product->sku }}</div>
                        @endif
                    </td>
                    <td class="center">{{ number_format($item->quantity) }}</td>
                    <td class="center">{{ number_format($item->received_quantity ?? 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Notes --}}
        @if($transfer->notes)
        <div style="margin-top: 30px;">
            <div class="section-title">Notes</div>
            <p style="color: #666; white-space: pre-line;">{{ $transfer->notes }}</p>
        </div>
        @endif

        {{-- Signatures --}}
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Prepared By</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Source Warehouse</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Destination Warehouse</div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>Generated on {{ now()->format('M d, Y H:i') }}</p>
            <p>{{ $company['name'] ?? config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
