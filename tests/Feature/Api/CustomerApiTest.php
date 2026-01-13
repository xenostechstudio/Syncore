<?php

use App\Models\User;
use App\Models\Sales\Customer;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('unauthenticated user cannot access customers', function () {
    $response = $this->getJson('/api/v1/customers');

    $response->assertStatus(401);
});

test('can list customers', function () {
    Customer::factory()->count(5)->create();

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/customers');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            'links',
        ])
        ->assertJson(['success' => true]);
});

test('can search customers', function () {
    Customer::factory()->create(['name' => 'John Doe']);
    Customer::factory()->create(['name' => 'Jane Smith']);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/customers?search=John');

    $response->assertStatus(200)
        ->assertJson(['success' => true]);

    expect($response->json('meta.total'))->toBe(1);
});

test('can paginate customers', function () {
    Customer::factory()->count(20)->create();

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/customers?per_page=5');

    $response->assertStatus(200);

    expect($response->json('meta.per_page'))->toBe(5)
        ->and($response->json('meta.total'))->toBe(20);
});

test('can get single customer', function () {
    $customer = Customer::factory()->create();

    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/customers/{$customer->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
            ],
        ]);
});

test('returns 404 for non-existent customer', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/customers/99999');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Customer not found',
        ]);
});

test('can create customer', function () {
    $data = [
        'name' => 'New Customer',
        'email' => 'new@example.com',
        'phone' => '+62812345678',
        'address' => '123 Main St',
        'city' => 'Jakarta',
        'country' => 'Indonesia',
    ];

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/customers', $data);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Customer created successfully',
        ]);

    $this->assertDatabaseHas('customers', [
        'name' => 'New Customer',
        'email' => 'new@example.com',
    ]);
});

test('validates required fields when creating customer', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/customers', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('validates email format', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/customers', [
            'name' => 'Test Customer',
            'email' => 'invalid-email',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('can update customer', function () {
    $customer = Customer::factory()->create();

    $response = $this->actingAs($this->user)
        ->putJson("/api/v1/customers/{$customer->id}", [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Customer updated successfully',
        ]);

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => 'Updated Name',
    ]);
});

test('can delete customer without orders', function () {
    $customer = Customer::factory()->create();

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/v1/customers/{$customer->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Customer deleted successfully',
        ]);

    // With soft deletes, check that the record is soft deleted
    $this->assertSoftDeleted('customers', ['id' => $customer->id]);
});

test('cannot delete customer with orders', function () {
    $customer = Customer::factory()->create();
    
    // Create an order for this customer
    \App\Models\Sales\SalesOrder::factory()->create([
        'customer_id' => $customer->id,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/v1/customers/{$customer->id}");

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Cannot delete customer with existing orders',
        ]);

    $this->assertDatabaseHas('customers', ['id' => $customer->id]);
});
