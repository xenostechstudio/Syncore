<?php

namespace App\Models\CRM;

use App\Models\Sales\Customer;
use App\Models\User;
use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Lead extends Model
{
    use LogsActivity, HasNotes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company_name',
        'job_title',
        'website',
        'address',
        'source',
        'status',
        'notes',
        'assigned_to',
        'converted_customer_id',
        'converted_at',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
    ];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'activitable');
    }

    public function convertToCustomer(): ?Customer
    {
        if ($this->status === 'converted') {
            return $this->convertedCustomer;
        }

        $customer = Customer::create([
            'name' => $this->company_name ?: $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'website' => $this->website,
            'contact_person' => $this->name,
        ]);

        $this->update([
            'status' => 'converted',
            'converted_customer_id' => $customer->id,
            'converted_at' => now(),
        ]);

        return $customer;
    }

    public static function getSources(): array
    {
        return [
            'website' => 'Website',
            'referral' => 'Referral',
            'cold_call' => 'Cold Call',
            'social_media' => 'Social Media',
            'advertisement' => 'Advertisement',
            'trade_show' => 'Trade Show',
            'email_campaign' => 'Email Campaign',
            'other' => 'Other',
        ];
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'new' => 'blue',
            'contacted' => 'amber',
            'qualified' => 'violet',
            'converted' => 'emerald',
            'lost' => 'red',
            default => 'zinc',
        };
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'email', 'phone', 'company_name', 'job_title',
                'source', 'status', 'assigned_to',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Lead created',
                'updated' => 'Lead updated',
                'deleted' => 'Lead deleted',
                default => "Lead {$eventName}",
            });
    }
}
