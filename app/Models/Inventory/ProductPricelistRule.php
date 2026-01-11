<?php

namespace App\Models\Inventory;

use App\Models\Sales\Pricelist;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPricelistRule extends Model
{
    use LogsActivity;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'product_id',
        'pricelist_id',
        'price_type',
        'fixed_price',
        'discount_percentage',
        'min_quantity',
        'date_start',
        'date_end',
    ];

    protected $casts = [
        'fixed_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'min_quantity' => 'integer',
        'date_start' => 'date',
        'date_end' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function pricelist(): BelongsTo
    {
        return $this->belongsTo(Pricelist::class);
    }
}
