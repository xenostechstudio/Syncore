<?php

namespace App\Mail;

use App\Models\Purchase\PurchaseRfq;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PurchaseRfq $purchaseOrder
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Purchase Order #' . $this->purchaseOrder->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.purchase-order-notification',
            with: [
                'purchaseOrder' => $this->purchaseOrder,
                'supplier' => $this->purchaseOrder->supplier,
                'items' => $this->purchaseOrder->items,
            ],
        );
    }
}
