<?php

/**
 * Pins the contract of the /api/health endpoint and the production:check
 * command. Both are part of the soft-launch toolkit — uptime monitoring
 * hits the endpoint, deploy scripts run the command. Breaking either
 * one silently is the kind of regression that only surfaces during an
 * incident, when you're least equipped to debug it.
 */

it('GET /api/health returns 200 with the expected shape when everything is up', function () {
    $response = $this->getJson('/api/health');

    $response->assertOk();

    $data = $response->json();
    expect($data)->toHaveKeys(['status', 'timestamp', 'php_version', 'database', 'cache', 'queue']);
    expect($data['database']['status'])->toBe('connected');
    expect($data['cache']['status'])->toBe('connected');
    // sync queue (used in tests by default) reports as 'connected' since
    // the match arm returns true; in real prod with redis/db it'd be
    // 'connected' if reachable, 'error' if not.
    expect($data['queue']['status'])->not->toBe('error');
});

it('returns 503 when the queue probe reports an error', function () {
    // Point the queue at the database driver but with a table name that
    // doesn't exist — the probe's Schema::hasTable() returns false, which
    // checkQueueConnection() reports as status='error'. (PerformanceService
    // calls static methods so partialMock() can't intercept it; driving the
    // failure through real config is both more faithful and more robust.)
    config([
        'queue.default'                    => 'database',
        'queue.connections.database.table' => 'nonexistent_jobs_table',
    ]);

    $response = $this->getJson('/api/health');

    $response->assertStatus(503);
    expect($response->json('queue.status'))->toBe('error');
});

it('production:check passes in a healthy local environment', function () {
    $this->artisan('production:check')->assertExitCode(0);
});

it('production:check fails when APP_DEBUG is true in production', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['app.debug' => true]);

    $this->artisan('production:check')
        ->expectsOutputToContain('APP_DEBUG is true in production')
        ->assertExitCode(1);
});

it('production:check fails when MAIL_MAILER=log in production', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['mail.default' => 'log']);

    $this->artisan('production:check')
        ->expectsOutputToContain('MAIL_MAILER=log in production')
        ->assertExitCode(1);
});

it('production:check fails when Xendit is configured but webhook token is empty in prod', function () {
    app()->detectEnvironment(fn () => 'production');
    config([
        'xendit.secret_key'    => 'live-secret',
        'xendit.webhook_token' => '',
    ]);

    $this->artisan('production:check')
        ->expectsOutputToContain('XENDIT_WEBHOOK_TOKEN is empty')
        ->assertExitCode(1);
});

it('production:check escalates http:// APP_URL warning to a failure under --strict (--config-only baseline)', function () {
    app()->detectEnvironment(fn () => 'production');
    // --config-only sidesteps the driver-specific checks (sqlite,
    // sync queue, etc. — all of which would fire in this test env) so the
    // http:// warn becomes the only finding under --strict.
    config([
        'app.debug'            => false,
        'app.url'              => 'http://erp.example.com',
        'app.key'              => 'base64:'.base64_encode(random_bytes(32)),
        'xendit.secret_key'    => null,
        'xendit.webhook_token' => null,
    ]);

    // Without --strict, warnings don't fail.
    $this->artisan('production:check', ['--config-only' => true])
        ->expectsOutputToContain('http://')
        ->assertExitCode(0);

    // With --strict, the same warning escalates to a failure.
    $this->artisan('production:check', ['--strict' => true, '--config-only' => true])
        ->expectsOutputToContain('http://')
        ->assertExitCode(1);
});
