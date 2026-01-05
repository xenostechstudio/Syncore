<?php

namespace App\Models\Inventory;

use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Inventory\InventoryTransfer;
use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\InventoryStock;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Warehouse extends Model
{
    use LogsActivity, HasNotes;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'location', 'contact_info'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Warehouse created',
                'updated' => 'Warehouse updated',
                'deleted' => 'Warehouse deleted',
                default => "Warehouse {$eventName}",
            });
    }
}
