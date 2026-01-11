<?php

namespace App\Traits;

use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * HasCurrency Trait
 * 
 * Provides multi-currency support for models with monetary values.
 * 
 * Usage:
 * ```php
 * class Invoice extends Model
 * {
 *     use HasCurrency;
 *     
 *     // Define which fields contain monetary values
 *     protected array $monetaryFields = ['subtotal', 'tax', 'total'];
 * }
 * ```
 */
trait HasCurrency
{
    /**
     * Get the currency relationship.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the currency model or default.
     */
    public function getCurrencyModel(): ?Currency
    {
        return $this->currency ?? Currency::getDefault();
    }

    /**
     * Get the exchange rate used for this record.
     */
    public function getExchangeRateAttribute(): float
    {
        return $this->attributes['exchange_rate'] ?? $this->getCurrencyModel()?->exchange_rate ?? 1.0;
    }

    /**
     * Convert a monetary field to base currency.
     */
    public function toBaseCurrency(string $field): float
    {
        $amount = $this->{$field} ?? 0;
        $currency = $this->getCurrencyModel();

        if (!$currency || $currency->is_default) {
            return $amount;
        }

        return CurrencyService::toBase($amount, $currency, $this->created_at);
    }

    /**
     * Convert a monetary field from base currency.
     */
    public function fromBaseCurrency(float $amount): float
    {
        $currency = $this->getCurrencyModel();

        if (!$currency || $currency->is_default) {
            return $amount;
        }

        return CurrencyService::fromBase($amount, $currency, $this->created_at);
    }

    /**
     * Format a monetary field with currency.
     */
    public function formatMoney(string $field): string
    {
        $amount = $this->{$field} ?? 0;
        return CurrencyService::format($amount, $this->getCurrencyModel());
    }

    /**
     * Get all monetary values in base currency.
     */
    public function getBaseAmounts(): array
    {
        $fields = $this->monetaryFields ?? [];
        $amounts = [];

        foreach ($fields as $field) {
            $amounts[$field] = $this->toBaseCurrency($field);
        }

        return $amounts;
    }

    /**
     * Set currency and exchange rate.
     */
    public function setCurrency(string|int|Currency $currency): self
    {
        $currencyModel = $currency instanceof Currency 
            ? $currency 
            : Currency::where(is_int($currency) ? 'id' : 'code', $currency)->first();

        if ($currencyModel) {
            $this->currency_id = $currencyModel->id;
            $this->exchange_rate = $currencyModel->exchange_rate;
        }

        return $this;
    }

    /**
     * Scope to filter by currency.
     */
    public function scopeInCurrency($query, string|int|Currency $currency)
    {
        $currencyId = $currency instanceof Currency 
            ? $currency->id 
            : (is_int($currency) ? $currency : Currency::where('code', $currency)->value('id'));

        return $query->where('currency_id', $currencyId);
    }

    /**
     * Scope to get records in base currency.
     */
    public function scopeInBaseCurrency($query)
    {
        $defaultCurrency = Currency::getDefault();
        
        if ($defaultCurrency) {
            return $query->where('currency_id', $defaultCurrency->id);
        }

        return $query;
    }
}
