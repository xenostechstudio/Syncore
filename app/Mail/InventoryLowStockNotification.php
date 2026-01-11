<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class InventoryLowStockNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Collection $products,
        public ?string $warehouseName = null
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->warehouseName
            ? "Low Stock Alert: {$this->products->count()} products in {$this->warehouseName}"
            : "Low Stock Alert: {$this->products->count()} products need attention";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.inventory-low-stock',
            with: [
                'products' => $this->products,
                'warehouseName' => $this->warehouseName,
                'totalProducts' => $this->products->count(),
                'outOfStockCount' => $this->products->where('quantity', '<=', 0)->count(),
            ],
        );
    }
}
