<?php

namespace App\Enums;

enum VendorBillState: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case PAID = 'paid';
    case PARTIAL = 'partial';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::PARTIAL => 'Partially Paid',
            self::OVERDUE => 'Overdue',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'zinc',
            self::PENDING => 'blue',
            self::PAID => 'emerald',
            self::PARTIAL => 'amber',
            self::OVERDUE => 'red',
            self::CANCELLED => 'zinc',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'pencil-square',
            self::PENDING => 'clock',
            self::PAID => 'check-circle',
            self::PARTIAL => 'minus-circle',
            self::OVERDUE => 'exclamation-circle',
            self::CANCELLED => 'x-circle',
        };
    }

    public function canConfirm(): bool
    {
        return $this === self::DRAFT;
    }

    public function canRegisterPayment(): bool
    {
        return in_array($this, [self::PENDING, self::PARTIAL, self::OVERDUE]);
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::DRAFT, self::PENDING]);
    }

    public function canEdit(): bool
    {
        return $this === self::DRAFT;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::PAID, self::CANCELLED]);
    }

    public static function steps(): array
    {
        return [
            ['key' => self::DRAFT->value, 'label' => self::DRAFT->label()],
            ['key' => self::PENDING->value, 'label' => self::PENDING->label()],
            ['key' => self::PAID->value, 'label' => self::PAID->label()],
        ];
    }
}
