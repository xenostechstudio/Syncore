<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .container { padding: 40px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .company-info { }
        .company-name { font-size: 24px; font-weight: bold; color: #111; }
        .invoice-title { font-size: 28px; font-weight: bold; color: #111; text-align: right; }
        .invoice-number { color: #666; text-align: right; margin-top: 5px; }
        .info-section { display: table; width: 100%; margin-bottom: 30px; }
        .info-box { display: table-cell; width: 50%; vertical-align: top; }
        .info-label { font-size: 10px; color: #666; text-transform: uppercase; margin-bottom: 5px; }
        .info-value { font-size: 12px; color: #111; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f5f5f5; padding: 12px; text-align: left; font-size: 10px; text-transform: uppercase; color: #666; border-bottom: 2px solid #ddd; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .totals { width: 300px; margin-left: auto; }
        .totals-row { display: table; width: 100%; padding: 8px 0; }
        .totals-label { display: table-cell; color: #666; }
        .totals-value { display: table-cell; text-align: right; }
        .totals-row.total { border-top: 2px solid #111; font-weight: bold; font-size: 14px; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; font-size: 10px; color: #666; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
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
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
                    <div style="margin-top: 10px;">
                        <span class="status status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <table style="margin-bottom: 30px;">
            <tr>
                <td style="border: none; padding: 0; width: 50%; vertical-align: top;">
                    <div class="info-label">Bill To</div>
                    <div class="info-value">
                        <strong>{{ $invoice->customer?->name ?? 'N/A' }}</strong><br>
                        {{ $invoice->customer?->address ?? '' }}<br>
                        {{ $invoice->customer?->email ?? '' }}<br>
                        {{ $invoice->customer?->phone ?? '' }}
                    </div>
                </td>
                <td style="border: none; padding: 0; width: 50%; vertical-align: top; text-align: right;">
                    <div class="info-label">Invoice Details</div>
                    <div class="info-value">
                        Invoice Date: {{ $invoice->invoice_date?->format('M d, Y') }}<br>
                        Due Date: {{ $invoice->due_date?->format('M d, Y') }}<br>
                        @if($invoice->sales_order_id)
                        Order: {{ $invoice->salesOrder?->order_number }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $item)
                <tr>
                    <td>{{ $item->product?->name ?? $item->description ?? 'Item' }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #666;">No items</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span class="totals-label">Subtotal</span>
                <span class="totals-value">Rp{{ number_format($invoice->subtotal ?? 0, 0, ',', '.') }}</span>
            </div>
            @if($invoice->tax_amount > 0)
            <div class="totals-row">
                <span class="totals-label">Tax</span>
                <span class="totals-value">Rp{{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($invoice->discount_amount > 0)
            <div class="totals-row">
                <span class="totals-label">Discount</span>
                <span class="totals-value">-Rp{{ number_format($invoice->discount_amount, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="totals-row total">
                <span class="totals-label">Total</span>
                <span class="totals-value">Rp{{ number_format($invoice->total, 0, ',', '.') }}</span>
            </div>
            @if($invoice->amount_paid > 0)
            <div class="totals-row">
                <span class="totals-label">Amount Paid</span>
                <span class="totals-value">Rp{{ number_format($invoice->amount_paid, 0, ',', '.') }}</span>
            </div>
            <div class="totals-row">
                <span class="totals-label">Balance Due</span>
                <span class="totals-value">Rp{{ number_format($invoice->total - $invoice->amount_paid, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>

        @if($invoice->notes)
        <div class="footer">
            <strong>Notes:</strong><br>
            {{ $invoice->notes }}
        </div>
        @endif
    </div>
</body>
</html>
