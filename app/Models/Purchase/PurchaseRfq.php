<?php

namespace App\Models\Purchase;

use App\Enums\PurchaseOrderState;
use App\Models\User;
use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRfq extends Model
{
    use LogsActivity, HasNotes;

    protected array $logActions = ['created', 'updated', 'deleted'];

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($rfq) {
            if (empty($rfq->reference)) {
                $rfq->reference = self::generateReference();
            }
        });
    }

    public static function generateReference(): string
    {
        $prefix = 'RFQ';
        $year = now()->year;
        
        $lastNumber = self::where('reference', 'like', "{$prefix}/{$year}/%")
            ->pluck('reference')
            ->map(fn($ref) => (int) substr($ref, strlen("{$prefix}/{$year}/")))
            ->max() ?? 0;

        return "{$prefix}/{$year}/" . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }

    public function getStateAttribute(): PurchaseOrderState
    {
        return PurchaseOrderState::tryFrom($this->status) ?? PurchaseOrderState::RFQ;
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
