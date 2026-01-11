<?php

namespace App\Models\Inventory;

use App\Enums\TransferState;
use App\Models\User;
use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryTransfer extends Model
{
    use LogsActivity, HasNotes;

    protected array $logActions = ['created', 'updated', 'deleted'];

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

    public function getStateAttribute(): TransferState
    {
        return TransferState::tryFrom($this->status) ?? TransferState::DRAFT;
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

    public static function generateTransferNumber(): string
    {
        $prefix = 'TRF';
        $year = now()->year;
        
        $lastNumber = self::where('transfer_number', 'like', "{$prefix}/{$year}/%")
            ->pluck('transfer_number')
            ->map(fn($num) => (int) substr($num, strlen("{$prefix}/{$year}/")))
            ->max() ?? 0;

        return "{$prefix}/{$year}/" . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }
}
