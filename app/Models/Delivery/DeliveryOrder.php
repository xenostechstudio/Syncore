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
        'share_token',
        'share_token_expires_at',
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
        // Proof of Delivery
        'signature_image',
        'delivery_photo',
        'received_by',
        // Delivery Instructions
        'delivery_instructions',
        'preferred_time_slot',
        // Delivery Attempts
        'delivery_attempts',
        'last_attempt_at',
        'failure_reason',
        // Performance Tracking
        'picked_at',
        'shipped_at',
        'delivered_at',
        // Costs
        'shipping_cost',
        'insurance_amount',
        // Customer Feedback
        'customer_rating',
        'customer_feedback',
        // Partial Delivery
        'is_partial',
        'parent_delivery_id',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        // Note: 'status' is managed by HasStateMachine trait via getStateAttribute()
        // Do NOT cast status to enum here — it conflicts with HasStateMachine::getStateAttribute()
        'last_attempt_at' => 'datetime',
        'picked_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'share_token_expires_at' => 'datetime',
        'shipping_cost' => 'decimal:2',
        'insurance_amount' => 'decimal:2',
        'is_partial' => 'boolean',
        'delivery_attempts' => 'integer',
        'customer_rating' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deliveryOrder) {
            if (!$deliveryOrder->share_token) {
                $deliveryOrder->share_token = \Illuminate\Support\Str::random(32);
            }
        });
    }

    public function markAsPicked(): bool
    {
        if ($this->state !== DeliveryOrderState::PENDING) {
            return false;
        }
        $this->picked_at = now();
        $this->save();
        return $this->transitionTo(DeliveryOrderState::PICKED);
    }

    public function markInTransit(): bool
    {
        if ($this->state !== DeliveryOrderState::PICKED) {
            return false;
        }
        $this->shipped_at = now();
        $this->save();
        return $this->transitionTo(DeliveryOrderState::IN_TRANSIT);
    }

    public function markAsDelivered(): bool
    {
        if ($this->state !== DeliveryOrderState::IN_TRANSIT) {
            return false;
        }
        $this->actual_delivery_date = now();
        $this->delivered_at = now();
        $this->save();
        return $this->transitionTo(DeliveryOrderState::DELIVERED);
    }

    public function recordDeliveryAttempt(?string $failureReason = null): void
    {
        $this->increment('delivery_attempts');
        $this->update([
            'last_attempt_at' => now(),
            'failure_reason' => $failureReason,
        ]);
    }

    public function markAsFailed(string $reason): bool
    {
        $this->recordDeliveryAttempt($reason);
        return $this->transitionTo(DeliveryOrderState::FAILED);
    }

    public function recordProofOfDelivery(array $data): bool
    {
        if ($this->state !== DeliveryOrderState::DELIVERED) {
            return false;
        }

        $this->update([
            'signature_image' => $data['signature_image'] ?? null,
            'delivery_photo' => $data['delivery_photo'] ?? null,
            'received_by' => $data['received_by'] ?? null,
        ]);

        return true;
    }

    public function recordCustomerFeedback(int $rating, ?string $feedback = null): bool
    {
        if ($this->state !== DeliveryOrderState::DELIVERED) {
            return false;
        }

        $this->update([
            'customer_rating' => max(1, min(5, $rating)),
            'customer_feedback' => $feedback,
        ]);

        return true;
    }

    public function isOnTime(): bool
    {
        if (!$this->delivered_at || !$this->delivery_date) {
            return false;
        }

        return $this->delivered_at->lte($this->delivery_date->endOfDay());
    }

    public function getDeliveryDuration(): ?int
    {
        if (!$this->picked_at || !$this->delivered_at) {
            return null;
        }

        return $this->picked_at->diffInHours($this->delivered_at);
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

    public function parentDelivery(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'parent_delivery_id');
    }

    public function partialDeliveries(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class, 'parent_delivery_id');
    }
}
