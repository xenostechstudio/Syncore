<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vendor Bill - {{ $bill->bill_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; line-height: 1.4; color: #333; }
        .container { padding: 30px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .company-info { max-width: 50%; }
        .company-name { font-size: 20px; font-weight: bold; color: #1a1a1a; margin-bottom: 5px; }
        .company-details { font-size: 10px; color: #666; }
        .document-info { text-align: right; }
        .document-title { font-size: 24px; font-weight: bold; color: #1a1a1a; margin-bottom: 10px; }
        .document-number { font-size: 14px; color: #666; }
        .parties { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .party-box { width: 48%; }
        .party-label { font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 5px; }
        .party-name { font-size: 14px; font-weight: bold; margin-bottom: 3px; }
        .party-details { font-size: 10px; color: #666; }
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 20px; padding: 15px; background: #f8f8f8; border-radius: 5px; }
        .info-item { text-align: center; }
        .info-label { font-size: 9px; text-transform: uppercase; color: #666; }
        .info-value { font-size: 12px; font-weight: bold; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f0f0f0; padding: 10px 8px; text-align: left; font-size: 10px; text-transform: uppercase; color: #666; border-bottom: 2px solid #ddd; }
        td { padding: 10px 8px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals { width: 300px; margin-left: auto; }
        .totals-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .totals-row.grand-total { border-top: 2px solid #333; border-bottom: none; font-size: 14px; font-weight: bold; padding-top: 12px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .status-draft { background: #f0f0f0; color: #666; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-partial { background: #dbeafe; color: #1e40af; }
        .notes { margin-top: 30px; padding: 15px; background: #f8f8f8; border-radius: 5px; }
        .notes-label { font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 5px; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <table style="width: 100%; margin-bottom: 30px;">
            <tr>
                <td style="width: 50%; vertical-align: top; border: none;">
                    <div class="company-name">{{ $company['name'] }}</div>
                    <div class="company-details">
                        @if($company['address']){{ $company['address'] }}<br>@endif
                        @if($company['phone'])Tel: {{ $company['phone'] }}<br>@endif
                        @if($company['email']){{ $company['email'] }}@endif
                    </div>
                </td>
                <td style="width: 50%; text-align: right; vertical-align: top; border: none;">
                    <div class="document-title">VENDOR BILL</div>
                    <div class="document-number">#{{ $bill->bill_number }}</div>
                    <div style="margin-top: 10px;">
                        <span class="status-badge status-{{ $bill->status }}">{{ ucfirst($bill->status) }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 30px;">
            <tr>
                <td style="width: 50%; vertical-align: top; border: none;">
                    <div class="party-label">Supplier</div>
                    <div class="party-name">{{ $bill->supplier?->name ?? '-' }}</div>
                    <div class="party-details">
                        @if($bill->supplier?->address){{ $bill->supplier->address }}<br>@endif
                        @if($bill->supplier?->phone)Tel: {{ $bill->supplier->phone }}<br>@endif
                        @if($bill->supplier?->email){{ $bill->supplier->email }}@endif
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; border: none;">
                    <div class="party-label">Bill To</div>
                    <div class="party-name">{{ $company['name'] }}</div>
                    <div class="party-details">
                        @if($company['address']){{ $company['address'] }}<br>@endif
                        @if($company['tax_id'])Tax ID: {{ $company['tax_id'] }}@endif
                    </div>
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 20px; background: #f8f8f8;">
            <tr>
                <td style="width: 25%; text-align: center; padding: 15px; border: none;">
                    <div class="info-label">Bill Date</div>
                    <div class="info-value">{{ $bill->bill_date?->format('M d, Y') ?? '-' }}</div>
                </td>
                <td style="width: 25%; text-align: center; padding: 15px; border: none;">
                    <div class="info-label">Due Date</div>
                    <div class="info-value">{{ $bill->due_date?->format('M d, Y') ?? '-' }}</div>
                </td>
                <td style="width: 25%; text-align: center; padding: 15px; border: none;">
                    <div class="info-label">Vendor Reference</div>
                    <div class="info-value">{{ $bill->vendor_reference ?? '-' }}</div>
                </td>
                <td style="width: 25%; text-align: center; padding: 15px; border: none;">
                    <div class="info-label">Payment Status</div>
                    <div class="info-value">{{ ucfirst($bill->status) }}</div>
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 40%;">Description</th>
                    <th style="width: 15%;" class="text-center">Quantity</th>
                    <th style="width: 20%;" class="text-right">Unit Price</th>
                    <th style="width: 20%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bill->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product?->name ?? $item->description }}</strong>
                        @if($item->product?->sku)<br><span style="font-size: 10px; color: #666;">SKU: {{ $item->product->sku }}</span>@endif
                    </td>
                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table style="width: 300px; margin-left: auto;">
            <tr>
                <td style="border: none; padding: 8px 0;">Subtotal</td>
                <td style="border: none; padding: 8px 0; text-align: right;">Rp {{ number_format($bill->subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border: none; padding: 8px 0;">Tax</td>
                <td style="border: none; padding: 8px 0; text-align: right;">Rp {{ number_format($bill->tax, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border: none; padding: 12px 0; font-size: 14px; font-weight: bold; border-top: 2px solid #333;">Total</td>
                <td style="border: none; padding: 12px 0; text-align: right; font-size: 14px; font-weight: bold; border-top: 2px solid #333;">Rp {{ number_format($bill->total, 0, ',', '.') }}</td>
            </tr>
            @if($bill->paid_amount > 0)
            <tr>
                <td style="border: none; padding: 8px 0; color: #059669;">Paid Amount</td>
                <td style="border: none; padding: 8px 0; text-align: right; color: #059669;">Rp {{ number_format($bill->paid_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border: none; padding: 8px 0; font-weight: bold;">Balance Due</td>
                <td style="border: none; padding: 8px 0; text-align: right; font-weight: bold;">Rp {{ number_format($bill->balance_due, 0, ',', '.') }}</td>
            </tr>
            @endif
        </table>

        @if($bill->notes)
        <div class="notes">
            <div class="notes-label">Notes</div>
            <div>{{ $bill->notes }}</div>
        </div>
        @endif

        <div class="footer">
            Generated on {{ now()->format('M d, Y H:i') }} | {{ $company['name'] }}
        </div>
    </div>
</body>
</html>
