<?php

namespace App\Listeners\Sales;

use App\Events\SalesOrderConfirmed;
use App\Mail\OrderConfirmation;
use App\Models\Settings\SalesOrderSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendSalesOrderConfirmationNotification implements ShouldQueue
{
    /**
     * Send the customer an OrderConfirmation email — but only when the
     * admin has enabled the auto-send-on-confirm setting. Until 2D this
     * listener fired unconditionally, so every confirmed order spammed
     * the customer regardless of admin preference.
     */
    public function handle(SalesOrderConfirmed $event): void
    {
        if (! SalesOrderSetting::instance()->auto_send_on_confirm) {
            return;
        }

        $salesOrder = $event->salesOrder;
        $customer = $salesOrder->customer;

        if (!$customer || !$customer->email) {
            return;
        }

        Mail::to($customer->email)->send(new OrderConfirmation($salesOrder));
    }
}
