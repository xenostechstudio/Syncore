<?php

namespace App\Enums;

enum LeadState: string
{
    case NEW = 'new';
    case CONTACTED = 'contacted';
    case QUALIFIED = 'qualified';
    case CONVERTED = 'converted';
    case LOST = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::CONTACTED => 'Contacted',
            self::QUALIFIED => 'Qualified',
            self::CONVERTED => 'Converted',
            self::LOST => 'Lost',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'blue',
            self::CONTACTED => 'amber',
            self::QUALIFIED => 'violet',
            self::CONVERTED => 'emerald',
            self::LOST => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::NEW => 'sparkles',
            self::CONTACTED => 'phone',
            self::QUALIFIED => 'star',
            self::CONVERTED => 'check-circle',
            self::LOST => 'x-circle',
        };
    }

    public function canContact(): bool
    {
        return $this === self::NEW;
    }

    public function canQualify(): bool
    {
        return $this === self::CONTACTED;
    }

    public function canConvert(): bool
    {
        return $this === self::QUALIFIED;
    }

    public function canMarkLost(): bool
    {
        return !$this->isTerminal();
    }

    public function canEdit(): bool
    {
        return !$this->isTerminal();
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::CONVERTED, self::LOST]);
    }

    public static function steps(): array
    {
        return [
            ['key' => self::NEW->value, 'label' => self::NEW->label()],
            ['key' => self::CONTACTED->value, 'label' => self::CONTACTED->label()],
            ['key' => self::QUALIFIED->value, 'label' => self::QUALIFIED->label()],
            ['key' => self::CONVERTED->value, 'label' => self::CONVERTED->label()],
        ];
    }
}
