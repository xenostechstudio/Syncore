<?php

namespace App\Listeners\Notification;

use App\Events\InvoiceOverdue;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendInvoiceOverdueAlert implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(InvoiceOverdue $event): void
    {
        $invoice = $event->invoice;
        $daysOverdue = now()->diffInDays($invoice->due_date);

        NotificationService::create(
            type: 'invoice_overdue',
            title: 'Invoice Overdue',
            message: "Invoice {$invoice->invoice_number} for {$invoice->customer->name} is {$daysOverdue} days overdue. Amount: " . number_format($invoice->total, 2),
            notifiable: $invoice,
            data: [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'days_overdue' => $daysOverdue,
                'amount' => $invoice->total,
            ]
        );
    }
}
