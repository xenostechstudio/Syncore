<?php

namespace App\Models\Inventory;

use App\Enums\TransferState;
use App\Models\User;
use App\Traits\HasNotes;
use App\Traits\HasSoftDeletes;
use App\Traits\HasStateMachine;
use App\Traits\HasYearlySequenceNumber;
use App\Traits\LogsActivity;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryTransfer extends Model
{
    use LogsActivity, HasNotes, HasSoftDeletes, Searchable, HasStateMachine, HasYearlySequenceNumber;

    protected string $stateEnum = TransferState::class;

    public const NUMBER_PREFIX = 'TRF';
    public const NUMBER_COLUMN = 'transfer_number';
    public const NUMBER_DIGITS = 5;

    protected array $logActions = ['created', 'updated', 'deleted'];
    
    protected array $searchable = ['transfer_number', 'notes'];

    protected $fillable = [
        'transfer_number',
        'source_warehouse_id',
        'destination_warehouse_id',
        'user_id',
        'transfer_date',
        'expected_arrival_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'expected_arrival_date' => 'date',
    ];

    public function markReady(): bool
    {
        if ($this->state !== TransferState::DRAFT) {
            return false;
        }
        return $this->transitionTo(TransferState::READY);
    }

    public function markInTransit(): bool
    {
        if ($this->state !== TransferState::READY) {
            return false;
        }
        return $this->transitionTo(TransferState::IN_TRANSIT);
    }

    public function complete(): bool
    {
        if ($this->state !== TransferState::IN_TRANSIT) {
            return false;
        }
        return $this->transitionTo(TransferState::COMPLETED);
    }

    public function cancelTransfer(): bool
    {
        if (!$this->state->canCancel()) {
            return false;
        }
        return $this->transitionTo(TransferState::CANCELLED);
    }

    /**
     * Advance to the next state in the workflow.
     */
    public function advanceState(): bool
    {
        $nextState = $this->state->next();
        if (!$nextState) {
            return false;
        }
        return $this->transitionTo($nextState);
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryTransferItem::class);
    }
}
