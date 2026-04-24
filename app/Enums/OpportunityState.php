<?php

namespace App\Enums;

use App\Enums\Contracts\HasDisplayMetadata;
use App\Enums\Contracts\ProvidesOptions;

enum OpportunityState: string implements HasDisplayMetadata
{
    use ProvidesOptions;

    case OPEN = 'open';
    case WON = 'won';
    case LOST = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::WON => 'Won',
            self::LOST => 'Lost',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OPEN => 'blue',
            self::WON => 'green',
            self::LOST => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::OPEN => 'clock',
            self::WON => 'trophy',
            self::LOST => 'x-circle',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
