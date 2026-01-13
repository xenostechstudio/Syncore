<?php

namespace App\Models\Sales;

use App\Traits\HasNotes;
use App\Traits\HasSoftDeletes;
use App\Traits\LogsActivity;
use App\Traits\Searchable;
use Database\Factories\Sales\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory, LogsActivity, HasNotes, HasSoftDeletes, Searchable;

    protected array $logActions = ['created', 'updated', 'deleted'];
    
    protected array $searchable = ['name', 'email', 'phone', 'city', 'country'];

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

    public function invoices(): HasMany
    {
        return $this->hasMany(\App\Models\Invoicing\Invoice::class);
    }
}
