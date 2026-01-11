<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .container { padding: 40px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .company-info { max-width: 300px; }
        .company-name { font-size: 24px; font-weight: bold; color: #111; margin-bottom: 8px; }
        .company-details { color: #666; font-size: 11px; }
        .invoice-info { text-align: right; }
        .invoice-title { font-size: 28px; font-weight: bold; color: #111; margin-bottom: 8px; }
        .invoice-number { font-size: 14px; color: #666; }
        .invoice-meta { margin-top: 16px; }
        .invoice-meta p { margin-bottom: 4px; }
        .invoice-meta strong { color: #111; }
        .customer-section { margin-bottom: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 8px; }
        .customer-name { font-size: 16px; font-weight: bold; color: #111; margin-bottom: 4px; }
        .customer-details { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f5f5f5; padding: 12px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #666; border-bottom: 2px solid #e5e5e5; }
        th.right, td.right { text-align: right; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .product-name { font-weight: 500; color: #111; }
        .product-desc { font-size: 11px; color: #666; }
        .totals { width: 300px; margin-left: auto; }
        .totals-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .totals-row.total { border-top: 2px solid #111; border-bottom: none; font-size: 16px; font-weight: bold; padding-top: 12px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-sent { background: #dbeafe; color: #1d4ed8; }
        .status-paid { background: #d1fae5; color: #059669; }
        .status-partial { background: #fef3c7; color: #d97706; }
        .status-overdue { background: #fee2e2; color: #dc2626; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #999; font-size: 10px; }
        .payment-info { margin-top: 30px; padding: 20px; background: #f0fdf4; border-radius: 8px; border-left: 4px solid #22c55e; }
        .payment-title { font-weight: bold; color: #166534; margin-bottom: 8px; }
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
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number"># {{ $invoice->invoice_number }}</div>
                    <div class="invoice-meta">
                        <p><strong>Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
                        <p><strong>Due Date:</strong> {{ $invoice->due_date?->format('M d, Y') ?? '-' }}</p>
                        <p>
                            <span class="status-badge status-{{ $invoice->status }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </p>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Bill To --}}
        <div class="customer-section">
            <div class="section-title">Bill To</div>
            <div class="customer-name">{{ $invoice->customer->name }}</div>
            <div class="customer-details">
                @if($invoice->customer->email){{ $invoice->customer->email }}<br>@endif
                @if($invoice->customer->phone){{ $invoice->customer->phone }}<br>@endif
                @if($invoice->customer->address){{ $invoice->customer->address }}@endif
            </div>
        </div>

        {{-- Items Table --}}
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Description</th>
                    <th class="right" style="width: 15%;">Qty</th>
                    <th class="right" style="width: 20%;">Unit Price</th>
                    <th class="right" style="width: 25%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <div class="product-name">{{ $item->product->name ?? $item->description }}</div>
                        @if($item->description && $item->product)
                            <div class="product-desc">{{ $item->description }}</div>
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
                <span>Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($invoice->tax > 0)
            <div class="totals-row">
                <span>Tax</span>
                <span>Rp {{ number_format($invoice->tax, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($invoice->discount > 0)
            <div class="totals-row">
                <span>Discount</span>
                <span>-Rp {{ number_format($invoice->discount, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="totals-row total">
                <span>Total</span>
                <span>Rp {{ number_format($invoice->total, 0, ',', '.') }}</span>
            </div>
            @if($invoice->paid_amount > 0)
            <div class="totals-row">
                <span>Paid</span>
                <span>-Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
            </div>
            <div class="totals-row total">
                <span>Balance Due</span>
                <span>Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>

        {{-- Payment Info --}}
        @if($invoice->balance_due > 0)
        <div class="payment-info">
            <div class="payment-title">Payment Information</div>
            <p>Please make payment to the following account:</p>
            <p><strong>Bank:</strong> [Bank Name]</p>
            <p><strong>Account:</strong> [Account Number]</p>
            <p><strong>Name:</strong> {{ $company['name'] }}</p>
        </div>
        @endif

        {{-- Notes --}}
        @if($invoice->notes)
        <div style="margin-top: 30px;">
            <div class="section-title">Notes</div>
            <p style="color: #666;">{{ $invoice->notes }}</p>
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
