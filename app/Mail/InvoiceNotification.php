<?php

namespace App\Mail;

use App\Models\Invoicing\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public ?string $customSubject = null,
        public ?string $customMessage = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->customSubject ?? "Invoice #{$this->invoice->invoice_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoice-notification',
            with: [
                'invoice' => $this->invoice,
                'customer' => $this->invoice->customer,
                'publicUrl' => $this->invoice->public_url ?? null,
                'customMessage' => $this->customMessage,
            ],
        );
    }
}
