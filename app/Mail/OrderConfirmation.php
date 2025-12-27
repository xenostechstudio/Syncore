<?php

namespace App\Mail;

use App\Models\Sales\SalesOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SalesOrder $order
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order Confirmation - {$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-confirmation',
            with: [
                'order' => $this->order,
                'customer' => $this->order->customer,
            ],
        );
    }
}
