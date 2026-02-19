<?php

namespace App\Services;

use App\Enums\LeadState;
use App\Events\OpportunityLost;
use App\Events\OpportunityWon;
use App\Models\CRM\Activity;
use App\Models\CRM\Lead;
use App\Models\CRM\Opportunity;
use App\Models\CRM\Pipeline;
use App\Models\Sales\Customer;
use Illuminate\Support\Facades\DB;

/**
 * CRM Service
 * 
 * Centralized business logic for CRM operations.
 */
class CRMService
{
    /**
     * Create a new lead.
     */
    public function createLead(array $data): Lead
    {
        return Lead::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'website' => $data['website'] ?? null,
            'address' => $data['address'] ?? null,
            'source' => $data['source'] ?? 'other',
            'status' => 'new',
            'notes' => $data['notes'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? auth()->id(),
        ]);
    }

    /**
     * Update lead status.
     */
    public function updateLeadStatus(Lead $lead, string $status): bool
    {
        $state = LeadState::tryFrom($status);
        if (!$state) {
            return false;
        }

        return $lead->transitionTo($state);
    }

    /**
     * Convert lead to customer.
     */
    public function convertLeadToCustomer(Lead $lead): ?Customer
    {
        return DB::transaction(function () use ($lead) {
            $customer = $lead->convertToCustomer();
            
            if ($customer) {
                $lead->logActivity('converted', "Lead converted to customer: {$customer->name}");
            }

            return $customer;
        });
    }

    /**
     * Mark lead as lost.
     */
    public function markLeadAsLost(Lead $lead, ?string $reason = null): bool
    {
        return $lead->markAsLost($reason);
    }

    /**
     * Create opportunity from lead.
     */
    public function createOpportunityFromLead(Lead $lead, array $data = []): Opportunity
    {
        $defaultPipeline = Pipeline::orderBy('sequence')->first();

        return Opportunity::create([
            'name' => $data['name'] ?? "Opportunity from {$lead->name}",
            'customer_id' => $lead->converted_customer_id,
            'lead_id' => $lead->id,
            'pipeline_id' => $data['pipeline_id'] ?? $defaultPipeline?->id,
            'expected_revenue' => $data['expected_revenue'] ?? 0,
            'probability' => $data['probability'] ?? $defaultPipeline?->probability ?? 10,
            'expected_close_date' => $data['expected_close_date'] ?? now()->addDays(30),
            'description' => $data['description'] ?? $lead->notes,
            'assigned_to' => $data['assigned_to'] ?? $lead->assigned_to ?? auth()->id(),
        ]);
    }

    /**
     * Create a new opportunity.
     */
    public function createOpportunity(array $data): Opportunity
    {
        $pipeline = Pipeline::find($data['pipeline_id']);

        return Opportunity::create([
            'name' => $data['name'],
            'customer_id' => $data['customer_id'],
            'lead_id' => $data['lead_id'] ?? null,
            'pipeline_id' => $data['pipeline_id'],
            'expected_revenue' => $data['expected_revenue'] ?? 0,
            'probability' => $data['probability'] ?? $pipeline?->probability ?? 10,
            'expected_close_date' => $data['expected_close_date'] ?? null,
            'description' => $data['description'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? auth()->id(),
        ]);
    }

    /**
     * Move opportunity to next pipeline stage.
     */
    public function moveToNextStage(Opportunity $opportunity): bool
    {
        if ($opportunity->isWon() || $opportunity->isLost()) {
            return false;
        }

        $currentPipeline = $opportunity->pipeline;
        $nextPipeline = Pipeline::where('sequence', '>', $currentPipeline->sequence)
            ->where('is_won', false)
            ->where('is_lost', false)
            ->orderBy('sequence')
            ->first();

        if (!$nextPipeline) {
            return false;
        }

        return $opportunity->moveToPipeline($nextPipeline);
    }

    /**
     * Mark opportunity as won.
     */
    public function markOpportunityAsWon(Opportunity $opportunity, ?int $salesOrderId = null): bool
    {
        $result = $opportunity->markAsWon($salesOrderId);

        if ($result) {
            // Dispatch event for notifications
            event(new OpportunityWon($opportunity));
        }

        return $result;
    }

    /**
     * Mark opportunity as lost.
     */
    public function markOpportunityAsLost(Opportunity $opportunity, string $reason = ''): bool
    {
        $result = $opportunity->markAsLost($reason);

        if ($result) {
            // Dispatch event for notifications
            event(new OpportunityLost($opportunity, $reason));
        }

        return $result;
    }

    /**
     * Log an activity for a model.
     */
    public function logActivity(
        string $type,
        string $subject,
        $model,
        ?string $description = null,
        ?\DateTime $scheduledAt = null
    ): Activity {
        return Activity::create([
            'type' => $type,
            'subject' => $subject,
            'description' => $description,
            'activitable_type' => get_class($model),
            'activitable_id' => $model->id,
            'user_id' => auth()->id(),
            'scheduled_at' => $scheduledAt,
            'completed_at' => $scheduledAt ? null : now(),
        ]);
    }

    /**
     * Get pipeline statistics.
     */
    public function getPipelineStats(): array
    {
        $pipelines = Pipeline::withCount(['opportunities' => function ($q) {
            $q->whereNull('won_at')->whereNull('lost_at');
        }])
        ->withSum(['opportunities' => function ($q) {
            $q->whereNull('won_at')->whereNull('lost_at');
        }], 'expected_revenue')
        ->orderBy('sequence')
        ->get();

        return $pipelines->map(function ($pipeline) {
            return [
                'id' => $pipeline->id,
                'name' => $pipeline->name,
                'count' => $pipeline->opportunities_count,
                'value' => $pipeline->opportunities_sum_expected_revenue ?? 0,
                'weighted_value' => ($pipeline->opportunities_sum_expected_revenue ?? 0) * ($pipeline->probability / 100),
            ];
        })->toArray();
    }
}
