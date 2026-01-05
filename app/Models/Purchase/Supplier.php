<?php

namespace App\Models\Purchase;

use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Supplier extends Model
{
    use LogsActivity, HasNotes;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'contact_person', 'email', 'phone',
                'address', 'city', 'country', 'is_active',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => __('activity.supplier_created'),
                'updated' => __('activity.supplier_updated'),
                'deleted' => __('activity.supplier_deleted'),
                default => __('activity.supplier_event', ['event' => $eventName]),
            });
    }
}
