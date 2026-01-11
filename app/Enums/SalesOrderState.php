<?php

namespace App\Enums;

enum SalesOrderState: string
{
    // Conceptual states: quotation → quotation sent → sales order → done / cancelled
    // Underlying values stay aligned with the existing database enum on sales_orders.status
    case QUOTATION       = 'draft';      // initial quotation
    case QUOTATION_SENT  = 'confirmed';  // quotation sent to customer
    case SALES_ORDER     = 'processing'; // confirmed as sales order
    case DONE            = 'delivered';  // completed (cannot change)
    case CANCELLED       = 'cancelled';  // cancelled (cannot change)

    public function label(): string
    {
        return match($this) {
            self::QUOTATION      => 'Quotation',
            self::QUOTATION_SENT => 'Quotation Sent',
            self::SALES_ORDER    => 'Sales Order',
            self::DONE           => 'Done',
            self::CANCELLED      => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::QUOTATION      => 'zinc',
            self::QUOTATION_SENT => 'blue',
            self::SALES_ORDER    => 'amber',
            self::DONE           => 'emerald',
            self::CANCELLED      => 'red',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::QUOTATION      => 'document-text',
            self::QUOTATION_SENT => 'paper-airplane',
            self::SALES_ORDER    => 'shopping-cart',
            self::DONE           => 'check-circle',
            self::CANCELLED      => 'x-circle',
        };
    }

    public function canConfirm(): bool
    {
        // "Confirm" moves the document into Sales Order
        return in_array($this, [self::QUOTATION, self::QUOTATION_SENT]);
    }

    public function canCreateInvoice(): bool
    {
        // Only Sales Orders can create invoices
        return $this === self::SALES_ORDER;
    }

    public function canCancel(): bool
    {
        // Cannot cancel once done/cancelled (terminal)
        return in_array($this, [self::QUOTATION, self::QUOTATION_SENT, self::SALES_ORDER]);
    }

    public function canEdit(): bool
    {
        // Editing allowed until the order is done or cancelled
        return in_array($this, [self::QUOTATION, self::QUOTATION_SENT, self::SALES_ORDER]);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::DONE, self::CANCELLED]);
    }

    public static function steps(): array
    {
        return [
            ['key' => self::QUOTATION->value,      'label' => self::QUOTATION->label()],
            ['key' => self::QUOTATION_SENT->value, 'label' => self::QUOTATION_SENT->label()],
            ['key' => self::SALES_ORDER->value,    'label' => self::SALES_ORDER->label()],
            ['key' => self::DONE->value,           'label' => self::DONE->label()],
        ];
    }
}
