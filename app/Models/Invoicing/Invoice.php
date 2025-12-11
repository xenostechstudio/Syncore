<?php

namespace App\Models\Invoicing;

use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'sales_order_id',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes',
        'terms',
        'paid_amount',
        'paid_date',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $year = now()->year;
                $prefix = "INV/{$year}/";

                // Get all invoice numbers for the current year and compute the next numeric suffix in PHP
                $lastNumber = static::where('invoice_number', 'like', $prefix . '%')
                    ->pluck('invoice_number')
                    ->map(function (string $number) use ($prefix) {
                        return (int) substr($number, strlen($prefix));
                    })
                    ->max() ?? 0;

                $nextNumber = $lastNumber + 1;

                $invoice->invoice_number = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
