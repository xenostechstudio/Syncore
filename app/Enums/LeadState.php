<?php

namespace App\Enums;

enum LeadState: string
{
    case NEW = 'new';
    case CONTACTED = 'contacted';
    case QUALIFIED = 'qualified';
    case PROPOSAL = 'proposal';
    case WON = 'won';
    case LOST = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::CONTACTED => 'Contacted',
            self::QUALIFIED => 'Qualified',
            self::PROPOSAL => 'Proposal',
            self::WON => 'Won',
            self::LOST => 'Lost',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'zinc',
            self::CONTACTED => 'blue',
            self::QUALIFIED => 'violet',
            self::PROPOSAL => 'amber',
            self::WON => 'emerald',
            self::LOST => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::NEW => 'sparkles',
            self::CONTACTED => 'phone',
            self::QUALIFIED => 'star',
            self::PROPOSAL => 'document-text',
            self::WON => 'trophy',
            self::LOST => 'x-circle',
        };
    }

    public function canConvert(): bool
    {
        return in_array($this, [self::QUALIFIED, self::PROPOSAL]);
    }

    public function canEdit(): bool
    {
        return !$this->isTerminal();
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::WON, self::LOST]);
    }

    public static function steps(): array
    {
        return [
            ['key' => self::NEW->value, 'label' => self::NEW->label()],
            ['key' => self::CONTACTED->value, 'label' => self::CONTACTED->label()],
            ['key' => self::QUALIFIED->value, 'label' => self::QUALIFIED->label()],
            ['key' => self::PROPOSAL->value, 'label' => self::PROPOSAL->label()],
            ['key' => self::WON->value, 'label' => self::WON->label()],
        ];
    }
}
