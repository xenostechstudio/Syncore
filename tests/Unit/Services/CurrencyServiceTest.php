<?php

use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // Clear existing currencies (seeded by migration)
    DB::table('currencies')->truncate();
    
    // Create test currencies
    Currency::create([
        'code' => 'IDR',
        'name' => 'Indonesian Rupiah',
        'symbol' => 'Rp',
        'exchange_rate' => 1.000000,
        'decimal_places' => 0,
        'decimal_separator' => ',',
        'thousand_separator' => '.',
        'symbol_position' => 'before',
        'is_default' => true,
        'is_active' => true,
    ]);

    Currency::create([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => '$',
        'exchange_rate' => 0.000063,
        'decimal_places' => 2,
        'decimal_separator' => '.',
        'thousand_separator' => ',',
        'symbol_position' => 'before',
        'is_default' => false,
        'is_active' => true,
    ]);
});

test('can get default currency', function () {
    $default = CurrencyService::getDefaultCurrency();

    expect($default)->not->toBeNull()
        ->and($default->code)->toBe('IDR')
        ->and($default->is_default)->toBeTrue();
});

test('can get active currencies', function () {
    $currencies = CurrencyService::getActiveCurrencies();

    expect($currencies)->toHaveCount(2)
        ->and($currencies->pluck('code')->toArray())->toContain('IDR', 'USD');
});

test('can convert between currencies', function () {
    $amount = 1000000; // 1 million IDR
    $converted = CurrencyService::convert($amount, 'IDR', 'USD');

    // 1,000,000 IDR * 0.000063 = 63 USD
    expect($converted)->toBe(63.0);
});

test('converting same currency returns same amount', function () {
    $amount = 1000000;
    $converted = CurrencyService::convert($amount, 'IDR', 'IDR');

    expect($converted)->toBe((float) $amount);
});

test('can convert to base currency', function () {
    $amount = 100; // 100 USD
    $base = CurrencyService::toBase($amount, 'USD');

    // 100 / 0.000063 ≈ 1,587,301 IDR
    expect($base)->toBeGreaterThan(1500000);
});

test('can format currency', function () {
    $formatted = CurrencyService::format(1000000, 'IDR');

    expect($formatted)->toContain('Rp')
        ->and($formatted)->toContain('1.000.000');
});

test('can format USD currency', function () {
    $formatted = CurrencyService::format(1234.56, 'USD');

    expect($formatted)->toContain('$')
        ->and($formatted)->toContain('1,234.56');
});

test('can get exchange rate', function () {
    $rate = CurrencyService::getRate('IDR', 'USD');

    expect($rate)->toBe(0.000063);
});

test('exchange rate for same currency is 1', function () {
    $rate = CurrencyService::getRate('IDR', 'IDR');

    expect($rate)->toBe(1.0);
});

test('returns null for invalid currency', function () {
    $rate = CurrencyService::getRate('IDR', 'INVALID');

    expect($rate)->toBeNull();
});
