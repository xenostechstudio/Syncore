<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustmentItem extends Model
{
    protected $fillable = [
        'inventory_adjustment_id',
        'product_id',
        'system_quantity',
        'counted_quantity',
        'difference',
        'notes',
    ];

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(InventoryAdjustment::class, 'inventory_adjustment_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
