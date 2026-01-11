<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class PerformanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Prevent lazy loading in development (catches N+1 queries)
        Model::preventLazyLoading(!app()->isProduction());

        // Log slow queries in development
        if (!app()->isProduction()) {
            DB::listen(function ($query) {
                if ($query->time > 100) { // Log queries taking more than 100ms
                    logger()->warning('Slow query detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms',
                    ]);
                }
            });
        }
    }
}
