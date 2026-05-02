<?php

namespace App\Models\Purchase;

use App\Enums\PurchaseReceiptState;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Traits\HasAttachments;
use App\Traits\HasNotes;
use App\Traits\HasSoftDeletes;
use App\Traits\HasStateMachine;
use App\Traits\HasYearlySequenceNumber;
use App\Traits\LogsActivity;
use App\Traits\Searchable;
use Database\Factories\Purchase\PurchaseReceiptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReceipt extends Model
{
    /** @use HasFactory<PurchaseReceiptFactory> */
    use HasFactory, LogsActivity, HasNotes, HasSoftDeletes, HasYearlySequenceNumber, Searchable, HasAttachments, HasStateMachine;

    protected string $stateEnum = PurchaseReceiptState::class;

    public const NUMBER_PREFIX = 'GRN';
    public const NUMBER_COLUMN = 'reference';
    public const NUMBER_DIGITS = 5;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected array $searchable = ['reference', 'notes'];

    protected $table = 'purchase_receipts';

    protected $fillable = [
        'reference',
        'purchase_rfq_id',
        'supplier_id',
        'warehouse_id',
        'received_by',
        'received_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function validate(): bool
    {
        if (! $this->state->canValidate()) {
            return false;
        }

        return $this->transitionTo(PurchaseReceiptState::VALIDATED);
    }

    public function cancel(): bool
    {
        if (! $this->state->canCancel()) {
            return false;
        }

        return $this->transitionTo(PurchaseReceiptState::CANCELLED);
    }

    public function purchaseRfq(): BelongsTo
    {
        return $this->belongsTo(PurchaseRfq::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }
}
