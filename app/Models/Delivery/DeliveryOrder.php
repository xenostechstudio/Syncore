<?php

namespace App\Models\Delivery;

use App\Enums\DeliveryOrderState;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Traits\HasAttachments;
use App\Traits\HasNotes;
use App\Traits\HasSequenceNumber;
use App\Traits\HasSoftDeletes;
use App\Traits\HasStateMachine;
use App\Traits\LogsActivity;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryOrder extends Model
{
    use LogsActivity, HasNotes, HasSequenceNumber, HasSoftDeletes, Searchable, HasAttachments, HasStateMachine;

    protected string $stateEnum = DeliveryOrderState::class;

    public const NUMBER_PREFIX = 'DO';
    public const NUMBER_COLUMN = 'delivery_number';
    public const NUMBER_DIGITS = 5;

    protected array $logActions = ['created', 'updated', 'deleted'];
    
    protected array $searchable = ['delivery_number', 'shipping_address', 'recipient_name', 'tracking_number', 'notes'];

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

    public function markAsPicked(): bool
    {
        if ($this->state !== DeliveryOrderState::PENDING) {
            return false;
        }
        return $this->transitionTo(DeliveryOrderState::PICKED);
    }

    public function markInTransit(): bool
    {
        if ($this->state !== DeliveryOrderState::PICKED) {
            return false;
        }
        return $this->transitionTo(DeliveryOrderState::IN_TRANSIT);
    }

    public function markAsDelivered(): bool
    {
        if ($this->state !== DeliveryOrderState::IN_TRANSIT) {
            return false;
        }
        $this->actual_delivery_date = now();
        return $this->transitionTo(DeliveryOrderState::DELIVERED);
    }

    public function cancelDelivery(): bool
    {
        if (!in_array($this->state, [DeliveryOrderState::PENDING, DeliveryOrderState::PICKED])) {
            return false;
        }
        return $this->transitionTo(DeliveryOrderState::CANCELLED);
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
}
