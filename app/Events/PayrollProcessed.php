<?php

namespace App\Events;

use App\Models\HR\PayrollPeriod;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayrollProcessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PayrollPeriod $payrollPeriod
    ) {}
}
