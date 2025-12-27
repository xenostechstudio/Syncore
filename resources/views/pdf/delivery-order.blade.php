<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Order {{ $delivery->delivery_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .container { padding: 40px; }
        .company-name { font-size: 24px; font-weight: bold; color: #111; }
        .document-title { font-size: 28px; font-weight: bold; color: #111; text-align: right; }
        .document-number { color: #666; text-align: right; margin-top: 5px; }
        .info-label { font-size: 10px; color: #666; text-transform: uppercase; margin-bottom: 5px; }
        .info-value { font-size: 12px; color: #111; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f5f5f5; padding: 12px; text-align: left; font-size: 10px; text-transform: uppercase; color: #666; border-bottom: 2px solid #ddd; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; font-size: 10px; color: #666; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-in_transit { background: #dbeafe; color: #1e40af; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .signature-box { border: 1px solid #ddd; padding: 20px; margin-top: 40px; }
        .signature-line { border-bottom: 1px solid #333; margin-top: 60px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <table style="margin-bottom: 40px;">
            <tr>
                <td style="border: none; padding: 0;">
                    <div class="company-name">{{ config('app.name') }}</div>
                </td>
                <td style="border: none; padding: 0; text-align: right;">
                    <div class="document-title">DELIVERY ORDER</div>
                    <div class="document-number">#{{ $delivery->delivery_number }}</div>
                    <div style="margin-top: 10px;">
                        <span class="status status-{{ str_replace(' ', '_', strtolower($delivery->status->value ?? $delivery->status ?? 'pending')) }}">{{ ucfirst($delivery->status->value ?? $delivery->status ?? 'Pending') }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <table style="margin-bottom: 30px;">
            <tr>
                <td style="border: none; padding: 0; width: 50%; vertical-align: top;">
                    <div class="info-label">Ship To</div>
                    <div class="info-value">
                        <strong>{{ $delivery->recipient_name ?? $delivery->salesOrder?->customer?->name ?? 'N/A' }}</strong><br>
                        {{ $delivery->shipping_address ?? $delivery->salesOrder?->customer?->address ?? '' }}<br>
                        Phone: {{ $delivery->recipient_phone ?? $delivery->salesOrder?->customer?->phone ?? '' }}
                    </div>
                </td>
                <td style="border: none; padding: 0; width: 50%; vertical-align: top; text-align: right;">
                    <div class="info-label">Delivery Details</div>
                    <div class="info-value">
                        Delivery Date: {{ $delivery->delivery_date?->format('M d, Y') }}<br>
                        @if($delivery->salesOrder)
                        Sales Order: {{ $delivery->salesOrder->order_number }}<br>
                        @endif
                        @if($delivery->warehouse)
                        Warehouse: {{ $delivery->warehouse->name }}<br>
                        @endif
                        @if($delivery->courier)
                        Courier: {{ $delivery->courier }}<br>
                        @endif
                        @if($delivery->tracking_number)
                        Tracking: {{ $delivery->tracking_number }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Product</th>
                    <th class="text-center">Qty Ordered</th>
                    <th class="text-center">Qty Delivered</th>
                </tr>
            </thead>
            <tbody>
                @forelse($delivery->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product?->name ?? 'Product' }}</td>
                    <td class="text-center">{{ $item->quantity_ordered ?? $item->quantity }}</td>
                    <td class="text-center">{{ $item->quantity_delivered ?? $item->quantity }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #666;">No items</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($delivery->notes)
        <div class="footer">
            <strong>Notes:</strong><br>
            {{ $delivery->notes }}
        </div>
        @endif

        <table style="margin-top: 40px;">
            <tr>
                <td style="border: none; padding: 20px; width: 50%; vertical-align: top;">
                    <div class="info-label">Delivered By</div>
                    <div class="signature-line"></div>
                    <div style="font-size: 10px; color: #666;">Name & Signature</div>
                </td>
                <td style="border: none; padding: 20px; width: 50%; vertical-align: top;">
                    <div class="info-label">Received By</div>
                    <div class="signature-line"></div>
                    <div style="font-size: 10px; color: #666;">Name & Signature</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
