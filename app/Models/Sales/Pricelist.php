<?php

namespace App\Models\Sales;

use App\Models\Inventory\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pricelist extends Model
{
    protected $fillable = [
        'name',
        'code',
        'currency',
        'type',
        'discount',
        'start_date',
        'end_date',
        'is_active',
        'description',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PricelistItem::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'pricelist_items', 'pricelist_id', 'product_id')
            ->withPivot(['price', 'min_quantity', 'start_date', 'end_date'])
            ->withTimestamps();
    }
}
