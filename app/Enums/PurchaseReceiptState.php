<?php

namespace App\Enums;

use App\Enums\Contracts\HasDisplayMetadata;
use App\Enums\Contracts\ProvidesOptions;

enum PurchaseReceiptState: string implements HasDisplayMetadata
{
    use ProvidesOptions;

    case DRAFT = 'draft';
    case VALIDATED = 'validated';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::VALIDATED => 'Validated',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'zinc',
            self::VALIDATED => 'emerald',
            self::CANCELLED => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'pencil-square',
            self::VALIDATED => 'check-circle',
            self::CANCELLED => 'x-circle',
        };
    }

    public function canValidate(): bool
    {
        return $this === self::DRAFT;
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::DRAFT, self::VALIDATED]);
    }

    public function canEdit(): bool
    {
        return $this === self::DRAFT;
    }

    public function isTerminal(): bool
    {
        return $this === self::CANCELLED;
    }
}
