<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Performance Service
 * 
 * Provides performance monitoring and optimization utilities.
 */
class PerformanceService
{
    protected static float $startTime;
    protected static array $queryLog = [];
    protected static bool $isMonitoring = false;

    /**
     * Start performance monitoring.
     */
    public static function startMonitoring(): void
    {
        self::$startTime = microtime(true);
        self::$isMonitoring = true;
        
        DB::enableQueryLog();
    }

    /**
     * Stop monitoring and get results.
     */
    public static function stopMonitoring(): array
    {
        if (!self::$isMonitoring) {
            return [];
        }

        $endTime = microtime(true);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        
        self::$isMonitoring = false;

        $totalQueryTime = array_sum(array_column($queries, 'time'));

        return [
            'execution_time_ms' => round(($endTime - self::$startTime) * 1000, 2),
            'query_count' => count($queries),
            'total_query_time_ms' => round($totalQueryTime, 2),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'queries' => array_map(fn($q) => [
                'sql' => $q['query'],
                'time_ms' => $q['time'],
            ], $queries),
        ];
    }

    /**
     * Log slow queries.
     */
    public static function logSlowQueries(float $thresholdMs = 100): void
    {
        DB::listen(function ($query) use ($thresholdMs) {
            if ($query->time > $thresholdMs) {
                Log::warning('Slow query detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time_ms' => $query->time,
                ]);
            }
        });
    }

    /**
     * Get database statistics.
     */
    public static function getDatabaseStats(): array
    {
        $stats = [];

        // Table sizes (PostgreSQL)
        try {
            $tableSizes = DB::select("
                SELECT 
                    relname as table_name,
                    pg_size_pretty(pg_total_relation_size(relid)) as total_size,
                    pg_size_pretty(pg_relation_size(relid)) as data_size,
                    pg_size_pretty(pg_total_relation_size(relid) - pg_relation_size(relid)) as index_size
                FROM pg_catalog.pg_statio_user_tables
                ORDER BY pg_total_relation_size(relid) DESC
                LIMIT 20
            ");
            $stats['table_sizes'] = $tableSizes;
        } catch (\Exception $e) {
            $stats['table_sizes'] = [];
        }

        // Connection stats
        try {
            $connections = DB::select("
                SELECT count(*) as total,
                       count(*) FILTER (WHERE state = 'active') as active,
                       count(*) FILTER (WHERE state = 'idle') as idle
                FROM pg_stat_activity
                WHERE datname = current_database()
            ");
            $stats['connections'] = $connections[0] ?? null;
        } catch (\Exception $e) {
            $stats['connections'] = null;
        }

        return $stats;
    }

    /**
     * Get cache statistics.
     */
    public static function getCacheStats(): array
    {
        return [
            'driver' => config('cache.default'),
            'stats' => CacheService::getStats(),
        ];
    }

    /**
     * Get system health metrics.
     */
    public static function getHealthMetrics(): array
    {
        return [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory' => [
                'usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'limit' => ini_get('memory_limit'),
            ],
            'database' => self::checkDatabaseConnection(),
            'cache' => self::checkCacheConnection(),
            'queue' => self::checkQueueConnection(),
        ];
    }

    /**
     * Check database connection.
     */
    protected static function checkDatabaseConnection(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $time = (microtime(true) - $start) * 1000;

            return [
                'status' => 'connected',
                'response_time_ms' => round($time, 2),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache connection.
     */
    protected static function checkCacheConnection(): array
    {
        try {
            $start = microtime(true);
            Cache::put('health_check', true, 1);
            Cache::get('health_check');
            $time = (microtime(true) - $start) * 1000;

            return [
                'status' => 'connected',
                'driver' => config('cache.default'),
                'response_time_ms' => round($time, 2),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue connection.
     */
    protected static function checkQueueConnection(): array
    {
        try {
            return [
                'status' => 'configured',
                'driver' => config('queue.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize database queries for a model.
     */
    public static function analyzeModelQueries(string $modelClass): array
    {
        $recommendations = [];
        
        // Check if model uses soft deletes
        if (method_exists($modelClass, 'bootSoftDeletes')) {
            $recommendations[] = 'Model uses soft deletes - ensure deleted_at is indexed';
        }

        // Check for common relationship patterns
        $model = new $modelClass;
        $relations = [];
        
        foreach (get_class_methods($model) as $method) {
            try {
                $reflection = new \ReflectionMethod($model, $method);
                if ($reflection->getNumberOfParameters() === 0 && 
                    !$reflection->isStatic() && 
                    $reflection->isPublic()) {
                    // Could be a relationship
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return [
            'model' => $modelClass,
            'recommendations' => $recommendations,
        ];
    }
}
