<?php

namespace App\Listeners\Notification;

use App\Events\InvoicePaid;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPaymentReceivedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(InvoicePaid $event): void
    {
        $invoice = $event->invoice;
        $payment = $event->payment;

        NotificationService::create(
            type: 'payment_received',
            title: 'Payment Received',
            message: "Payment of " . number_format($payment->amount, 2) . " received for invoice {$invoice->invoice_number} from {$invoice->customer->name}",
            notifiable: $invoice,
            data: [
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'customer_id' => $invoice->customer_id,
            ]
        );
    }
}
