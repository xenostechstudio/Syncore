<?php

/**
 * Master-data hard Delete for Product, surfaced through both module
 * forms (Inventory and Sales). A true `forceDelete()`, distinct from
 * the recoverable Archive, allowed only when no document or stock
 * record references the product (Product::isReferenced()). A referenced
 * product must be Archived instead — forceDelete would cascade-corrupt
 * order line items and stock rows. See "Destructive actions" in CLAUDE.md.
 */

use App\Livewire\Inventory\Products\Form as InventoryForm;
use App\Livewire\Sales\Products\Form as SalesForm;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function referenceProduct(Product $product): void
{
    $warehouse = Warehouse::factory()->create();
    DB::table('inventory_stocks')->insert([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => 7,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('hard-deletes an unreferenced product from the Inventory form', function () {
    actAsAdmin();
    $product = Product::factory()->create();

    Livewire::test(InventoryForm::class, ['id' => $product->id])
        ->assertSet('canDelete', true)
        ->call('delete')
        ->assertRedirect(route('inventory.products.index'));

    expect(Product::withTrashed()->find($product->id))->toBeNull();
});

it('hard-deletes an unreferenced product from the Sales form', function () {
    actAsAdmin();
    $product = Product::factory()->create();

    Livewire::test(SalesForm::class, ['id' => $product->id])
        ->assertSet('canDelete', true)
        ->call('delete')
        ->assertRedirect(route('sales.products.index'));

    expect(Product::withTrashed()->find($product->id))->toBeNull();
});

it('refuses to hard-delete a referenced product from the Inventory form', function () {
    actAsAdmin();
    $product = Product::factory()->create();
    referenceProduct($product);

    Livewire::test(InventoryForm::class, ['id' => $product->id])
        ->assertSet('canDelete', false)
        ->call('delete');

    expect(Product::find($product->id))->not->toBeNull();
});

it('refuses to hard-delete a referenced product from the Sales form', function () {
    actAsAdmin();
    $product = Product::factory()->create();
    referenceProduct($product);

    Livewire::test(SalesForm::class, ['id' => $product->id])
        ->assertSet('canDelete', false)
        ->call('delete');

    expect(Product::find($product->id))->not->toBeNull();
});
