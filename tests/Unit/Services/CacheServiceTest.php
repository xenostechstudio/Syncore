<?php

use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('can remember value', function () {
    $callCount = 0;
    
    $value = CacheService::remember('test_key', function () use (&$callCount) {
        $callCount++;
        return 'test_value';
    });

    expect($value)->toBe('test_value')
        ->and($callCount)->toBe(1);

    // Second call should use cache
    $value2 = CacheService::remember('test_key', function () use (&$callCount) {
        $callCount++;
        return 'different_value';
    });

    expect($value2)->toBe('test_value')
        ->and($callCount)->toBe(1); // Still 1, callback not called
});

test('can remember forever', function () {
    $value = CacheService::rememberForever('forever_key', fn() => 'forever_value');

    expect($value)->toBe('forever_value')
        ->and(Cache::has('forever_key'))->toBeTrue();
});

test('can cache collection', function () {
    $value = CacheService::collection('products', fn() => ['product1', 'product2']);

    expect($value)->toBe(['product1', 'product2'])
        ->and(Cache::has('collection:products'))->toBeTrue();
});

test('can cache dashboard data', function () {
    $value = CacheService::dashboard('sales', fn() => ['total' => 1000]);

    expect($value)->toBe(['total' => 1000]);
});

test('can cache report data', function () {
    $params = ['start_date' => '2026-01-01', 'end_date' => '2026-01-31'];
    $value = CacheService::report('sales', $params, fn() => ['revenue' => 5000]);

    expect($value)->toBe(['revenue' => 5000]);
});

test('can invalidate all cache', function () {
    Cache::put('key1', 'value1');
    Cache::put('key2', 'value2');

    CacheService::invalidateAll();

    expect(Cache::has('key1'))->toBeFalse()
        ->and(Cache::has('key2'))->toBeFalse();
});

test('can get cache stats', function () {
    $stats = CacheService::getStats();

    expect($stats)->toHaveKey('cache_driver')
        ->and($stats)->toHaveKey('registered_keys');
});

test('uses correct TTL constants', function () {
    expect(CacheService::DEFAULT_TTL)->toBe(3600)
        ->and(CacheService::SHORT_TTL)->toBe(300)
        ->and(CacheService::LONG_TTL)->toBe(86400);
});
