<?php

namespace App\Events;

use App\Models\CRM\Opportunity;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OpportunityWon
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Opportunity $opportunity
    ) {}
}
