<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

/**
 * Cache Service
 * 
 * Provides centralized caching functionality with tagging support
 * and automatic cache invalidation.
 */
class CacheService
{
    /** @var int Default cache TTL in seconds (1 hour) */
    public const DEFAULT_TTL = 3600;

    /** @var int Short cache TTL (5 minutes) */
    public const SHORT_TTL = 300;

    /** @var int Long cache TTL (24 hours) */
    public const LONG_TTL = 86400;

    /**
     * Get or set a cached value.
     */
    public static function remember(string $key, callable $callback, int $ttl = self::DEFAULT_TTL): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get or set a cached value forever.
     */
    public static function rememberForever(string $key, callable $callback): mixed
    {
        return Cache::rememberForever($key, $callback);
    }

    /**
     * Cache a model query result.
     */
    public static function model(string $modelClass, int|string $id, callable $callback, int $ttl = self::DEFAULT_TTL): mixed
    {
        $key = self::modelKey($modelClass, $id);
        return self::remember($key, $callback, $ttl);
    }

    /**
     * Cache a collection query result.
     */
    public static function collection(string $key, callable $callback, int $ttl = self::DEFAULT_TTL): mixed
    {
        return self::remember("collection:{$key}", $callback, $ttl);
    }

    /**
     * Cache dashboard data.
     */
    public static function dashboard(string $widget, callable $callback, int $ttl = self::SHORT_TTL): mixed
    {
        $key = "dashboard:{$widget}:" . now()->format('Y-m-d-H');
        return self::remember($key, $callback, $ttl);
    }

    /**
     * Cache report data.
     */
    public static function report(string $reportType, array $params, callable $callback, int $ttl = self::SHORT_TTL): mixed
    {
        $key = "report:{$reportType}:" . md5(serialize($params));
        return self::remember($key, $callback, $ttl);
    }

    /**
     * Invalidate model cache.
     */
    public static function invalidateModel(Model|string $model, int|string|null $id = null): void
    {
        if ($model instanceof Model) {
            $modelClass = get_class($model);
            $id = $model->getKey();
        } else {
            $modelClass = $model;
        }

        if ($id) {
            Cache::forget(self::modelKey($modelClass, $id));
        }

        // Also invalidate related collection caches
        $baseName = strtolower(class_basename($modelClass));
        self::invalidatePattern("collection:{$baseName}*");
    }

    /**
     * Invalidate dashboard caches.
     */
    public static function invalidateDashboard(?string $widget = null): void
    {
        if ($widget) {
            self::invalidatePattern("dashboard:{$widget}:*");
        } else {
            self::invalidatePattern("dashboard:*");
        }
    }

    /**
     * Invalidate report caches.
     */
    public static function invalidateReports(?string $reportType = null): void
    {
        if ($reportType) {
            self::invalidatePattern("report:{$reportType}:*");
        } else {
            self::invalidatePattern("report:*");
        }
    }

    /**
     * Invalidate all application caches.
     */
    public static function invalidateAll(): void
    {
        Cache::flush();
    }

    /**
     * Generate model cache key.
     */
    protected static function modelKey(string $modelClass, int|string $id): string
    {
        $baseName = strtolower(class_basename($modelClass));
        return "model:{$baseName}:{$id}";
    }

    /**
     * Invalidate caches matching a pattern.
     * Note: This is a simplified implementation. For production with Redis,
     * use Redis SCAN with pattern matching.
     */
    protected static function invalidatePattern(string $pattern): void
    {
        // For database cache driver, we can't easily pattern match
        // This is a placeholder for when using Redis
        // With Redis: Cache::getRedis()->keys($pattern) then delete
        
        // For now, we'll use a registry approach
        $registry = Cache::get('cache_registry', []);
        $regex = '/^' . str_replace('*', '.*', preg_quote($pattern, '/')) . '$/';
        
        foreach ($registry as $key => $timestamp) {
            if (preg_match($regex, $key)) {
                Cache::forget($key);
                unset($registry[$key]);
            }
        }
        
        Cache::put('cache_registry', $registry, self::LONG_TTL);
    }

    /**
     * Register a cache key for pattern-based invalidation.
     */
    public static function register(string $key): void
    {
        $registry = Cache::get('cache_registry', []);
        $registry[$key] = now()->timestamp;
        
        // Keep registry size manageable
        if (count($registry) > 1000) {
            $registry = array_slice($registry, -500, null, true);
        }
        
        Cache::put('cache_registry', $registry, self::LONG_TTL);
    }

    /**
     * Get cache statistics.
     */
    public static function getStats(): array
    {
        $registry = Cache::get('cache_registry', []);
        
        return [
            'registered_keys' => count($registry),
            'cache_driver' => config('cache.default'),
        ];
    }
}
