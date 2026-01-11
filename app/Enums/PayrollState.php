<?php

namespace App\Enums;

enum PayrollState: string
{
    case DRAFT = 'draft';
    case APPROVED = 'approved';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::APPROVED => 'Approved',
            self::PROCESSING => 'Processing',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'zinc',
            self::APPROVED => 'blue',
            self::PROCESSING => 'amber',
            self::PAID => 'emerald',
            self::CANCELLED => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'pencil-square',
            self::APPROVED => 'check-badge',
            self::PROCESSING => 'cog',
            self::PAID => 'banknotes',
            self::CANCELLED => 'x-circle',
        };
    }

    public function canApprove(): bool
    {
        return $this === self::DRAFT;
    }

    public function canStartProcessing(): bool
    {
        return $this === self::APPROVED;
    }

    public function canMarkPaid(): bool
    {
        return $this === self::PROCESSING;
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::DRAFT, self::APPROVED]);
    }

    public function canEdit(): bool
    {
        return $this === self::DRAFT;
    }

    public function canResetToDraft(): bool
    {
        return in_array($this, [self::APPROVED, self::CANCELLED]);
    }

    public function isLocked(): bool
    {
        return in_array($this, [self::PROCESSING, self::PAID, self::CANCELLED]);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::PAID, self::CANCELLED]);
    }

    public static function steps(): array
    {
        return [
            ['key' => self::DRAFT->value, 'label' => self::DRAFT->label()],
            ['key' => self::APPROVED->value, 'label' => self::APPROVED->label()],
            ['key' => self::PROCESSING->value, 'label' => self::PROCESSING->label()],
            ['key' => self::PAID->value, 'label' => self::PAID->label()],
        ];
    }
}
