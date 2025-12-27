<?php

namespace App\Providers;

use App\Models\Settings\EmailConfiguration;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Apply database email configuration if available
        // Only run if the table exists (after migration)
        try {
            if (\Schema::hasTable('email_configuration')) {
                EmailConfiguration::applyToConfig();
            }
        } catch (\Exception $e) {
            // Silently fail if database is not available
        }
    }
}
