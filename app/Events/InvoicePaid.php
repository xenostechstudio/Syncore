<?php

namespace App\Events;

use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoicePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Invoice $invoice,
        public Payment $payment
    ) {}
}
