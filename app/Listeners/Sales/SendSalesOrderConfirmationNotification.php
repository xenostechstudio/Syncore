<?php

namespace App\Listeners\Sales;

use App\Events\SalesOrderConfirmed;
use App\Mail\OrderConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendSalesOrderConfirmationNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(SalesOrderConfirmed $event): void
    {
        $salesOrder = $event->salesOrder;
        $customer = $salesOrder->customer;

        if (!$customer || !$customer->email) {
            return;
        }

        Mail::to($customer->email)->send(new OrderConfirmation($salesOrder));
    }
}
