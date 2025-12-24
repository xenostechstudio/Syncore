<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'type',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'notes',
        'salesperson_id',
        'payment_term_id',
        'payment_method',
        'pricelist_id',
        'banks',
        'status',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }
}
