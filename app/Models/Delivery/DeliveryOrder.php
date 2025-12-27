<?php

namespace App\Models\Delivery;

use App\Enums\DeliveryOrderState;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DeliveryOrder extends Model
{
    use LogsActivity;

    protected $fillable = [
        'delivery_number',
        'sales_order_id',
        'warehouse_id',
        'user_id',
        'delivery_date',
        'actual_delivery_date',
        'status',
        'shipping_address',
        'recipient_name',
        'recipient_phone',
        'notes',
        'tracking_number',
        'courier',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'status' => DeliveryOrderState::class,
    ];

    public function getStateAttribute(): DeliveryOrderState
    {
        return DeliveryOrderState::tryFrom($this->status) ?? DeliveryOrderState::PENDING;
    }

    public function transitionTo(DeliveryOrderState $state): bool
    {
        $this->status = $state->value;
        return $this->save();
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

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
        return $this->hasMany(DeliveryOrderItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(DeliveryReturn::class);
    }

    public static function generateDeliveryNumber(): string
    {
        $prefix = 'DO';
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', today())->latest()->first();
        $sequence = $lastOrder ? (int) substr($lastOrder->delivery_number, -4) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'delivery_number', 'sales_order_id', 'warehouse_id', 'delivery_date',
                'actual_delivery_date', 'status', 'shipping_address', 'recipient_name',
                'recipient_phone', 'notes', 'tracking_number', 'courier',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => __('activity.delivery_order_created'),
                'updated' => __('activity.delivery_order_updated'),
                'deleted' => __('activity.delivery_order_deleted'),
                default => __('activity.delivery_order_event', ['event' => $eventName]),
            });
    }
}
