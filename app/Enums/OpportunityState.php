<?php

namespace App\Enums;

enum OpportunityState: string
{
    case OPEN = 'open';
    case WON = 'won';
    case LOST = 'lost';

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'Open',
            self::WON => 'Won',
            self::LOST => 'Lost',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::OPEN => 'blue',
            self::WON => 'green',
            self::LOST => 'red',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->label()
        ])->toArray();
    }
}
