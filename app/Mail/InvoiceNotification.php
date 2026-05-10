<?php

namespace App\Mail;

use App\Models\Invoicing\Invoice;
use App\Services\PdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

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
        // Build the customer-facing link from share_token if present. Previous
        // implementation read $invoice->public_url which never existed on the
        // model — every email shipped without a "View Invoice" button.
        $publicUrl = null;
        if ($this->invoice->share_token) {
            $publicUrl = URL::signedRoute('public.invoices.show', [
                'token' => $this->invoice->share_token,
            ]);
        }

        return new Content(
            markdown: 'emails.invoice-notification',
            with: [
                'invoice'       => $this->invoice,
                'customer'      => $this->invoice->customer,
                'publicUrl'     => $publicUrl,
                'customMessage' => $this->customMessage,
            ],
        );
    }

    /**
     * Attach the invoice PDF. The share-modal copy promises "Please find
     * attached invoice…" — without this, the email shipped that text plus
     * no attachment, which customers reasonably read as the email being
     * broken.
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => PdfService::renderInvoice($this->invoice),
                "Invoice-{$this->invoice->invoice_number}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
