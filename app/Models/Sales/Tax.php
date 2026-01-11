<?php

namespace App\Models\Sales;

use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use LogsActivity, HasNotes;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'name',
        'code',
        'rate',
        'type',
        'scope',
        'is_active',
        'include_in_price',
        'description',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_active' => 'boolean',
        'include_in_price' => 'boolean',
    ];

    public function getFormattedRateAttribute(): string
    {
        return $this->type === 'percentage' 
            ? number_format($this->rate, 2) . '%'
            : 'Rp ' . number_format($this->rate, 0, ',', '.');
    }
}
