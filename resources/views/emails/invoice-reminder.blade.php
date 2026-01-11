<x-mail::message>
# Payment Reminder

Dear {{ $customer?->name ?? 'Customer' }},

@if($daysOverdue > 0)
This is a friendly reminder that your invoice **{{ $invoice->invoice_number }}** is now **{{ $daysOverdue }} days overdue**.
@else
This is a friendly reminder about your upcoming payment for invoice **{{ $invoice->invoice_number }}**.
@endif

**Invoice Details:**
- Invoice Number: {{ $invoice->invoice_number }}
- Invoice Date: {{ $invoice->invoice_date?->format('M d, Y') }}
- Due Date: {{ $invoice->due_date?->format('M d, Y') }}
- Total Amount: Rp{{ number_format($invoice->total, 0, ',', '.') }}
- Amount Paid: Rp{{ number_format($invoice->paid_amount ?? 0, 0, ',', '.') }}
- **Balance Due: Rp{{ number_format($balanceDue, 0, ',', '.') }}**

@if($publicUrl)
<x-mail::button :url="$publicUrl">
View & Pay Invoice
</x-mail::button>
@endif

If you have already made this payment, please disregard this reminder. If you have any questions or concerns, please don't hesitate to contact us.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
