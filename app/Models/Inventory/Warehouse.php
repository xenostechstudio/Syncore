<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'location',
        'contact_info',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }
}
