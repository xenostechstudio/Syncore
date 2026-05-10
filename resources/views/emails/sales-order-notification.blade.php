<x-mail::message>
# {{ $documentType }} {{ $order->order_number }}

@if(!empty($customMessage))
{!! nl2br(e($customMessage)) !!}
@else
Dear {{ $customer?->name ?? 'Customer' }},

Please find your {{ strtolower($documentType) }} below.
@endif

**Details:**
- Number: {{ $order->order_number }}
- Date: {{ $order->order_date?->format('M d, Y') }}
- Total: Rp{{ number_format($order->total, 0, ',', '.') }}

@if($publicUrl)
<x-mail::button :url="$publicUrl">
View {{ $documentType }}
</x-mail::button>
@endif

If you have any questions, please don't hesitate to contact us.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
