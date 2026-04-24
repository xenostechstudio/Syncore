<?php

use App\Livewire\Sales\Products\Index;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

describe('Sales Products Index', function () {
    it('renders list', function () {
        Product::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('products', fn ($p) => $p->total() === 3);
    });

    it('filters by search on name or sku', function () {
        Product::factory()->create(['name' => 'Widget Deluxe', 'sku' => 'WDG-001']);
        Product::factory()->create(['name' => 'Gadget Pro', 'sku' => 'GDT-002']);

        Livewire::test(Index::class)
            ->set('search', 'Widget')
            ->assertViewHas('products', fn ($p) => $p->total() === 1);

        Livewire::test(Index::class)
            ->set('search', 'GDT-002')
            ->assertViewHas('products', fn ($p) => $p->total() === 1);
    });

    it('filters by status', function () {
        Product::factory()->inStock()->count(2)->create();
        Product::factory()->outOfStock()->count(3)->create();

        Livewire::test(Index::class)
            ->set('status', 'out_of_stock')
            ->assertViewHas('products', fn ($p) => $p->total() === 3);
    });

    it('bulkActivate sets status to in_stock', function () {
        $products = Product::factory()->outOfStock()->count(2)->create();

        Livewire::test(Index::class)
            ->set('selected', $products->pluck('id')->map(fn ($id) => (string) $id)->toArray())
            ->call('bulkActivate');

        expect(Product::where('status', 'in_stock')->count())->toBe(2);
    });

    it('bulkDeactivate sets status to out_of_stock', function () {
        $products = Product::factory()->count(2)->create();

        Livewire::test(Index::class)
            ->set('selected', $products->pluck('id')->map(fn ($id) => (string) $id)->toArray())
            ->call('bulkDeactivate');

        expect(Product::where('status', 'out_of_stock')->count())->toBe(2);
    });

    it('bulkDelete removes selected products', function () {
        $products = Product::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->set('selected', $products->pluck('id')->map(fn ($id) => (string) $id)->toArray())
            ->call('bulkDelete');

        expect(Product::count())->toBe(0);
    });

    it('toggleFavorite flips is_favorite', function () {
        $p = Product::factory()->create(['is_favorite' => false]);

        Livewire::test(Index::class)->call('toggleFavorite', $p->id);

        expect($p->fresh()->is_favorite)->toBeTrue();
    });

    it('toggleGroup adds/removes groupId from openGroups', function () {
        $c = Livewire::test(Index::class)
            ->call('toggleGroup', 'group-abc')
            ->call('toggleGroup', 'group-xyz');

        expect($c->get('openGroups'))->toBe(['group-abc', 'group-xyz']);

        $c->call('toggleGroup', 'group-abc');
        expect($c->get('openGroups'))->toBe(['group-xyz']);
    });

    it('sorts by price_high and stock_low', function () {
        Product::factory()->create(['name' => 'A', 'selling_price' => 100, 'quantity' => 5]);
        Product::factory()->create(['name' => 'B', 'selling_price' => 500, 'quantity' => 20]);
        Product::factory()->create(['name' => 'C', 'selling_price' => 250, 'quantity' => 2]);

        $byPrice = Livewire::test(Index::class)->set('sort', 'price_high')
            ->viewData('products')->pluck('name')->all();
        expect($byPrice)->toBe(['B', 'C', 'A']);

        $byStock = Livewire::test(Index::class)->set('sort', 'stock_low')
            ->viewData('products')->pluck('name')->all();
        expect($byStock)->toBe(['C', 'A', 'B']);
    });
});
