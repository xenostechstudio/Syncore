<?php

namespace App\Models\Sales;

use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use Database\Factories\Sales\PaymentTermFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTerm extends Model
{
    /** @use HasFactory<PaymentTermFactory> */
    use HasFactory, LogsActivity, HasNotes;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'name',
        'code',
        'days',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'days' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getFormattedDaysAttribute(): string
    {
        if ($this->days === 0) {
            return 'Immediate';
        }
        return $this->days . ' days';
    }
}
