<?php

namespace App\Models\Purchase;

use App\Traits\HasNotes;
use App\Traits\HasSoftDeletes;
use App\Traits\LogsActivity;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use LogsActivity, HasNotes, HasSoftDeletes, Searchable;

    protected array $logActions = ['created', 'updated', 'deleted'];
    
    protected array $searchable = ['name', 'email', 'contact_person', 'phone'];

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the purchase orders for this supplier.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseRfq::class);
    }

    /**
     * Get the vendor bills for this supplier.
     */
    public function vendorBills(): HasMany
    {
        return $this->hasMany(VendorBill::class);
    }

    /**
     * Scope to get only active suppliers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
