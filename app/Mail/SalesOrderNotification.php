<?php

namespace App\Mail;

use App\Models\Sales\SalesOrder;
use App\Services\PdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * Customer-facing email for quotations and confirmed sales orders. Mirrors
 * InvoiceNotification's shape so both customer-facing channels (invoice,
 * quotation/SO) share one pattern: optional custom subject + custom message,
 * optional PDF attachment, signed share-link button.
 */
class SalesOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SalesOrder $order,
        public ?string $customSubject = null,
        public ?string $customMessage = null,
        public bool $attachPdf = true
    ) {}

    public function envelope(): Envelope
    {
        $isQuotation = in_array($this->order->status, ['draft', 'confirmed']);
        $documentType = $isQuotation ? 'Quotation' : 'Sales Order';

        return new Envelope(
            subject: $this->customSubject ?? "{$documentType} {$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        $publicUrl = null;
        if ($this->order->share_token) {
            $publicUrl = URL::signedRoute('public.sales-orders.show', [
                'token' => $this->order->share_token,
            ]);
        }

        $isQuotation = in_array($this->order->status, ['draft', 'confirmed']);

        return new Content(
            markdown: 'emails.sales-order-notification',
            with: [
                'order'         => $this->order,
                'customer'      => $this->order->customer,
                'publicUrl'     => $publicUrl,
                'customMessage' => $this->customMessage,
                'documentType'  => $isQuotation ? 'Quotation' : 'Sales Order',
            ],
        );
    }

    public function attachments(): array
    {
        if (! $this->attachPdf) {
            return [];
        }

        $isQuotation = in_array($this->order->status, ['draft', 'confirmed']);
        $docType = $isQuotation ? 'Quotation' : 'SalesOrder';

        return [
            Attachment::fromData(
                fn () => PdfService::renderSalesOrder($this->order),
                "{$docType}-{$this->order->order_number}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
