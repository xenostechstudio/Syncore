<?php

/**
 * Product follows the master-data Archive pattern (see "Destructive
 * actions" in CLAUDE.md), but uniquely across two module surfaces —
 * Inventory and Sales — that share the same underlying model. Both
 * forms' destructive action is Archive (a recoverable soft delete) and
 * both indexes expose an "Archived" pseudo-option on the status filter
 * plus a Restore path. The Inventory index additionally keeps its
 * stock guard: a product with units on hand cannot be archived.
 */

use App\Livewire\Inventory\Items\Index as InventoryIndex;
use App\Livewire\Inventory\Products\Form as InventoryForm;
use App\Livewire\Sales\Products\Form as SalesForm;
use App\Livewire\Sales\Products\Index as SalesIndex;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

it('archives a product from the Inventory form as a recoverable soft delete', function () {
    actAsAdmin();
    $product = Product::factory()->create();

    Livewire::test(InventoryForm::class, ['id' => $product->id])
        ->call('archive')
        ->assertRedirect(route('inventory.products.index'));

    expect(Product::find($product->id))->toBeNull();
    expect(Product::withTrashed()->find($product->id)->trashed())->toBeTrue();
});

it('archives a product from the Sales form as a recoverable soft delete', function () {
    actAsAdmin();
    $product = Product::factory()->create();

    Livewire::test(SalesForm::class, ['id' => $product->id])
        ->call('archive')
        ->assertRedirect(route('sales.products.index'));

    expect(Product::find($product->id))->toBeNull();
    expect(Product::withTrashed()->find($product->id)->trashed())->toBeTrue();
});

it('hides archived products from the Inventory index by default, shows them under the Archived filter', function () {
    actAsAdmin();
    $live = Product::factory()->create(['name' => 'Live Item '.uniqid()]);
    $archived = Product::factory()->create(['name' => 'Archived Item '.uniqid()]);
    $archived->delete();

    Livewire::test(InventoryIndex::class)
        ->set('view', 'list')
        ->assertSee($live->name)
        ->assertDontSee($archived->name);

    Livewire::test(InventoryIndex::class)
        ->set('status', 'archived')
        ->assertSee($archived->name)
        ->assertDontSee($live->name);
});

it('hides archived products from the Sales index by default, shows them under the Archived filter', function () {
    actAsAdmin();
    $live = Product::factory()->create(['name' => 'Live Product '.uniqid()]);
    $archived = Product::factory()->create(['name' => 'Archived Product '.uniqid()]);
    $archived->delete();

    Livewire::test(SalesIndex::class)
        ->set('view', 'list')
        ->assertSee($live->name)
        ->assertDontSee($archived->name);

    Livewire::test(SalesIndex::class)
        ->set('status', 'archived')
        ->assertSee($archived->name)
        ->assertDontSee($live->name);
});

it('archives and restores a product via the Inventory index per-row actions', function () {
    actAsAdmin();
    $product = Product::factory()->create();

    Livewire::test(InventoryIndex::class)->call('archive', $product->id);
    expect(Product::find($product->id))->toBeNull();

    Livewire::test(InventoryIndex::class)
        ->set('status', 'archived')
        ->call('restore', $product->id);
    expect(Product::find($product->id))->not->toBeNull();
});

it('refuses to archive an Inventory product that still has units in stock', function () {
    actAsAdmin();
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    DB::table('inventory_stocks')->insert([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => 25,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(InventoryIndex::class)->call('archive', $product->id);

    expect(Product::find($product->id))->not->toBeNull();
});

it('restores archived products via bulkRestore on both index surfaces', function () {
    actAsAdmin();
    $a = Product::factory()->create();
    $b = Product::factory()->create();
    $a->delete();
    $b->delete();

    Livewire::test(InventoryIndex::class)
        ->set('status', 'archived')
        ->set('selected', [$a->id])
        ->call('bulkRestore');
    expect(Product::find($a->id))->not->toBeNull();

    Livewire::test(SalesIndex::class)
        ->set('status', 'archived')
        ->set('selected', [$b->id])
        ->call('bulkRestore');
    expect(Product::find($b->id))->not->toBeNull();
});

it('archives selected products via bulkDelete on the Sales index', function () {
    actAsAdmin();
    $a = Product::factory()->create();
    $b = Product::factory()->create();

    Livewire::test(SalesIndex::class)
        ->set('selected', [$a->id, $b->id])
        ->call('bulkDelete');

    expect(Product::whereIn('id', [$a->id, $b->id])->count())->toBe(0);
    expect(Product::withTrashed()->whereIn('id', [$a->id, $b->id])->count())->toBe(2);
});
