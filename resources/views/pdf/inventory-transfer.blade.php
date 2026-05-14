<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    @php
        $primaryColor = $settings->primary_color ?? "#18181b";
        $accentColor  = $settings->accent_color  ?? "#10b981";
        $dateFormat   = $settings->date_format   ?? "M d, Y";
    @endphp

    <title>Internal Transfer {{ $transfer->transfer_number }}</title>
    <style>

        /* Watermark for draft/cancelled */
        .watermark {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            font-weight: bold;
            color: rgba(0, 0, 0, 0.06);
            text-transform: uppercase;
            z-index: -1;
            white-space: nowrap;
        }
        .logo-left { text-align: left; }
        .logo-center { text-align: center; }
        .logo-right { text-align: right; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .container { padding: 40px;  position: relative;}
        .company-name { font-size: 24px; font-weight: bold; color: {{ $primaryColor }}; margin-bottom: 8px; }
        .company-details { color: #666; font-size: 11px; }
        .doc-title { font-size: 28px; font-weight: bold; color: {{ $primaryColor }}; margin-bottom: 8px; }
        .doc-number { font-size: 14px; color: #666; }
        .doc-meta { margin-top: 16px; }
        .doc-meta p { margin-bottom: 4px; }
        .doc-meta strong { color: {{ $primaryColor }}; }
        .warehouse-section { margin-bottom: 30px; }
        .warehouse-box { display: inline-block; width: 45%; padding: 20px; background: #f9f9f9; border-radius: 8px; vertical-align: top; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 8px; }
        .warehouse-name { font-size: 16px; font-weight: bold; color: {{ $primaryColor }}; margin-bottom: 4px; }
        .warehouse-details { color: #666; }
        .arrow { display: inline-block; width: 8%; text-align: center; font-size: 24px; color: #999; vertical-align: middle; padding-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f5f5f5; padding: 12px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #666; border-bottom: 2px solid #e5e5e5; }
        th.right, td.right { text-align: right; }
        th.center, td.center { text-align: center; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .product-name { font-weight: 500; color: {{ $primaryColor }}; }
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

        {{-- Watermark for draft/cancelled --}}
        @if(($settings->show_watermark ?? true) && in_array($transfer->status, ['draft', 'cancelled']))
            <div class="watermark">{{ $transfer->status === 'cancelled' ? 'CANCELLED' : ($settings->watermark_text ?? 'DRAFT') }}</div>
        @endif
        {{-- Header --}}
        <table style="margin-bottom: 40px;">
            <tr>
                <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                    @if(($settings->show_logo ?? true) && $company["logo"])
                        <div class="logo-{{ $settings->logo_position ?? "left" }}" style="margin-bottom: 10px;">
                            <img src="{{ $company["logo"] }}" alt="{{ $company["name"] }}" style="max-width: {{ $settings->logo_size ?? 120 }}px; height: auto;" />
                        </div>
                    @endif
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
                        <p><strong>Date:</strong> {{ $transfer->transfer_date->format($dateFormat) }}</p>
                        @if($transfer->expected_arrival_date)
                        <p><strong>Expected Arrival:</strong> {{ $transfer->expected_arrival_date->format($dateFormat) }}</p>
                        @endif
                        @if($settings->show_status_badge ?? true)
                        <p>
                            <span class="status-badge status-{{ $transfer->status }}">
                                {{ ucfirst(str_replace('_', ' ', $transfer->status)) }}
                            </span>
                        </p>
                        @endif
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
