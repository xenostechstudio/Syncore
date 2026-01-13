<?php

use App\Models\User;
use App\Models\Inventory\Product;
use App\Models\Inventory\Category;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('unauthenticated user cannot access products', function () {
    $response = $this->getJson('/api/v1/products');

    $response->assertStatus(401);
});

test('can list products', function () {
    Product::factory()->count(5)->create();

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/products');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data',
            'meta',
        ])
        ->assertJson(['success' => true]);
});

test('can search products by name', function () {
    Product::factory()->create(['name' => 'Widget Pro']);
    Product::factory()->create(['name' => 'Gadget Plus']);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/products?search=Widget');

    $response->assertStatus(200);
    expect($response->json('meta.total'))->toBe(1);
});

test('can search products by SKU', function () {
    Product::factory()->create(['sku' => 'SKU-001']);
    Product::factory()->create(['sku' => 'SKU-002']);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/products?search=SKU-001');

    $response->assertStatus(200);
    expect($response->json('meta.total'))->toBe(1);
});

test('can filter products by category', function () {
    $category = Category::factory()->create();
    
    Product::factory()->count(3)->create(['category_id' => $category->id]);
    Product::factory()->count(2)->create();

    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/products?category_id={$category->id}");

    $response->assertStatus(200);
    expect($response->json('meta.total'))->toBe(3);
});

test('can filter products by status', function () {
    Product::factory()->count(3)->create(['status' => 'active']);
    Product::factory()->count(2)->create(['status' => 'inactive']);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/products?status=active');

    $response->assertStatus(200);
    expect($response->json('meta.total'))->toBe(3);
});

test('can filter low stock products', function () {
    Product::factory()->create(['quantity' => 5, 'status' => 'active']);
    Product::factory()->create(['quantity' => 100, 'status' => 'active']);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/products?low_stock=1');

    $response->assertStatus(200);
    expect($response->json('meta.total'))->toBe(1);
});

test('can get single product', function () {
    $product = Product::factory()->create();

    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
            ],
        ]);
});

test('returns 404 for non-existent product', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/products/99999');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Product not found',
        ]);
});

test('can get product stock', function () {
    $product = Product::factory()->create(['quantity' => 100]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/products/{$product->id}/stock");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'product_id',
                'total_quantity',
            ],
        ]);
});

test('can create product', function () {
    $data = [
        'name' => 'New Product',
        'sku' => 'NEW-001',
        'cost_price' => 10000,
        'selling_price' => 15000,
        'quantity' => 50,
        'unit' => 'pcs',
        'status' => 'active',
    ];

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/products', $data);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Product created successfully',
        ]);

    $this->assertDatabaseHas('products', [
        'name' => 'New Product',
        'sku' => 'NEW-001',
    ]);
});

test('validates required fields when creating product', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/products', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'sku', 'cost_price', 'selling_price']);
});

test('validates unique SKU', function () {
    Product::factory()->create(['sku' => 'EXISTING-SKU']);

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/products', [
            'name' => 'New Product',
            'sku' => 'EXISTING-SKU',
            'cost_price' => 10000,
            'selling_price' => 15000,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['sku']);
});

test('can update product', function () {
    $product = Product::factory()->create();

    $response = $this->actingAs($this->user)
        ->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Product Name',
            'selling_price' => 20000,
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Product updated successfully',
        ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Product Name',
        'selling_price' => 20000,
    ]);
});

test('can update product SKU to unique value', function () {
    $product = Product::factory()->create(['sku' => 'OLD-SKU']);

    $response = $this->actingAs($this->user)
        ->putJson("/api/v1/products/{$product->id}", [
            'sku' => 'NEW-SKU',
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'sku' => 'NEW-SKU',
    ]);
});

test('cannot update product SKU to existing SKU', function () {
    Product::factory()->create(['sku' => 'EXISTING-SKU']);
    $product = Product::factory()->create(['sku' => 'MY-SKU']);

    $response = $this->actingAs($this->user)
        ->putJson("/api/v1/products/{$product->id}", [
            'sku' => 'EXISTING-SKU',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['sku']);
});
