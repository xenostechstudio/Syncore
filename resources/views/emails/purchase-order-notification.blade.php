<x-mail::message>
# Purchase Order

Dear {{ $supplier->name }},

We are pleased to send you the following purchase order:

**Order Number:** {{ $purchaseOrder->reference }}  
**Order Date:** {{ $purchaseOrder->order_date?->format('M d, Y') }}  
**Expected Delivery:** {{ $purchaseOrder->expected_date?->format('M d, Y') ?? 'Not specified' }}

## Order Items

<x-mail::table>
| Product | Quantity | Unit Price | Total |
|:--------|:--------:|:----------:|------:|
@foreach($items as $item)
| {{ $item->product?->name ?? $item->description }} | {{ number_format($item->quantity) }} | Rp {{ number_format($item->unit_price, 0, ',', '.') }} | Rp {{ number_format($item->total, 0, ',', '.') }} |
@endforeach
</x-mail::table>

**Subtotal:** Rp {{ number_format($purchaseOrder->subtotal, 0, ',', '.') }}  
**Tax:** Rp {{ number_format($purchaseOrder->tax, 0, ',', '.') }}  
**Total:** Rp {{ number_format($purchaseOrder->total, 0, ',', '.') }}

@if($purchaseOrder->notes)
**Notes:** {{ $purchaseOrder->notes }}
@endif

Please confirm receipt of this order and provide the expected delivery date.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
