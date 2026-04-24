<?php

namespace App\Models\CRM;

use App\Enums\OpportunityState;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Traits\HasAttachments;
use App\Traits\HasNotes;
use App\Traits\HasSoftDeletes;
use App\Traits\HasStateMachine;
use App\Traits\LogsActivity;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Opportunity extends Model
{
    use LogsActivity, HasNotes, Searchable, HasSoftDeletes, HasAttachments, HasStateMachine;

    protected string $stateEnum = OpportunityState::class;
    protected string $statusColumn = 'status';

    protected array $logActions = ['created', 'updated', 'deleted'];
    
    protected array $searchable = ['name', 'description'];

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
        'status',
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
        'status' => OpportunityState::class,
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

    public function markAsWon(?int $salesOrderId = null): bool
    {
        if ($this->isWon() || $this->isLost()) {
            return false;
        }

        $wonStage = Pipeline::where('is_won', true)->first();
        
        $this->update([
            'pipeline_id' => $wonStage?->id ?? $this->pipeline_id,
            'probability' => 100,
            'won_at' => now(),
            'sales_order_id' => $salesOrderId,
        ]);

        // Use state machine for status transition
        $this->transitionTo(OpportunityState::WON, 'Opportunity marked as won');

        return true;
    }

    public function markAsLost(string $reason = ''): bool
    {
        if ($this->isWon() || $this->isLost()) {
            return false;
        }

        $lostStage = Pipeline::where('is_lost', true)->first();
        
        $this->update([
            'pipeline_id' => $lostStage?->id ?? $this->pipeline_id,
            'probability' => 0,
            'lost_at' => now(),
            'lost_reason' => $reason,
        ]);

        // Use state machine for status transition
        $this->transitionTo(OpportunityState::LOST, $reason ?: 'Opportunity marked as lost');

        return true;
    }

    public function moveToPipeline(Pipeline $pipeline): bool
    {
        if ($this->isWon() || $this->isLost()) {
            return false;
        }

        $oldPipelineId = $this->pipeline_id;
        
        $this->update([
            'pipeline_id' => $pipeline->id,
            'probability' => $pipeline->probability,
        ]);

        $this->logStatusChange(
            "pipeline:{$oldPipelineId}",
            "pipeline:{$pipeline->id}",
            "Moved to stage: {$pipeline->name}"
        );

        return true;
    }

    public function isWon(): bool
    {
        return $this->status === OpportunityState::WON;
    }

    public function isLost(): bool
    {
        return $this->status === OpportunityState::LOST;
    }

    public function isOpen(): bool
    {
        return $this->status === OpportunityState::OPEN;
    }
}
