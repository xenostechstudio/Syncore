<?php

use App\Livewire\Inventory\Transfers\Index;
use App\Models\Inventory\InventoryTransfer;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create()->assignRole('super-admin'));
});

describe('Transfers Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('search', '')
            ->assertSet('sourceWarehouse', '')
            ->assertSet('destinationWarehouse', '');
    });

    it('renders list', function () {
        InventoryTransfer::factory()->count(2)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('transfers', fn ($p) => $p->total() === 2);
    });

    it('filters by source and destination warehouses', function () {
        $w1 = Warehouse::factory()->create();
        $w2 = Warehouse::factory()->create();
        InventoryTransfer::factory()->count(2)->create([
            'source_warehouse_id' => $w1->id,
            'destination_warehouse_id' => $w2->id,
        ]);
        InventoryTransfer::factory()->create([
            'source_warehouse_id' => $w2->id,
            'destination_warehouse_id' => $w1->id,
        ]);

        Livewire::test(Index::class)
            ->set('sourceWarehouse', (string) $w1->id)
            ->assertViewHas('transfers', fn ($p) => $p->total() === 2);
    });

    it('confirmBulkDelete blocks completed transfers', function () {
        $draft = InventoryTransfer::factory()->create(['status' => 'draft']);
        $completed = InventoryTransfer::factory()->completed()->create();

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $draft->id, (string) $completed->id])
            ->call('confirmBulkDelete');

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect($v['cannotDelete'])->toHaveCount(1);
    });

    it('bulkDelete removes only draft/pending transfers', function () {
        $draft = InventoryTransfer::factory()->create(['status' => 'draft']);
        $completed = InventoryTransfer::factory()->completed()->create();

        Livewire::test(Index::class)
            ->set('selected', [(string) $draft->id, (string) $completed->id])
            ->call('bulkDelete');

        expect(InventoryTransfer::find($draft->id))->toBeNull();
        expect(InventoryTransfer::find($completed->id))->not->toBeNull();
    });
});
