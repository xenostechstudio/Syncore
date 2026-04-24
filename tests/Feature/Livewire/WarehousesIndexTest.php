<?php

use App\Livewire\Inventory\Warehouses\Index;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

function attachWarehouseProduct(int $warehouseId, ?string $sku = null): int
{
    return DB::table('products')->insertGetId([
        'name' => fake()->words(2, true),
        'sku' => $sku ?? 'SKU-' . uniqid(),
        'warehouse_id' => $warehouseId,
        'product_type' => 'goods',
        'quantity' => 10,
        'status' => 'in_stock',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

describe('Warehouses Index', function () {
    it('mounts with view=grid and default perPage', function () {
        Livewire::test(Index::class)
            ->assertSet('view', 'grid')
            ->assertSet('perPage', 15);
    });

    it('renders list', function () {
        Warehouse::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('warehouses', fn ($p) => $p->total() === 3);
    });

    it('filters by search on name or location', function () {
        Warehouse::factory()->create(['name' => 'North Hub', 'location' => 'Jakarta']);
        Warehouse::factory()->create(['name' => 'South Hub', 'location' => 'Surabaya']);

        Livewire::test(Index::class)
            ->set('search', 'North')
            ->assertViewHas('warehouses', fn ($p) => $p->total() === 1);

        Livewire::test(Index::class)
            ->set('search', 'Surabaya')
            ->assertViewHas('warehouses', fn ($p) => $p->total() === 1);
    });

    it('confirmBulkDelete separates warehouses by product count', function () {
        $empty = Warehouse::factory()->create(['name' => 'Empty Hub']);
        $used = Warehouse::factory()->create(['name' => 'Stocked Hub']);
        attachWarehouseProduct($used->id);
        attachWarehouseProduct($used->id);

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $empty->id, (string) $used->id])
            ->call('confirmBulkDelete')
            ->assertSet('showDeleteConfirm', true);

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect($v['canDelete'][0]['name'])->toBe('Empty Hub');
        expect($v['cannotDelete'])->toHaveCount(1);
        expect($v['cannotDelete'][0]['reason'])->toBe('Has 2 products in stock');
    });

    it('bulkDelete removes only empty warehouses', function () {
        $empty = Warehouse::factory()->create();
        $used = Warehouse::factory()->create();
        attachWarehouseProduct($used->id);

        Livewire::test(Index::class)
            ->set('selected', [(string) $empty->id, (string) $used->id])
            ->call('bulkDelete');

        expect(Warehouse::find($empty->id))->toBeNull();
        expect(Warehouse::find($used->id))->not->toBeNull();
    });

    it('bulkDelete returns early when all have products', function () {
        $a = Warehouse::factory()->create();
        $b = Warehouse::factory()->create();
        attachWarehouseProduct($a->id);
        attachWarehouseProduct($b->id);

        Livewire::test(Index::class)
            ->set('selected', [(string) $a->id, (string) $b->id])
            ->call('bulkDelete');

        expect(Warehouse::count())->toBe(2);
    });

    it('delete removes a single warehouse', function () {
        $w = Warehouse::factory()->create();

        Livewire::test(Index::class)->call('delete', $w->id);

        expect(Warehouse::find($w->id))->toBeNull();
    });
});
