<x-mail::message>
# Payment Confirmation

Dear Customer,

We have received your payment. Thank you!

**Payment Details:**
- Payment Reference: {{ $payment->payment_number }}
- Payment Date: {{ $payment->payment_date?->format('M d, Y') }}
- Amount Paid: Rp{{ number_format($payment->amount, 0, ',', '.') }}
- Payment Method: {{ ucfirst($payment->payment_method ?? 'N/A') }}

@if($invoice)
**Invoice Details:**
- Invoice Number: {{ $invoice->invoice_number }}
- Invoice Total: Rp{{ number_format($invoice->total, 0, ',', '.') }}
@endif

Thanks for your business!

{{ config('app.name') }}
</x-mail::message>
