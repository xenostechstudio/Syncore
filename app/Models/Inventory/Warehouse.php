<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Inventory\InventoryTransfer;
use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\InventoryStock;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'location',
        'contact_info',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(InventoryTransfer::class, 'source_warehouse_id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    public function warehouseIns(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class)->where('adjustment_type', 'increase');
    }

    public function warehouseOuts(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class)->where('adjustment_type', 'decrease');
    }
}
