<?php

namespace App\Models\Purchase;

use App\Enums\VendorBillState;
use App\Models\User;
use App\Traits\HasNotes;
use App\Traits\HasSoftDeletes;
use App\Traits\LogsActivity;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorBill extends Model
{
    use LogsActivity, HasNotes, HasSoftDeletes, Searchable;

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bill) {
            if (empty($bill->bill_number)) {
                $bill->bill_number = self::generateBillNumber();
            }
        });
    }

    public static function generateBillNumber(): string
    {
        $prefix = 'BILL';
        $year = now()->year;
        
        $lastNumber = self::where('bill_number', 'like', "{$prefix}/{$year}/%")
            ->pluck('bill_number')
            ->map(fn($num) => (int) substr($num, strlen("{$prefix}/{$year}/")))
            ->max() ?? 0;

        return "{$prefix}/{$year}/" . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }

    public function getStateAttribute(): VendorBillState
    {
        return VendorBillState::tryFrom($this->status) ?? VendorBillState::DRAFT;
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
