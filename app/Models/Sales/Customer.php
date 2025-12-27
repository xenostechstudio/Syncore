<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
{
    use LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'type', 'name', 'email', 'phone', 'address', 'city', 'country',
                'notes', 'salesperson_id', 'payment_term_id', 'payment_method',
                'pricelist_id', 'status',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => __('activity.customer_created'),
                'updated' => __('activity.customer_updated'),
                'deleted' => __('activity.customer_deleted'),
                default => __('activity.customer_event', ['event' => $eventName]),
            });
    }
}
