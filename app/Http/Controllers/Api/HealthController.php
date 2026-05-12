<?php

namespace App\Http\Controllers\Api;

use App\Services\PerformanceService;
use Illuminate\Http\JsonResponse;

class HealthController extends BaseApiController
{
    public function __invoke(): JsonResponse
    {
        $health = PerformanceService::getHealthMetrics();

        // Treat 'error' status from any backend probe as unhealthy. Both
        // 'connected' (probe ran + reachable) and 'configured' (probe not
        // applicable — sync/null queue, etc.) are fine.
        $isHealthy = $health['database']['status'] === 'connected'
            && $health['cache']['status'] === 'connected'
            && $health['queue']['status'] !== 'error';

        return response()->json($health, $isHealthy ? 200 : 503);
    }

    public function detailed(): JsonResponse
    {
        $health = PerformanceService::getHealthMetrics();
        $dbStats = PerformanceService::getDatabaseStats();
        $cacheStats = PerformanceService::getCacheStats();

        return $this->success([
            'health' => $health,
            'database' => $dbStats,
            'cache' => $cacheStats,
        ]);
    }
}
