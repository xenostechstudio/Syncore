<?php

namespace App\Listeners\Purchase;

use App\Events\VendorBillPaid;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendVendorBillPaidNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(VendorBillPaid $event): void
    {
        $bill = $event->vendorBill;
        $payment = $event->payment;

        NotificationService::create(
            type: 'vendor_bill_paid',
            title: 'Vendor Bill Payment Recorded',
            message: "Payment of " . number_format($payment->amount, 2) . " recorded for bill {$bill->bill_number} from {$bill->supplier?->name}. Remaining balance: " . number_format($bill->balance_due, 2),
            notifiable: $bill,
            data: [
                'vendor_bill_id' => $bill->id,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'balance_due' => $bill->balance_due,
            ]
        );
    }
}
