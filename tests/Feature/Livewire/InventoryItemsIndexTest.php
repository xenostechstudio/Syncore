<?php

use App\Livewire\Inventory\Items\Index;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create()->assignRole('super-admin'));
});

function setStock(int $productId, int $warehouseId, int $qty): void
{
    DB::table('inventory_stocks')->insert([
        'product_id' => $productId,
        'warehouse_id' => $warehouseId,
        'quantity' => $qty,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

describe('Inventory Items Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('perPage', 15)
            ->assertSet('search', '')
            ->assertSet('status', '');
    });

    it('renders list', function () {
        Product::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('items', fn ($p) => $p->total() === 3);
    });

    it('filters by search on name/sku', function () {
        Product::factory()->create(['name' => 'Alpha Widget', 'sku' => 'AW-001']);
        Product::factory()->create(['name' => 'Beta Gadget', 'sku' => 'BG-002']);

        Livewire::test(Index::class)
            ->set('search', 'Alpha')
            ->assertViewHas('items', fn ($p) => $p->total() === 1);

        Livewire::test(Index::class)
            ->set('search', 'BG-002')
            ->assertViewHas('items', fn ($p) => $p->total() === 1);
    });

    it('filters by status', function () {
        Product::factory()->inStock()->count(2)->create();
        Product::factory()->outOfStock()->count(3)->create();

        Livewire::test(Index::class)
            ->set('status', 'out_of_stock')
            ->assertViewHas('items', fn ($p) => $p->total() === 3);
    });

    it('delete refuses products with stock', function () {
        $w = Warehouse::factory()->create();
        $p = Product::factory()->create();
        setStock($p->id, $w->id, 5);

        Livewire::test(Index::class)->call('delete', $p->id);

        expect(Product::find($p->id))->not->toBeNull();
    });

    it('delete removes products with zero stock', function () {
        $p = Product::factory()->create();

        Livewire::test(Index::class)->call('delete', $p->id);

        expect(Product::find($p->id))->toBeNull();
    });

    it('confirmBulkDelete splits products by stock totals', function () {
        $w = Warehouse::factory()->create();
        $empty = Product::factory()->create(['name' => 'Empty P']);
        $stocked = Product::factory()->create(['name' => 'Stocked P']);
        setStock($stocked->id, $w->id, 12);

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $empty->id, (string) $stocked->id])
            ->call('confirmBulkDelete');

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect($v['cannotDelete'])->toHaveCount(1);
        expect($v['cannotDelete'][0]['reason'])->toBe('Has 12 units in stock');
    });

    it('bulkDelete removes only products without stock', function () {
        $w = Warehouse::factory()->create();
        $empty = Product::factory()->create();
        $stocked = Product::factory()->create();
        setStock($stocked->id, $w->id, 3);

        Livewire::test(Index::class)
            ->set('selected', [(string) $empty->id, (string) $stocked->id])
            ->call('bulkDelete');

        expect(Product::find($empty->id))->toBeNull();
        expect(Product::find($stocked->id))->not->toBeNull();
    });

    it('bulkActivate sets status to in_stock', function () {
        $products = Product::factory()->outOfStock()->count(2)->create();

        Livewire::test(Index::class)
            ->set('selected', $products->pluck('id')->map(fn ($id) => (string) $id)->toArray())
            ->call('bulkActivate');

        expect(Product::where('status', 'in_stock')->count())->toBe(2);
    });

    it('toggleFavorite flips is_favorite', function () {
        $p = Product::factory()->create(['is_favorite' => false]);

        Livewire::test(Index::class)->call('toggleFavorite', $p->id);

        expect($p->fresh()->is_favorite)->toBeTrue();
    });
});
