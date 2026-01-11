<?php

namespace App\Events;

use App\Models\Purchase\VendorBill;
use App\Models\Purchase\VendorBillPayment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VendorBillPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public VendorBill $vendorBill,
        public VendorBillPayment $payment
    ) {}
}
