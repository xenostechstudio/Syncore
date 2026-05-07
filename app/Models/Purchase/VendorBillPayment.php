<?php

namespace App\Models\Purchase;

use App\Models\User;
use App\Traits\LogsActivity;
use Database\Factories\Purchase\VendorBillPaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBillPayment extends Model
{
    /** @use HasFactory<VendorBillPaymentFactory> */
    use HasFactory, LogsActivity;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'vendor_bill_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function vendorBill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
