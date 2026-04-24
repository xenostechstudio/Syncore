<?php

namespace App\Models\CRM;

use App\Enums\LeadState;
use App\Models\Sales\Customer;
use App\Models\User;
use App\Traits\HasAttachments;
use App\Traits\HasNotes;
use App\Traits\HasSoftDeletes;
use App\Traits\HasStateMachine;
use App\Traits\LogsActivity;
use App\Traits\Searchable;
use Database\Factories\CRM\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory, LogsActivity, HasNotes, Searchable, HasStateMachine, HasSoftDeletes, HasAttachments;

    protected string $stateEnum = LeadState::class;

    protected array $logActions = ['created', 'updated', 'deleted'];
    
    protected array $searchable = ['name', 'email', 'phone', 'company_name'];

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

    public function markAsContacted(): bool
    {
        if (!$this->state->canContact()) {
            return false;
        }
        return $this->transitionTo(LeadState::CONTACTED);
    }

    public function markAsQualified(): bool
    {
        if (!$this->state->canQualify()) {
            return false;
        }
        return $this->transitionTo(LeadState::QUALIFIED);
    }

    public function markAsLost(?string $reason = null): bool
    {
        if (!$this->state->canMarkLost()) {
            return false;
        }
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Lost reason: " . $reason;
        }
        return $this->transitionTo(LeadState::LOST);
    }

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
        if (!$this->state->canConvert()) {
            return null;
        }

        $customer = Customer::create([
            'name' => $this->company_name ?: $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'website' => $this->website,
            'contact_person' => $this->name,
        ]);

        $this->converted_customer_id = $customer->id;
        $this->converted_at = now();
        $this->save();
        $this->transitionTo(LeadState::CONVERTED);

        return $customer;
    }

    public static function getSources(): array
    {
        return [
            'website' => __('crm.source.website'),
            'referral' => __('crm.source.referral'),
            'cold_call' => __('crm.source.cold_call'),
            'social_media' => __('crm.source.social_media'),
            'advertisement' => __('crm.source.advertisement'),
            'trade_show' => __('crm.source.trade_show'),
            'email_campaign' => __('crm.source.email_campaign'),
            'other' => __('crm.source.other'),
        ];
    }

    public function getStatusColor(): string
    {
        return $this->state->color();
    }
}
