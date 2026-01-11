<?php

namespace App\Models\Delivery;

use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\Warehouse;
use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryReturn extends Model
{
    use LogsActivity, HasNotes;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'return_number',
        'delivery_order_id',
        'warehouse_id',
        'return_date',
        'status',
        'notes',
        'received_at',
    ];

    protected $casts = [
        'return_date' => 'date',
        'received_at' => 'datetime',
    ];

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryReturnItem::class);
    }

    public function inventoryAdjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class, 'source_delivery_return_id');
    }

    public static function generateReturnNumber(): string
    {
        $prefix = 'RET';
        $date = now()->format('Ymd');
        $last = self::query()->whereDate('created_at', today())->latest()->value('return_number');

        $sequence = 1;
        if ($last && str_starts_with($last, $prefix . $date)) {
            $suffix = substr($last, strlen($prefix . $date));
            if (ctype_digit($suffix)) {
                $sequence = ((int) $suffix) + 1;
            }
        }

        return $prefix . $date . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
