<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
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
