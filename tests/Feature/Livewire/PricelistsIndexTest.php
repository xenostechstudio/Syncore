<?php

use App\Models\Sales\Pricelist;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create()->assignRole('super-admin'));
});

describe('Sales Pricelists Index', function () {
    it('renders and filters', function () {
        Pricelist::factory()->create(['name' => 'Retail', 'code' => 'RTL']);
        Pricelist::factory()->create(['name' => 'Wholesale', 'code' => 'WHS']);

        Livewire::test(\App\Livewire\Sales\Configuration\Pricelists\Index::class)
            ->assertStatus(200)
            ->assertViewHas('pricelists', fn ($p) => $p->total() === 2);

        Livewire::test(\App\Livewire\Sales\Configuration\Pricelists\Index::class)
            ->set('search', 'Retail')
            ->assertViewHas('pricelists', fn ($p) => $p->total() === 1);
    });

    it('selectAll, deleteSelected, activate/deactivate work', function () {
        $pls = Pricelist::factory()->count(2)->inactive()->create();
        $ids = $pls->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        Livewire::test(\App\Livewire\Sales\Configuration\Pricelists\Index::class)
            ->set('selected', $ids)
            ->call('activateSelected');
        expect(Pricelist::where('is_active', true)->count())->toBe(2);

        Livewire::test(\App\Livewire\Sales\Configuration\Pricelists\Index::class)
            ->set('selected', $ids)
            ->call('deactivateSelected');
        expect(Pricelist::where('is_active', false)->count())->toBe(2);

        Livewire::test(\App\Livewire\Sales\Configuration\Pricelists\Index::class)
            ->set('selected', $ids)
            ->call('deleteSelected');
        expect(Pricelist::count())->toBe(0);
    });
});

describe('Inventory Pricelists Index (simpler wrapper)', function () {
    it('renders and filters by search', function () {
        Pricelist::factory()->create(['name' => 'VIP']);
        Pricelist::factory()->create(['name' => 'Standard']);

        Livewire::test(\App\Livewire\Inventory\Products\Pricelists\Index::class)
            ->assertStatus(200)
            ->assertViewHas('pricelists', fn ($p) => $p->total() === 2);

        Livewire::test(\App\Livewire\Inventory\Products\Pricelists\Index::class)
            ->set('search', 'VIP')
            ->assertViewHas('pricelists', fn ($p) => $p->total() === 1);
    });

    it('deleteSelected removes selected pricelists', function () {
        $pls = Pricelist::factory()->count(2)->create();

        Livewire::test(\App\Livewire\Inventory\Products\Pricelists\Index::class)
            ->set('selected', $pls->pluck('id')->map(fn ($id) => (string) $id)->toArray())
            ->call('deleteSelected');

        expect(Pricelist::count())->toBe(0);
    });
});
