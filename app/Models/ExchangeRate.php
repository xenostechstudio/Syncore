<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * Exchange Rate Model
 * 
 * Stores historical exchange rates for currency conversion.
 */
class ExchangeRate extends Model
{
    use LogsActivity;

    protected array $logActions = ['created', 'updated'];

    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'rate',
        'rate_date',
        'source',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'rate_date' => 'date',
    ];

    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    /**
     * Get the exchange rate for a specific date.
     */
    public static function getRateForDate(
        int $fromCurrencyId,
        int $toCurrencyId,
        ?\Carbon\Carbon $date = null
    ): ?float {
        $date = $date ?? now();
        $cacheKey = "exchange_rate_{$fromCurrencyId}_{$toCurrencyId}_{$date->format('Y-m-d')}";

        return Cache::remember($cacheKey, 3600, function () use ($fromCurrencyId, $toCurrencyId, $date) {
            // Try exact date first
            $rate = static::where('from_currency_id', $fromCurrencyId)
                ->where('to_currency_id', $toCurrencyId)
                ->whereDate('rate_date', $date)
                ->first();

            if ($rate) {
                return $rate->rate;
            }

            // Fall back to most recent rate before the date
            $rate = static::where('from_currency_id', $fromCurrencyId)
                ->where('to_currency_id', $toCurrencyId)
                ->where('rate_date', '<=', $date)
                ->orderByDesc('rate_date')
                ->first();

            return $rate?->rate;
        });
    }

    /**
     * Get the latest exchange rate.
     */
    public static function getLatestRate(int $fromCurrencyId, int $toCurrencyId): ?float
    {
        return static::where('from_currency_id', $fromCurrencyId)
            ->where('to_currency_id', $toCurrencyId)
            ->orderByDesc('rate_date')
            ->value('rate');
    }

    /**
     * Record a new exchange rate.
     */
    public static function recordRate(
        int $fromCurrencyId,
        int $toCurrencyId,
        float $rate,
        ?\Carbon\Carbon $date = null,
        string $source = 'manual'
    ): self {
        $date = $date ?? now();

        return static::updateOrCreate(
            [
                'from_currency_id' => $fromCurrencyId,
                'to_currency_id' => $toCurrencyId,
                'rate_date' => $date->format('Y-m-d'),
            ],
            [
                'rate' => $rate,
                'source' => $source,
            ]
        );
    }
}
