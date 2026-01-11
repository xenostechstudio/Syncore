<?php

namespace App\Listeners\CRM;

use App\Events\OpportunityWon;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOpportunityWonNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(OpportunityWon $event): void
    {
        $opportunity = $event->opportunity;

        NotificationService::create(
            type: 'opportunity_won',
            title: '🎉 Opportunity Won!',
            message: "Opportunity '{$opportunity->name}' has been won! Revenue: " . number_format($opportunity->expected_revenue, 2),
            notifiable: $opportunity,
            userId: $opportunity->assigned_to,
            data: [
                'opportunity_id' => $opportunity->id,
                'customer_id' => $opportunity->customer_id,
                'expected_revenue' => $opportunity->expected_revenue,
                'sales_order_id' => $opportunity->sales_order_id,
            ]
        );
    }
}
