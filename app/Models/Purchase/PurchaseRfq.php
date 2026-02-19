<?php

namespace App\Models\Purchase;

use App\Enums\PurchaseOrderState;
use App\Models\User;
use App\Traits\HasAttachments;
use App\Traits\HasNotes;
use App\Traits\HasSoftDeletes;
use App\Traits\HasStateMachine;
use App\Traits\HasYearlySequenceNumber;
use App\Traits\LogsActivity;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRfq extends Model
{
    use LogsActivity, HasNotes, HasSoftDeletes, HasYearlySequenceNumber, Searchable, HasAttachments, HasStateMachine;

    protected string $stateEnum = PurchaseOrderState::class;

    public const NUMBER_PREFIX = 'RFQ';
    public const NUMBER_COLUMN = 'reference';
    public const NUMBER_DIGITS = 5;

    protected array $logActions = ['created', 'updated', 'deleted'];
    
    protected array $searchable = ['reference', 'supplier_name', 'notes'];

    protected $table = 'purchase_rfqs';

    protected $fillable = [
        'reference',
        'supplier_id',
        'supplier_name',
        'order_date',
        'expected_arrival',
        'status',
        'subtotal',
        'tax',
        'total',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_arrival' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function sendRfq(): bool
    {
        if (!$this->state->canSendRfq()) {
            return false;
        }
        return $this->transitionTo(PurchaseOrderState::RFQ_SENT);
    }

    public function confirmOrder(): bool
    {
        if (!$this->state->canConfirmOrder()) {
            return false;
        }
        return $this->transitionTo(PurchaseOrderState::PURCHASE_ORDER);
    }

    public function markAsReceived(): bool
    {
        if (!$this->state->canReceive()) {
            return false;
        }
        return $this->transitionTo(PurchaseOrderState::RECEIVED);
    }

    public function markAsBilled(): bool
    {
        if (!$this->state->canCreateBill()) {
            return false;
        }
        return $this->transitionTo(PurchaseOrderState::BILLED);
    }

    public function cancelOrder(): bool
    {
        if (!$this->state->canCancel()) {
            return false;
        }
        return $this->transitionTo(PurchaseOrderState::CANCELLED);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRfqItem::class, 'purchase_rfq_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
