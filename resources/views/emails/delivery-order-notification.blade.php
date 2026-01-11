<x-mail::message>
# Delivery Order

Dear {{ $customer?->name ?? 'Valued Customer' }},

Your order is on its way! Here are the delivery details:

**Delivery Number:** {{ $deliveryOrder->delivery_number }}  
**Delivery Date:** {{ $deliveryOrder->delivery_date?->format('M d, Y') }}  
**Status:** {{ ucfirst($deliveryOrder->status) }}

## Items Being Delivered

<x-mail::table>
| Product | Quantity |
|:--------|:--------:|
@foreach($items as $item)
| {{ $item->product?->name ?? '-' }} | {{ number_format($item->quantity) }} |
@endforeach
</x-mail::table>

@if($deliveryOrder->shipping_address)
**Shipping Address:**  
{{ $deliveryOrder->shipping_address }}
@endif

@if($deliveryOrder->notes)
**Notes:** {{ $deliveryOrder->notes }}
@endif

If you have any questions about your delivery, please don't hesitate to contact us.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
