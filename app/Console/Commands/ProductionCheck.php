<?php

namespace App\Console\Commands;

use App\Services\PerformanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Pre-deploy / post-deploy sanity checks. Run after every release to
 * catch misconfigurations that would otherwise only surface when a real
 * customer hit a real edge case. Designed to fail fast with a clear
 * remediation message, not to be a smoke test of every feature.
 *
 * Usage:
 *   php artisan production:check
 *
 * Exit code 0 = pass, 1 = at least one critical issue. Non-critical
 * issues print as WARN but don't fail the command.
 */
class ProductionCheck extends Command
{
    protected $signature = 'production:check
        {--strict : Treat warnings as failures}
        {--config-only : Run env/key/url/storage checks only; skip driver-specific checks and live probes}';

    protected $description = 'Sanity-check the production environment for common misconfigurations';

    /** @var array<int,array{level:string,msg:string,fix:?string}> */
    protected array $findings = [];

    public function handle(): int
    {
        $this->info('Production readiness checks…');
        $this->newLine();

        $configOnly = (bool) $this->option('config-only');

        $this->checkAppDebug();
        $this->checkAppKey();
        $this->checkAppUrl();
        if (! $configOnly) {
            $this->checkDatabaseDriver();
            $this->checkQueueDriver();
            $this->checkCacheDriver();
            $this->checkMailDriver();
        }
        $this->checkXenditWebhookToken();
        $this->checkStorageLink();
        if (! $configOnly) {
            $this->checkBackendsReachable();
        }

        return $this->renderReport();
    }

    protected function checkAppDebug(): void
    {
        if (app()->isProduction() && config('app.debug')) {
            $this->fail_('APP_DEBUG is true in production — stack traces will leak to customers.', 'Set APP_DEBUG=false in .env');
        }
    }

    protected function checkAppKey(): void
    {
        if (empty(config('app.key'))) {
            $this->fail_('APP_KEY is empty — sessions and signed URLs will be unstable.', 'Run: php artisan key:generate --force');
        }
    }

    protected function checkAppUrl(): void
    {
        $url = config('app.url');
        if (app()->isProduction() && (empty($url) || str_starts_with($url, 'http://localhost'))) {
            $this->fail_("APP_URL is '{$url}' in production — signed URLs and Xendit redirects will be wrong.", 'Set APP_URL to the real https:// host');
        }
        if (app()->isProduction() && str_starts_with((string) $url, 'http://')) {
            $this->warn_('APP_URL is http:// in production — should be https:// for signed URLs to validate behind a reverse proxy.', 'Use https:// in APP_URL and terminate TLS in front of the app');
        }
    }

    protected function checkDatabaseDriver(): void
    {
        $driver = config('database.default');
        if (app()->isProduction() && $driver === 'sqlite') {
            $this->fail_('DB_CONNECTION=sqlite in production — SQLite ignores enum() CHECK constraints and serializes on every write.', 'Switch to pgsql in .env');
        }
    }

    protected function checkQueueDriver(): void
    {
        $driver = config('queue.default');
        if (app()->isProduction() && in_array($driver, ['sync', 'null'], true)) {
            $this->fail_("QUEUE_CONNECTION={$driver} in production — listeners run in-request, blocking user actions on email/PDF generation.", 'Switch to database or redis');
        }
        if (app()->isProduction() && $driver === 'database') {
            $this->warn_('QUEUE_CONNECTION=database — works, but serializes every job through a row lock. Redis is recommended at scale.', 'Switch to redis when you outgrow the database queue');
        }
    }

    protected function checkCacheDriver(): void
    {
        $driver = config('cache.default');
        if (app()->isProduction() && $driver === 'array') {
            $this->fail_('CACHE_STORE=array in production — cache resets every request.', 'Switch to redis or database');
        }
    }

    protected function checkMailDriver(): void
    {
        $mailer = config('mail.default');
        if (app()->isProduction() && $mailer === 'log') {
            $this->fail_('MAIL_MAILER=log in production — every email goes to storage/logs/laravel.log, none reach customers.', 'Switch to smtp/ses/mailgun/etc. and set credentials');
        }
    }

    protected function checkXenditWebhookToken(): void
    {
        $hasSecret = ! empty(config('xendit.secret_key'));
        $hasToken = ! empty(config('xendit.webhook_token'));

        if (app()->isProduction() && $hasSecret && ! $hasToken) {
            $this->fail_('Xendit is configured but XENDIT_WEBHOOK_TOKEN is empty — the webhook controller refuses callbacks in production without it.', 'Copy the token from Xendit dashboard → Settings → Webhooks');
        }
    }

    protected function checkStorageLink(): void
    {
        $linkPath = public_path('storage');
        if (! is_link($linkPath) && ! is_dir($linkPath)) {
            $this->warn_("public/storage doesn't exist — uploaded attachments and logos won't be accessible.", 'Run: php artisan storage:link');
        }
    }

    protected function checkBackendsReachable(): void
    {
        $health = PerformanceService::getHealthMetrics();

        foreach (['database', 'cache', 'queue'] as $backend) {
            $status = $health[$backend]['status'] ?? null;
            if ($status === 'error') {
                $msg = $health[$backend]['message'] ?? 'unknown error';
                $this->fail_("{$backend} backend is unreachable: {$msg}", "Check the {$backend} service and credentials");
            }
        }
    }

    protected function fail_(string $msg, ?string $fix = null): void
    {
        $this->findings[] = ['level' => 'fail', 'msg' => $msg, 'fix' => $fix];
    }

    protected function warn_(string $msg, ?string $fix = null): void
    {
        $this->findings[] = ['level' => 'warn', 'msg' => $msg, 'fix' => $fix];
    }

    protected function renderReport(): int
    {
        $fails = collect($this->findings)->where('level', 'fail');
        $warns = collect($this->findings)->where('level', 'warn');

        foreach ($warns as $f) {
            $this->line("  <fg=yellow>⚠</>  {$f['msg']}");
            if ($f['fix']) {
                $this->line("     <fg=gray>→ {$f['fix']}</>");
            }
        }
        foreach ($fails as $f) {
            $this->line("  <fg=red>✗</>  {$f['msg']}");
            if ($f['fix']) {
                $this->line("     <fg=gray>→ {$f['fix']}</>");
            }
        }

        if ($fails->isEmpty() && $warns->isEmpty()) {
            $this->newLine();
            $this->info('✓ All checks passed.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $this->line("  {$fails->count()} failure(s), {$warns->count()} warning(s)");

        $strict = (bool) $this->option('strict');
        if ($fails->isNotEmpty() || ($strict && $warns->isNotEmpty())) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
