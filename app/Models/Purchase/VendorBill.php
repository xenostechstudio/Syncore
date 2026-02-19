<?php

namespace App\Models\Purchase;

use App\Enums\VendorBillState;
use App\Models\User;
use App\Traits\HasAttachments;
use App\Traits\HasCreatedBy;
use App\Traits\HasNotes;
use App\Traits\HasSoftDeletes;
use App\Traits\HasStateMachine;
use App\Traits\HasYearlySequenceNumber;
use App\Traits\LogsActivity;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorBill extends Model
{
    use LogsActivity, HasNotes, HasSoftDeletes, Searchable, HasAttachments, HasStateMachine, HasCreatedBy, HasYearlySequenceNumber;

    protected string $stateEnum = VendorBillState::class;

    public const NUMBER_PREFIX = 'BILL';
    public const NUMBER_COLUMN = 'bill_number';
    public const NUMBER_DIGITS = 5;

    protected array $logActions = ['created', 'updated', 'deleted'];
    
    protected array $searchable = ['bill_number', 'vendor_reference', 'notes'];

    protected $fillable = [
        'bill_number',
        'vendor_reference',
        'supplier_id',
        'purchase_rfq_id',
        'bill_date',
        'due_date',
        'status',
        'subtotal',
        'tax',
        'total',
        'paid_amount',
        'paid_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function confirm(): bool
    {
        if (!$this->state->canConfirm()) {
            return false;
        }
        return $this->transitionTo(VendorBillState::PENDING);
    }

    public function markAsPaid(): bool
    {
        if ($this->state->isTerminal()) {
            return false;
        }
        $this->paid_date = now();
        return $this->transitionTo(VendorBillState::PAID);
    }

    public function markAsPartial(): bool
    {
        if (!$this->state->canRegisterPayment()) {
            return false;
        }
        return $this->transitionTo(VendorBillState::PARTIAL);
    }

    public function markAsOverdue(): bool
    {
        if ($this->state->isTerminal()) {
            return false;
        }
        return $this->transitionTo(VendorBillState::OVERDUE);
    }

    public function cancelBill(): bool
    {
        if (!$this->state->canCancel()) {
            return false;
        }
        return $this->transitionTo(VendorBillState::CANCELLED);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseRfq::class, 'purchase_rfq_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorBillItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(VendorBillPayment::class);
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->paid_amount);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->balance_due > 0;
    }
}
