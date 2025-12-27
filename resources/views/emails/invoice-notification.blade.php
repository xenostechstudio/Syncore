<x-mail::message>
# Invoice {{ $invoice->invoice_number }}

Dear {{ $customer?->name ?? 'Customer' }},

A new invoice has been generated for you.

**Invoice Details:**
- Invoice Number: {{ $invoice->invoice_number }}
- Invoice Date: {{ $invoice->invoice_date?->format('M d, Y') }}
- Due Date: {{ $invoice->due_date?->format('M d, Y') }}
- Total Amount: Rp{{ number_format($invoice->total, 0, ',', '.') }}

@if($publicUrl)
<x-mail::button :url="$publicUrl">
View Invoice
</x-mail::button>
@endif

If you have any questions about this invoice, please don't hesitate to contact us.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
