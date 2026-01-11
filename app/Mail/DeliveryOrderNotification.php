<?php

namespace App\Mail;

use App\Models\Delivery\DeliveryOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DeliveryOrder $deliveryOrder
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Delivery Order #' . $this->deliveryOrder->delivery_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.delivery-order-notification',
            with: [
                'deliveryOrder' => $this->deliveryOrder,
                'customer' => $this->deliveryOrder->salesOrder?->customer,
                'items' => $this->deliveryOrder->items,
            ],
        );
    }
}
