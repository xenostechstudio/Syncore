<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $settings->invoice_title ?? 'Invoice' }} {{ $invoice->invoice_number }}</title>
    <style>
        @php
            $primaryColor = $settings->primary_color ?? '#18181b';
            $accentColor = $settings->accent_color ?? '#10b981';
        @endphp
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .container { padding: 40px; position: relative; }
        
        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            font-weight: bold;
            color: rgba(0, 0, 0, 0.06);
            text-transform: uppercase;
            z-index: -1;
            white-space: nowrap;
        }
        
        /* Header */
        .company-name { font-size: 24px; font-weight: bold; color: {{ $primaryColor }}; margin-bottom: 8px; }
        .company-details { color: #666; font-size: 11px; }
        .invoice-title { font-size: 28px; font-weight: bold; color: {{ $primaryColor }}; margin-bottom: 8px; }
        .invoice-number { font-size: 14px; color: #666; }
        .invoice-meta { margin-top: 16px; }
        .invoice-meta p { margin-bottom: 4px; }
        .invoice-meta strong { color: {{ $primaryColor }}; }
        
        /* Customer */
        .customer-section { margin-bottom: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 8px; }
        .customer-name { font-size: 16px; font-weight: bold; color: {{ $primaryColor }}; margin-bottom: 4px; }
        .customer-details { color: #666; }
        
        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f5f5f5; padding: 12px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #666; border-bottom: 2px solid #e5e5e5; }
        th.right, td.right { text-align: right; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .product-name { font-weight: 500; color: {{ $primaryColor }}; }
        .product-desc { font-size: 11px; color: #666; }
        
        /* Totals */
        .totals { width: 300px; margin-left: auto; }
        .totals-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .totals-row.total { border-top: 2px solid {{ $primaryColor }}; border-bottom: none; font-size: 16px; font-weight: bold; padding-top: 12px; }
        
        /* Status Badge */
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-sent { background: #dbeafe; color: #1d4ed8; }
        .status-paid { background: #d1fae5; color: #059669; }
        .status-partial { background: #fef3c7; color: #d97706; }
        .status-overdue { background: #fee2e2; color: #dc2626; }
        
        /* Payment Info */
        .payment-info { margin-top: 30px; padding: 20px; background: #f0fdf4; border-radius: 8px; border-left: 4px solid {{ $accentColor }}; }
        .payment-title { font-weight: bold; color: #166534; margin-bottom: 8px; }
        .bank-grid { display: table; width: 100%; }
        .bank-item { display: table-cell; width: 50%; padding-right: 20px; }
        .bank-name { font-weight: bold; color: {{ $primaryColor }}; }
        
        /* Signature */
        .signature-section { margin-top: 60px; text-align: right; }
        .signature-line { border-top: 1px solid #333; width: 200px; margin-left: auto; padding-top: 8px; }
        .signature-label { font-size: 11px; color: #666; }
        
        /* Footer */
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #999; font-size: 10px; }
        
        /* Logo positioning */
        .logo-left { text-align: left; }
        .logo-center { text-align: center; }
        .logo-right { text-align: right; }
    </style>
</head>
<body>
    <div class="container">
        {{-- Watermark for Draft/Cancelled --}}
        @if(($settings->show_watermark ?? true) && in_array($invoice->status, ['draft', 'cancelled']))
            <div class="watermark">{{ $invoice->status === 'cancelled' ? 'CANCELLED' : ($settings->watermark_text ?? 'DRAFT') }}</div>
        @endif

        {{-- Header --}}
        <table style="margin-bottom: 40px;">
            <tr>
                <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                    @if(($settings->show_logo ?? true) && $company['logo'])
                        <div class="logo-{{ $settings->logo_position ?? 'left' }}" style="margin-bottom: 10px;">
                            <img src="{{ $company['logo'] }}" alt="{{ $company['name'] }}" style="max-width: {{ $settings->logo_size ?? 120 }}px; height: auto;" />
                        </div>
                    @endif
                    <div class="company-name">{{ $company['name'] }}</div>
                    <div class="company-details">
                        @if($company['address']){{ $company['address'] }}<br>@endif
                        @if($company['phone'])Phone: {{ $company['phone'] }}<br>@endif
                        @if($company['email'])Email: {{ $company['email'] }}<br>@endif
                        @if($company['tax_id'])Tax ID: {{ $company['tax_id'] }}@endif
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right; border: none; padding: 0;">
                    <div class="invoice-title">{{ $settings->invoice_title ?? 'INVOICE' }}</div>
                    <div class="invoice-number"># {{ $invoice->invoice_number }}</div>
                    <div class="invoice-meta">
                        @php
                            $dateFormat = $settings->date_format ?? 'M d, Y';
                        @endphp
                        <p><strong>Date:</strong> {{ $invoice->invoice_date->format($dateFormat) }}</p>
                        <p><strong>Due Date:</strong> {{ $invoice->due_date?->format($dateFormat) ?? '-' }}</p>
                        @if($settings->show_status_badge ?? true)
                        <p>
                            <span class="status-badge status-{{ $invoice->status }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </p>
                        @endif
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
                    <td class="right">@formatCurrency($item->unit_price, $settings)</td>
                    <td class="right">@formatCurrency($item->total, $settings)</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        @php
            $currencySymbol = $settings->currency_symbol ?? 'Rp';
            $numberFormat = $settings->number_format ?? 'id';
            $formatNumber = function($amount) use ($numberFormat, $currencySymbol) {
                $formatted = $numberFormat === 'id' 
                    ? number_format($amount, 0, ',', '.')
                    : number_format($amount, 2, '.', ',');
                return "{$currencySymbol} {$formatted}";
            };
        @endphp
        <div class="totals">
            <div class="totals-row">
                <span>Subtotal</span>
                <span>{{ $formatNumber($invoice->subtotal) }}</span>
            </div>
            @if(($settings->show_tax_breakdown ?? true) && $invoice->tax > 0)
            <div class="totals-row">
                <span>Tax</span>
                <span>{{ $formatNumber($invoice->tax) }}</span>
            </div>
            @endif
            @if(($settings->show_discount ?? true) && $invoice->discount > 0)
            <div class="totals-row">
                <span>Discount</span>
                <span>-{{ $formatNumber($invoice->discount) }}</span>
            </div>
            @endif
            <div class="totals-row total">
                <span>Total</span>
                <span>{{ $formatNumber($invoice->total) }}</span>
            </div>
            @if($invoice->paid_amount > 0)
            <div class="totals-row">
                <span>Paid</span>
                <span>-{{ $formatNumber($invoice->paid_amount) }}</span>
            </div>
            <div class="totals-row total">
                <span>Balance Due</span>
                <span>{{ $formatNumber($invoice->total - $invoice->paid_amount) }}</span>
            </div>
            @endif
        </div>

        {{-- Payment Info --}}
        @if(($settings->show_payment_info ?? true) && ($invoice->total - $invoice->paid_amount) > 0)
        <div class="payment-info">
            <div class="payment-title">Payment Information</div>
            @if($settings->bank_name || $settings->bank_name_2)
                <div class="bank-grid">
                    @if($settings->bank_name)
                    <div class="bank-item">
                        <p class="bank-name">{{ $settings->bank_name }}</p>
                        <p>Account: {{ $settings->bank_account }}</p>
                        <p>Name: {{ $settings->bank_holder }}</p>
                    </div>
                    @endif
                    @if($settings->bank_name_2)
                    <div class="bank-item">
                        <p class="bank-name">{{ $settings->bank_name_2 }}</p>
                        <p>Account: {{ $settings->bank_account_2 }}</p>
                        <p>Name: {{ $settings->bank_holder_2 }}</p>
                    </div>
                    @endif
                </div>
            @else
                <p>Please contact us for payment details.</p>
            @endif
        </div>
        @endif

        {{-- Notes --}}
        @if($invoice->notes || $settings->default_notes)
        <div style="margin-top: 30px;">
            <div class="section-title">Notes</div>
            <p style="color: #666;">{{ $invoice->notes ?? $settings->default_notes }}</p>
        </div>
        @endif

        {{-- Terms --}}
        @if($invoice->terms || $settings->default_terms)
        <div style="margin-top: 20px;">
            <div class="section-title">Terms & Conditions</div>
            <p style="color: #666; font-size: 10px;">{{ $invoice->terms ?? $settings->default_terms }}</p>
        </div>
        @endif

        {{-- Signature --}}
        @if($settings->show_signature ?? false)
        <div class="signature-section">
            <div class="signature-line">
                <span class="signature-label">{{ $settings->signature_label ?? 'Authorized Signature' }}</span>
            </div>
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <p>{{ $settings->footer_text ?? 'Thank you for your business!' }}</p>
            <p>{{ $company['name'] }} @if($company['website'])• {{ $company['website'] }}@endif</p>
        </div>
    </div>
</body>
</html>
