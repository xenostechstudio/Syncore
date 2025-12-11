<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'adjustment_number',
        'warehouse_id',
        'user_id',
        'adjustment_date',
        'adjustment_type',
        'status',
        'reason',
        'notes',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryAdjustmentItem::class);
    }

    public static function generateAdjustmentNumber(): string
    {
        $prefix = 'ADJ';
        $date = now()->format('Ymd');
        $lastAdjustment = self::whereDate('created_at', today())->latest()->first();
        $sequence = $lastAdjustment ? (int) substr($lastAdjustment->adjustment_number, -4) + 1 : 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }
}
