<?php

use App\Models\User;

test('health check endpoint is accessible without auth', function () {
    $response = $this->getJson('/api/health');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'timestamp',
            'php_version',
            'laravel_version',
            'memory' => ['usage_mb', 'peak_mb', 'limit'],
            'database' => ['status'],
            'cache' => ['status', 'driver'],
            'queue' => ['status', 'driver'],
        ]);
});

test('health check returns healthy status when all services are up', function () {
    $response = $this->getJson('/api/health');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'healthy',
            'database' => ['status' => 'connected'],
            'cache' => ['status' => 'connected'],
        ]);
});

test('detailed health check requires authentication', function () {
    $response = $this->getJson('/api/v1/health/detailed');

    $response->assertStatus(401);
});

test('authenticated user can access detailed health check', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson('/api/v1/health/detailed');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'health',
                'database',
                'cache',
            ],
        ]);
});
