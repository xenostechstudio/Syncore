<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Currency Service
 * 
 * Provides currency conversion and formatting functionality.
 */
class CurrencyService
{
    /**
     * Convert amount between currencies.
     */
    public static function convert(
        float $amount,
        string|int|Currency $fromCurrency,
        string|int|Currency $toCurrency,
        ?Carbon $date = null
    ): float {
        $from = self::resolveCurrency($fromCurrency);
        $to = self::resolveCurrency($toCurrency);

        if (!$from || !$to) {
            return $amount;
        }

        if ($from->id === $to->id) {
            return $amount;
        }

        // Try to get historical rate first
        $rate = ExchangeRate::getRateForDate($from->id, $to->id, $date);

        if ($rate) {
            return $amount * $rate;
        }

        // Fall back to currency exchange rates
        return $from->convertTo($amount, $to);
    }

    /**
     * Convert amount to base currency.
     */
    public static function toBase(float $amount, string|int|Currency $fromCurrency, ?Carbon $date = null): float
    {
        $baseCurrency = Currency::getDefault();
        
        if (!$baseCurrency) {
            return $amount;
        }

        return self::convert($amount, $fromCurrency, $baseCurrency, $date);
    }

    /**
     * Convert amount from base currency.
     */
    public static function fromBase(float $amount, string|int|Currency $toCurrency, ?Carbon $date = null): float
    {
        $baseCurrency = Currency::getDefault();
        
        if (!$baseCurrency) {
            return $amount;
        }

        return self::convert($amount, $baseCurrency, $toCurrency, $date);
    }

    /**
     * Format amount with currency.
     */
    public static function format(float $amount, string|int|Currency|null $currency = null): string
    {
        $currency = $currency ? self::resolveCurrency($currency) : Currency::getDefault();

        if (!$currency) {
            return number_format($amount, 2);
        }

        return $currency->format($amount);
    }

    /**
     * Get exchange rate between currencies.
     */
    public static function getRate(
        string|int|Currency $fromCurrency,
        string|int|Currency $toCurrency,
        ?Carbon $date = null
    ): ?float {
        $from = self::resolveCurrency($fromCurrency);
        $to = self::resolveCurrency($toCurrency);

        if (!$from || !$to) {
            return null;
        }

        if ($from->id === $to->id) {
            return 1.0;
        }

        // Try historical rate
        $rate = ExchangeRate::getRateForDate($from->id, $to->id, $date);

        if ($rate) {
            return $rate;
        }

        // Calculate from currency exchange rates
        if ($from->exchange_rate > 0) {
            return $to->exchange_rate / $from->exchange_rate;
        }

        return null;
    }


    /**
     * Update exchange rate.
     */
    public static function updateRate(
        string|int|Currency $fromCurrency,
        string|int|Currency $toCurrency,
        float $rate,
        ?Carbon $date = null,
        string $source = 'manual'
    ): ?ExchangeRate {
        $from = self::resolveCurrency($fromCurrency);
        $to = self::resolveCurrency($toCurrency);

        if (!$from || !$to) {
            return null;
        }

        return ExchangeRate::recordRate($from->id, $to->id, $rate, $date, $source);
    }

    /**
     * Get all active currencies.
     */
    public static function getActiveCurrencies(): \Illuminate\Database\Eloquent\Collection
    {
        return Currency::getActive();
    }

    /**
     * Get default currency.
     */
    public static function getDefaultCurrency(): ?Currency
    {
        return Currency::getDefault();
    }

    /**
     * Resolve currency from code, ID, or model.
     */
    protected static function resolveCurrency(string|int|Currency $currency): ?Currency
    {
        if ($currency instanceof Currency) {
            return $currency;
        }

        $cacheKey = "currency_" . (is_int($currency) ? "id_{$currency}" : "code_{$currency}");

        return Cache::remember($cacheKey, 3600, function () use ($currency) {
            if (is_int($currency)) {
                return Currency::find($currency);
            }

            return Currency::where('code', strtoupper($currency))->first();
        });
    }

    /**
     * Parse amount from formatted string.
     */
    public static function parse(string $amount, ?Currency $currency = null): float
    {
        $currency = $currency ?? Currency::getDefault();

        if ($currency) {
            // Remove currency symbol
            $amount = str_replace($currency->symbol, '', $amount);
            // Replace thousand separator
            $amount = str_replace($currency->thousand_separator, '', $amount);
            // Replace decimal separator with dot
            $amount = str_replace($currency->decimal_separator, '.', $amount);
        }

        // Remove any remaining non-numeric characters except dot and minus
        $amount = preg_replace('/[^0-9.\-]/', '', $amount);

        return (float) $amount;
    }

    /**
     * Clear currency cache.
     */
    public static function clearCache(): void
    {
        Currency::clearCache();
    }
}
