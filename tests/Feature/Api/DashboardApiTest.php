<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('unauthenticated user cannot access dashboard API', function () {
    $response = $this->getJson('/api/v1/dashboard');

    $response->assertStatus(401);
});

test('can get dashboard data', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/dashboard');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'sales',
                'invoices',
                'inventory',
                'purchases',
                'pending_actions',
                'cash_flow',
            ],
        ])
        ->assertJson(['success' => true]);
});

test('can get fresh dashboard data', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/dashboard?fresh=1');

    $response->assertStatus(200)
        ->assertJson(['success' => true]);
});

test('can get KPI data', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/dashboard/kpi');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'sales',
                'invoices',
                'inventory',
                'purchases',
                'pending_actions',
                'cash_flow',
            ],
        ]);
});

test('can get sales widget data', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/dashboard/sales');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'summary',
                'chart',
                'top_customers',
                'top_products',
            ],
        ]);
});

test('can get inventory widget data', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/dashboard/inventory');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'summary',
                'low_stock',
                'by_warehouse',
            ],
        ]);
});

test('can get invoicing widget data', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/dashboard/invoicing');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'summary',
                'aging',
                'revenue_chart',
            ],
        ]);
});

test('can get HR widget data', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/dashboard/hr');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'summary',
                'by_department',
            ],
        ]);
});

test('can get CRM widget data', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/dashboard/crm');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'summary',
                'pipeline',
                'lead_funnel',
            ],
        ]);
});

test('can get purchase widget data', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/dashboard/purchase');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'summary',
                'by_supplier',
                'bill_aging',
            ],
        ]);
});
