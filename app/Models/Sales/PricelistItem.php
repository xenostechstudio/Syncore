<?php

namespace App\Models\Sales;

use App\Models\Inventory\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricelistItem extends Model
{
    protected $fillable = [
        'pricelist_id',
        'product_id',
        'price',
        'min_quantity',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'min_quantity' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function pricelist(): BelongsTo
    {
        return $this->belongsTo(Pricelist::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
