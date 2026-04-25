<?php

namespace App\Enums;

use App\Enums\Contracts\HasDisplayMetadata;
use App\Enums\Contracts\ProvidesOptions;

enum ProductStockLevel: string implements HasDisplayMetadata
{
    use ProvidesOptions;

    case IN_STOCK = 'in_stock';
    case LOW_STOCK = 'low_stock';
    case OUT_OF_STOCK = 'out_of_stock';

    public function label(): string
    {
        // Localized via lang/{locale}/common.php — keys: in_stock, low_stock, out_of_stock.
        return (string) __('common.' . $this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::IN_STOCK => 'emerald',
            self::LOW_STOCK => 'amber',
            self::OUT_OF_STOCK => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::IN_STOCK => 'check-circle',
            self::LOW_STOCK => 'exclamation-triangle',
            self::OUT_OF_STOCK => 'x-circle',
        };
    }
}
