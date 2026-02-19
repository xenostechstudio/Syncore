<?php

namespace App\Models\Sales;

use App\Enums\SalesOrderState;
use App\Models\Delivery\DeliveryOrder;
use App\Models\Invoicing\Invoice;
use App\Models\Sales\Pricelist;
use App\Models\Sales\Promotion;
use App\Models\User;
use App\Traits\HasAttachments;
use App\Traits\HasNotes;
use App\Traits\HasSequenceNumber;
use App\Traits\HasSoftDeletes;
use App\Traits\HasStateMachine;
use App\Traits\LogsActivity;
use App\Traits\Searchable;
use Database\Factories\Sales\SalesOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class SalesOrder extends Model
{
    /** @use HasFactory<SalesOrderFactory> */
    use HasFactory, LogsActivity, HasNotes, HasSoftDeletes, Searchable, HasSequenceNumber, HasAttachments, HasStateMachine;

    protected string $stateEnum = SalesOrderState::class;

    public const NUMBER_PREFIX = 'SO';
    public const NUMBER_COLUMN = 'order_number';
    public const NUMBER_DIGITS = 5;
    
    protected array $logActions = ['created', 'updated', 'deleted'];
    
    protected array $searchable = ['order_number', 'shipping_address', 'notes'];
    protected $fillable = [
        'order_number',
        'customer_id',
        'user_id',
        'pricelist_id',
        'promotion_id',
        'promotion_code',
        'promotion_discount',
        'order_date',
        'expected_delivery_date',
        'status',
        'payment_terms',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes',
        'terms',
        'shipping_address',
        'share_token',
        'share_token_expires_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'promotion_discount' => 'decimal:2',
        'total' => 'decimal:2',
        'share_token_expires_at' => 'datetime',
    ];

    public function confirm(): bool
    {
        if (!$this->state->canConfirm()) {
            return false;
        }
        return $this->transitionTo(SalesOrderState::SALES_ORDER);
    }

    public function lock(): bool
    {
        // Locking an order conceptually means marking it as done (delivered)
        if ($this->state !== SalesOrderState::SALES_ORDER) {
            return false;
        }

        return $this->transitionTo(SalesOrderState::DONE);
    }

    public function cancelOrder(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }
        return $this->transitionTo(SalesOrderState::CANCELLED);
    }

    public function canBeCancelled(?string &$reason = null): bool
    {
        if (!$this->state->canCancel()) {
            $reason = 'Current status does not allow cancellation.';
            return false;
        }

        if ($this->invoices()->where('status', '!=', 'cancelled')->exists()) {
            $reason = 'Cannot cancel order with active invoices.';
            return false;
        }

        if ($this->deliveryOrders()->where('status', '!=', 'cancelled')->exists()) {
            $reason = 'Cannot cancel order with active delivery orders.';
            return false;
        }

        return true;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function pricelist(): BelongsTo
    {
        return $this->belongsTo(Pricelist::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Check if there are items remaining to be invoiced
     */
    public function hasQuantityToInvoice(): bool
    {
        return $this->items->contains(fn($item) => $item->quantity_to_invoice > 0);
    }

    /**
     * Check if there are items remaining to be delivered
     */
    public function hasQuantityToDeliver(): bool
    {
        return $this->items->contains(fn($item) => $item->quantity_to_deliver > 0);
    }

    /**
     * Check if there's an active (non-cancelled, non-delivered) delivery order
     */
    public function hasActiveDeliveryOrder(): bool
    {
        return $this->deliveryOrders()
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->exists();
    }

    /**
     * Check if a new delivery order can be created
     * - No active (pending/picked/in_transit) DO exists
     * - There are items remaining to deliver
     */
    public function canCreateDeliveryOrder(): bool
    {
        if ($this->hasActiveDeliveryOrder()) {
            return false;
        }
        return $this->hasQuantityToDeliver();
    }

    /**
     * Check if all items are fully invoiced
     */
    public function isFullyInvoiced(): bool
    {
        return $this->items->every(fn($item) => $item->isFullyInvoiced());
    }

    /**
     * Check if all items are fully delivered
     */
    public function isFullyDelivered(): bool
    {
        return $this->items->every(fn($item) => $item->isFullyDelivered());
    }

    /**
     * Get total quantity to invoice across all items
     */
    public function getTotalQuantityToInvoiceAttribute(): int
    {
        return $this->items->sum(fn($item) => $item->quantity_to_invoice);
    }

    /**
     * Get total quantity to deliver across all items
     */
    public function getTotalQuantityToDeliverAttribute(): int
    {
        return $this->items->sum(fn($item) => $item->quantity_to_deliver);
    }

    /**
     * Check if the order is locked (has active invoices or delivery orders)
     * A locked order cannot have its items modified
     */
    public function isLocked(): bool
    {
        // Check for non-cancelled invoices
        $hasActiveInvoices = $this->invoices()
            ->where('status', '!=', 'cancelled')
            ->exists();

        // Check for non-cancelled delivery orders
        $hasActiveDeliveries = $this->deliveryOrders()
            ->where('status', '!=', 'cancelled')
            ->exists();

        return $hasActiveInvoices || $hasActiveDeliveries;
    }

    /**
     * Check if order items can be edited
     */
    public function canEditItems(): bool
    {
        // Cannot edit if terminal state or locked
        if ($this->state->isTerminal()) {
            return false;
        }

        return !$this->isLocked();
    }

    /**
     * Ensure a share token exists for public preview
     */
    public function ensureShareToken(bool $forceRefresh = false): void
    {
        if (
            $forceRefresh
            || blank($this->share_token)
            || ($this->share_token_expires_at && $this->share_token_expires_at->isPast())
        ) {
            $this->share_token = Str::random(48);
            $this->share_token_expires_at = now()->addDays(30);
            $this->save();
        }
    }

}
