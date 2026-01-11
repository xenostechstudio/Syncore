<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use App\Services\DashboardService;
use App\Services\Reports\ReportService;
use Illuminate\Console\Command;

class CacheManagement extends Command
{
    protected $signature = 'cache:manage 
                            {action : Action to perform (warm|clear|stats)}
                            {--module= : Specific module to target (dashboard|reports|all)}';

    protected $description = 'Manage application cache (warm, clear, or view stats)';

    public function handle(): int
    {
        $action = $this->argument('action');
        $module = $this->option('module') ?? 'all';

        return match ($action) {
            'warm' => $this->warmCache($module),
            'clear' => $this->clearCache($module),
            'stats' => $this->showStats(),
            default => $this->invalidAction($action),
        };
    }

    protected function warmCache(string $module): int
    {
        $this->info('Warming cache...');

        if ($module === 'all' || $module === 'dashboard') {
            $this->line('  → Dashboard data...');
            DashboardService::getAllDashboardData(false);
            $this->info('    ✓ Dashboard cache warmed');
        }

        if ($module === 'all' || $module === 'reports') {
            $this->line('  → Report widgets...');
            $reportService = new ReportService();
            $reportService->getAllWidgetData(false);
            $this->info('    ✓ Report cache warmed');
        }

        $this->newLine();
        $this->info('Cache warming complete!');

        return self::SUCCESS;
    }

    protected function clearCache(string $module): int
    {
        $this->info('Clearing cache...');

        if ($module === 'all') {
            CacheService::invalidateAll();
            $this->info('  ✓ All cache cleared');
        } elseif ($module === 'dashboard') {
            CacheService::invalidateDashboard();
            DashboardService::clearCache();
            $this->info('  ✓ Dashboard cache cleared');
        } elseif ($module === 'reports') {
            CacheService::invalidateReports();
            ReportService::clearCache();
            $this->info('  ✓ Reports cache cleared');
        }

        $this->newLine();
        $this->info('Cache cleared!');

        return self::SUCCESS;
    }

    protected function showStats(): int
    {
        $stats = CacheService::getStats();

        $this->info('Cache Statistics');
        $this->newLine();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Driver', $stats['cache_driver']],
                ['Registered Keys', $stats['registered_keys']],
            ]
        );

        return self::SUCCESS;
    }

    protected function invalidAction(string $action): int
    {
        $this->error("Invalid action: {$action}");
        $this->line('Valid actions: warm, clear, stats');

        return self::FAILURE;
    }
}
