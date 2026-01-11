<?php

namespace App\Enums;

enum AdjustmentState: string
{
    case DRAFT = 'draft';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'zinc',
            self::COMPLETED => 'emerald',
            self::CANCELLED => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'pencil-square',
            self::COMPLETED => 'check-circle',
            self::CANCELLED => 'x-circle',
        };
    }

    public function canValidate(): bool
    {
        return $this === self::DRAFT;
    }

    public function canEdit(): bool
    {
        return $this === self::DRAFT;
    }

    public function canCancel(): bool
    {
        return $this === self::DRAFT;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED]);
    }

    public static function steps(): array
    {
        return [
            ['key' => self::DRAFT->value, 'label' => self::DRAFT->label()],
            ['key' => self::COMPLETED->value, 'label' => self::COMPLETED->label()],
        ];
    }
}
