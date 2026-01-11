<?php

namespace App\Models\CRM;

use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Opportunity extends Model
{
    use LogsActivity, HasNotes;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'name',
        'customer_id',
        'lead_id',
        'pipeline_id',
        'expected_revenue',
        'probability',
        'expected_close_date',
        'description',
        'assigned_to',
        'sales_order_id',
        'won_at',
        'lost_at',
        'lost_reason',
    ];

    protected $casts = [
        'expected_revenue' => 'decimal:2',
        'probability' => 'decimal:2',
        'expected_close_date' => 'date',
        'won_at' => 'datetime',
        'lost_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'activitable');
    }

    public function getWeightedRevenue(): float
    {
        return $this->expected_revenue * ($this->probability / 100);
    }

    public function markAsWon(?int $salesOrderId = null): void
    {
        $wonStage = Pipeline::where('is_won', true)->first();
        
        $this->update([
            'pipeline_id' => $wonStage?->id ?? $this->pipeline_id,
            'probability' => 100,
            'won_at' => now(),
            'sales_order_id' => $salesOrderId,
        ]);
    }

    public function markAsLost(string $reason = ''): void
    {
        $lostStage = Pipeline::where('is_lost', true)->first();
        
        $this->update([
            'pipeline_id' => $lostStage?->id ?? $this->pipeline_id,
            'probability' => 0,
            'lost_at' => now(),
            'lost_reason' => $reason,
        ]);
    }

    public function isWon(): bool
    {
        return $this->won_at !== null;
    }

    public function isLost(): bool
    {
        return $this->lost_at !== null;
    }

    public function isOpen(): bool
    {
        return !$this->isWon() && !$this->isLost();
    }
}
