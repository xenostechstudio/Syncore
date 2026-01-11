<?php

namespace App\Listeners\CRM;

use App\Events\OpportunityLost;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOpportunityLostNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(OpportunityLost $event): void
    {
        $opportunity = $event->opportunity;

        $message = "Opportunity '{$opportunity->name}' has been marked as lost.";
        if ($event->reason) {
            $message .= " Reason: {$event->reason}";
        }

        NotificationService::create(
            type: 'opportunity_lost',
            title: 'Opportunity Lost',
            message: $message,
            notifiable: $opportunity,
            userId: $opportunity->assigned_to,
            data: [
                'opportunity_id' => $opportunity->id,
                'customer_id' => $opportunity->customer_id,
                'expected_revenue' => $opportunity->expected_revenue,
                'reason' => $event->reason,
            ]
        );
    }
}
