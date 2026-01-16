<?php

namespace App\Models\Invoicing;

use App\Traits\HasNotes;
use App\Traits\HasYearlySequenceNumber;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, LogsActivity, HasNotes, HasYearlySequenceNumber;

    public const NUMBER_PREFIX = 'PAY';
    public const NUMBER_COLUMN = 'payment_number';
    public const NUMBER_DIGITS = 5;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference',
        'notes',
        'status',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
