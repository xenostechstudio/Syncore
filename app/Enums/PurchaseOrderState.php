<?php

namespace App\Enums;

use App\Enums\Contracts\HasDisplayMetadata;
use App\Enums\Contracts\ProvidesOptions;
enum PurchaseOrderState: string implements HasDisplayMetadata
{
    use ProvidesOptions;

    case RFQ = 'rfq';
    case RFQ_SENT = 'sent';
    case PURCHASE_ORDER = 'purchase_order';
    case PARTIALLY_RECEIVED = 'partially_received';
    case RECEIVED = 'received';
    case BILLED = 'billed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::RFQ => 'RFQ',
            self::RFQ_SENT => 'RFQ Sent',
            self::PURCHASE_ORDER => 'Purchase Order',
            self::PARTIALLY_RECEIVED => 'Partially Received',
            self::RECEIVED => 'Received',
            self::BILLED => 'Billed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::RFQ => 'zinc',
            self::RFQ_SENT => 'blue',
            self::PURCHASE_ORDER => 'amber',
            self::PARTIALLY_RECEIVED => 'orange',
            self::RECEIVED => 'emerald',
            self::BILLED => 'violet',
            self::CANCELLED => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::RFQ => 'document-text',
            self::RFQ_SENT => 'paper-airplane',
            self::PURCHASE_ORDER => 'shopping-bag',
            self::PARTIALLY_RECEIVED => 'inbox',
            self::RECEIVED => 'inbox-arrow-down',
            self::BILLED => 'receipt-percent',
            self::CANCELLED => 'x-circle',
        };
    }

    public function canSendRfq(): bool
    {
        return $this === self::RFQ;
    }

    public function canConfirmOrder(): bool
    {
        return in_array($this, [self::RFQ, self::RFQ_SENT]);
    }

    public function canReceive(): bool
    {
        return in_array($this, [self::PURCHASE_ORDER, self::PARTIALLY_RECEIVED]);
    }

    public function canCreateBill(): bool
    {
        return in_array($this, [self::PURCHASE_ORDER, self::PARTIALLY_RECEIVED, self::RECEIVED]);
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::RFQ, self::RFQ_SENT, self::PURCHASE_ORDER]);
    }

    public function canEdit(): bool
    {
        return in_array($this, [self::RFQ, self::RFQ_SENT]);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::BILLED, self::CANCELLED]);
    }

    public static function steps(): array
    {
        return [
            ['key' => self::RFQ->value, 'label' => self::RFQ->label()],
            ['key' => self::RFQ_SENT->value, 'label' => self::RFQ_SENT->label()],
            ['key' => self::PURCHASE_ORDER->value, 'label' => self::PURCHASE_ORDER->label()],
            ['key' => self::RECEIVED->value, 'label' => self::RECEIVED->label()],
        ];
    }
}
