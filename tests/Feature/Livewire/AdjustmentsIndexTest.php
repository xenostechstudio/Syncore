<?php

use App\Livewire\Inventory\Adjustments\Index;
use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

describe('Adjustments Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('search', '')
            ->assertSet('status', '')
            ->assertSet('warehouse', '')
            ->assertSet('adjustmentType', '');
    });

    it('renders list', function () {
        InventoryAdjustment::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('adjustments', fn ($p) => $p->total() === 3);
    });

    it('filters by warehouse and adjustmentType', function () {
        $w1 = Warehouse::factory()->create();
        $w2 = Warehouse::factory()->create();
        InventoryAdjustment::factory()->count(2)->create(['warehouse_id' => $w1->id, 'adjustment_type' => 'increase']);
        InventoryAdjustment::factory()->count(3)->create(['warehouse_id' => $w2->id, 'adjustment_type' => 'decrease']);

        Livewire::test(Index::class)
            ->set('warehouse', (string) $w1->id)
            ->assertViewHas('adjustments', fn ($p) => $p->total() === 2);

        Livewire::test(Index::class)
            ->set('adjustmentType', 'decrease')
            ->assertViewHas('adjustments', fn ($p) => $p->total() === 3);
    });

    it('confirmBulkDelete splits by draft/pending status', function () {
        $draft = InventoryAdjustment::factory()->create(['status' => 'draft']);
        $completed = InventoryAdjustment::factory()->completed()->create();

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $draft->id, (string) $completed->id])
            ->call('confirmBulkDelete');

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect($v['cannotDelete'])->toHaveCount(1);
    });

    it('bulkDelete removes only draft/pending adjustments', function () {
        $draft = InventoryAdjustment::factory()->create(['status' => 'draft']);
        $completed = InventoryAdjustment::factory()->completed()->create();

        Livewire::test(Index::class)
            ->set('selected', [(string) $draft->id, (string) $completed->id])
            ->call('bulkDelete');

        expect(InventoryAdjustment::find($draft->id))->toBeNull();
        expect(InventoryAdjustment::find($completed->id))->not->toBeNull();
    });

    it('sortBy toggles direction on same field', function () {
        Livewire::test(Index::class)
            ->assertSet('sortDirection', 'desc')
            ->call('sortBy', 'adjustment_number')
            ->assertSet('sortField', 'adjustment_number')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'adjustment_number')
            ->assertSet('sortDirection', 'desc');
    });
});
