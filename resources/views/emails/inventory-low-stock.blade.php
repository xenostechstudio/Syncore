<x-mail::message>
# Low Stock Alert

@if($warehouseName)
The following {{ $totalProducts }} product(s) in **{{ $warehouseName }}** are running low on stock:
@else
The following {{ $totalProducts }} product(s) are running low on stock:
@endif

@if($outOfStockCount > 0)
**⚠️ {{ $outOfStockCount }} product(s) are out of stock!**
@endif

<x-mail::table>
| Product | SKU | Current Stock | Min Stock |
|:--------|:----|:-------------:|:---------:|
@foreach($products as $product)
| {{ $product->name }} | {{ $product->sku ?? '-' }} | {{ $product->quantity ?? 0 }} | {{ $product->min_stock ?? 10 }} |
@endforeach
</x-mail::table>

Please review and restock these items as soon as possible.

<x-mail::button :url="route('inventory.products.index')">
View Inventory
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
