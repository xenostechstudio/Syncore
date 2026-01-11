<?php

namespace App\Enums;

enum OpportunityState: string
{
    case PROSPECTING = 'prospecting';
    case ANALYSIS = 'analysis';
    case PROPOSAL = 'proposal';
    case NEGOTIATION = 'negotiation';
    case WON = 'won';
    case LOST = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::PROSPECTING => 'Prospecting',
            self::ANALYSIS => 'Analysis',
            self::PROPOSAL => 'Proposal',
            self::NEGOTIATION => 'Negotiation',
            self::WON => 'Won',
            self::LOST => 'Lost',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PROSPECTING => 'zinc',
            self::ANALYSIS => 'blue',
            self::PROPOSAL => 'violet',
            self::NEGOTIATION => 'amber',
            self::WON => 'emerald',
            self::LOST => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PROSPECTING => 'magnifying-glass',
            self::ANALYSIS => 'chart-bar',
            self::PROPOSAL => 'document-text',
            self::NEGOTIATION => 'chat-bubble-left-right',
            self::WON => 'trophy',
            self::LOST => 'x-circle',
        };
    }

    public function probability(): int
    {
        return match ($this) {
            self::PROSPECTING => 10,
            self::ANALYSIS => 25,
            self::PROPOSAL => 50,
            self::NEGOTIATION => 75,
            self::WON => 100,
            self::LOST => 0,
        };
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
            ['key' => self::PROSPECTING->value, 'label' => self::PROSPECTING->label()],
            ['key' => self::ANALYSIS->value, 'label' => self::ANALYSIS->label()],
            ['key' => self::PROPOSAL->value, 'label' => self::PROPOSAL->label()],
            ['key' => self::NEGOTIATION->value, 'label' => self::NEGOTIATION->label()],
            ['key' => self::WON->value, 'label' => self::WON->label()],
        ];
    }
}
