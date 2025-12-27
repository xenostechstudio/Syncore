<x-mail::message>
# Order Confirmation

Dear {{ $customer?->name ?? 'Customer' }},

Thank you for your order! Your order has been confirmed.

**Order Details:**
- Order Number: {{ $order->order_number }}
- Order Date: {{ $order->order_date?->format('M d, Y') }}
- Total Amount: Rp{{ number_format($order->total, 0, ',', '.') }}

@if($order->items && $order->items->count() > 0)
**Items Ordered:**

<x-mail::table>
| Product | Qty | Price |
|:--------|:---:|------:|
@foreach($order->items as $item)
| {{ $item->product?->name ?? 'Product' }} | {{ $item->quantity }} | Rp{{ number_format($item->subtotal, 0, ',', '.') }} |
@endforeach
</x-mail::table>
@endif

We will notify you once your order has been shipped.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
