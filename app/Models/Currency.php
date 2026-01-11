<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * Currency Model
 * 
 * Manages currencies and exchange rates for multi-currency support.
 */
class Currency extends Model
{
    use LogsActivity;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'decimal_places',
        'decimal_separator',
        'thousand_separator',
        'symbol_position',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'decimal_places' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the default currency.
     */
    public static function getDefault(): ?self
    {
        return Cache::remember('default_currency', 3600, function () {
            return static::where('is_default', true)->first();
        });
    }

    /**
     * Get active currencies.
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('active_currencies', 3600, function () {
            return static::where('is_active', true)->orderBy('code')->get();
        });
    }

    /**
     * Convert amount from this currency to target currency.
     */
    public function convertTo(float $amount, Currency $targetCurrency): float
    {
        if ($this->code === $targetCurrency->code) {
            return $amount;
        }

        // Convert to base currency first, then to target
        $baseAmount = $amount / $this->exchange_rate;
        return $baseAmount * $targetCurrency->exchange_rate;
    }

    /**
     * Convert amount from base currency to this currency.
     */
    public function convertFromBase(float $amount): float
    {
        return $amount * $this->exchange_rate;
    }

    /**
     * Convert amount from this currency to base currency.
     */
    public function convertToBase(float $amount): float
    {
        return $amount / $this->exchange_rate;
    }

    /**
     * Format amount with currency symbol and formatting.
     */
    public function format(float $amount): string
    {
        $formatted = number_format(
            $amount,
            $this->decimal_places,
            $this->decimal_separator,
            $this->thousand_separator
        );

        return $this->symbol_position === 'before'
            ? $this->symbol . ' ' . $formatted
            : $formatted . ' ' . $this->symbol;
    }

    /**
     * Set as default currency.
     */
    public function setAsDefault(): bool
    {
        static::where('is_default', true)->update(['is_default' => false]);
        $this->is_default = true;
        $result = $this->save();
        
        Cache::forget('default_currency');
        
        return $result;
    }

    /**
     * Clear currency cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('default_currency');
        Cache::forget('active_currencies');
    }

    protected static function booted(): void
    {
        static::saved(fn() => static::clearCache());
        static::deleted(fn() => static::clearCache());
    }
}
