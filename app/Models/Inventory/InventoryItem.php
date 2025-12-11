<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'product_type',
        'internal_reference',
        'description',
        'quantity',
        'cost_price',
        'selling_price',
        'status',
        'warehouse_id',
        'category_id',
        'responsible_id',
        'weight',
        'volume',
        'customer_lead_time',
        'receipt_note',
        'delivery_note',
        'internal_notes',
        'is_favorite',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'weight' => 'decimal:3',
        'volume' => 'decimal:3',
        'customer_lead_time' => 'integer',
        'is_favorite' => 'boolean',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function pricelistRules(): HasMany
    {
        return $this->hasMany(ProductPricelistRule::class, 'product_id');
    }
}
