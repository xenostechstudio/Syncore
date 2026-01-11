<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Clean up old activity logs (runs weekly on Sunday at 2 AM)
Schedule::command('activity-logs:cleanup --days=90')
    ->weeklyOn(0, '02:00')
    ->withoutOverlapping()
    ->runInBackground();

// Check for overdue invoices (runs daily at 8 AM)
Schedule::command('invoices:check-overdue')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground();

// Check for low stock products (runs daily at 9 AM)
Schedule::command('inventory:check-low-stock')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->runInBackground();

// Clear dashboard cache hourly
Schedule::call(function () {
    \App\Services\DashboardService::clearCache();
})->hourly();
