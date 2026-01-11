<?php

namespace App\Models\Sales;

use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use LogsActivity, HasNotes;

    protected array $logActions = ['created', 'updated', 'deleted'];

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
