<?php

namespace App\Models\Delivery;

use App\Models\Inventory\Product;
use App\Models\Sales\SalesOrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrderItem extends Model
{
    protected $fillable = [
        'delivery_order_id',
        'sales_order_item_id',
        'product_id',
        'description',
        'quantity',
        'quantity_delivered',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_delivered' => 'integer',
    ];

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function salesOrderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isFullyDelivered(): bool
    {
        return $this->quantity_delivered >= $this->quantity;
    }

    public function getRemainingQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->quantity_delivered);
    }
}
