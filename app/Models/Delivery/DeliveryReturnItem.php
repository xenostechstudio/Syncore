<?php

namespace App\Models\Delivery;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryReturnItem extends Model
{
    protected $fillable = [
        'delivery_return_id',
        'delivery_order_item_id',
        'quantity',
    ];

    public function deliveryReturn(): BelongsTo
    {
        return $this->belongsTo(DeliveryReturn::class);
    }

    public function deliveryOrderItem(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrderItem::class);
    }
}
