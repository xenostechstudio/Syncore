<?php

namespace App\Helpers;

use App\Models\Currency;
use App\Services\CurrencyService;

/**
 * Currency Helper
 * 
 * Provides convenient helper functions for currency operations in views.
 */
class CurrencyHelper
{
    /**
     * Format amount with default currency.
     */
    public static function format(float $amount, ?string $currencyCode = null): string
    {
        return CurrencyService::format($amount, $currencyCode);
    }

    /**
     * Convert amount between currencies.
     */
    public static function convert(float $amount, string $from, string $to): float
    {
        return CurrencyService::convert($amount, $from, $to);
    }

    /**
     * Get currency symbol.
     */
    public static function symbol(?string $currencyCode = null): string
    {
        if ($currencyCode) {
            $currency = Currency::where('code', $currencyCode)->first();
            return $currency?->symbol ?? $currencyCode;
        }

        return Currency::getDefault()?->symbol ?? 'Rp';
    }

    /**
     * Get all active currencies for dropdowns.
     */
    public static function options(): array
    {
        return Currency::getActive()
            ->map(fn($c) => [
                'value' => $c->code,
                'label' => "{$c->code} - {$c->name}",
                'symbol' => $c->symbol,
            ])
            ->toArray();
    }
}
