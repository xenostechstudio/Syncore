<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Adjustment {{ $adjustment->adjustment_number }}</title>
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
        .section { margin-bottom: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <table style="margin-bottom: 40px;">
            <tr>
                <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                    <div class="company-name">{{ $company['name'] ?? config('app.name') }}</div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right; border: none; padding: 0;">
                    <div class="doc-title">STOCK ADJUSTMENT</div>
                    <div class="doc-number"># {{ $adjustment->adjustment_number }}</div>
                </td>
            </tr>
        </table>

        <div class="section">
            <div class="section-title">Adjustment Details</div>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; border: none; padding: 4px 0;">
                        <strong>Warehouse:</strong> {{ $adjustment->warehouse?->name ?? '-' }}
                    </td>
                    <td style="width: 50%; border: none; padding: 4px 0;">
                        <strong>Date:</strong> {{ $adjustment->adjustment_date->format('M d, Y') }}
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 4px 0;">
                        <strong>Type:</strong> {{ ucfirst($adjustment->adjustment_type) }}
                    </td>
                    <td style="border: none; padding: 4px 0;">
                        <strong>Status:</strong> {{ ucfirst($adjustment->status) }}
                    </td>
                </tr>
            </table>
            @if($adjustment->reason)
            <p style="margin-top: 12px;"><strong>Reason:</strong> {{ $adjustment->reason }}</p>
            @endif
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
            <thead>
                <tr>
                    <th style="background: #f5f5f5; padding: 12px; text-align: left; border-bottom: 2px solid #e5e5e5;">Product</th>
                    <th style="background: #f5f5f5; padding: 12px; text-align: right; border-bottom: 2px solid #e5e5e5;">System Qty</th>
                    <th style="background: #f5f5f5; padding: 12px; text-align: right; border-bottom: 2px solid #e5e5e5;">Counted Qty</th>
                    <th style="background: #f5f5f5; padding: 12px; text-align: right; border-bottom: 2px solid #e5e5e5;">Difference</th>
                </tr>
            </thead>
            <tbody>
                @foreach($adjustment->items as $item)
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;">
                        <div style="font-weight: 500;">{{ $item->product?->name ?? '-' }}</div>
                        @if($item->product?->sku)<div style="font-size: 11px; color: #666;">SKU: {{ $item->product->sku }}</div>@endif
                    </td>
                    <td style="padding: 12px; border-bottom: 1px solid #eee; text-align: right;">{{ number_format($item->system_quantity) }}</td>
                    <td style="padding: 12px; border-bottom: 1px solid #eee; text-align: right;">{{ number_format($item->counted_quantity) }}</td>
                    <td style="padding: 12px; border-bottom: 1px solid #eee; text-align: right; color: {{ $item->difference >= 0 ? '#059669' : '#dc2626' }};">
                        {{ $item->difference >= 0 ? '+' : '' }}{{ number_format($item->difference) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($adjustment->notes)
        <div style="margin-top: 30px;">
            <div class="section-title">Notes</div>
            <p style="color: #666;">{{ $adjustment->notes }}</p>
        </div>
        @endif

        <div style="margin-top: 60px;">
            <div style="display: inline-block; width: 200px; text-align: center; margin-right: 40px;">
                <div style="border-top: 1px solid #333; margin-top: 60px; padding-top: 8px;">Prepared By</div>
            </div>
            <div style="display: inline-block; width: 200px; text-align: center;">
                <div style="border-top: 1px solid #333; margin-top: 60px; padding-top: 8px;">Approved By</div>
            </div>
        </div>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #999; font-size: 10px;">
            <p>Generated on {{ now()->format('M d, Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
