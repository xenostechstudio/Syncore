<?php

use App\Livewire\Sales\Configuration\Taxes\Index;
use App\Models\Sales\Tax;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create()->assignRole('super-admin'));
});

describe('Taxes Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('search', '')
            ->assertSet('selected', []);
    });

    it('renders list', function () {
        Tax::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('taxes', fn ($p) => $p->total() === 3);
    });

    it('filters by search', function () {
        Tax::factory()->create(['name' => 'VAT', 'code' => 'VAT']);
        Tax::factory()->create(['name' => 'Sales Tax', 'code' => 'ST']);

        Livewire::test(Index::class)
            ->set('search', 'VAT')
            ->assertViewHas('taxes', fn ($p) => $p->total() === 1);
    });

    it('selectAll populates $selected', function () {
        Tax::factory()->count(3)->create();
        $c = Livewire::test(Index::class)->set('selectAll', true);
        expect($c->get('selected'))->toHaveCount(3);
    });

    it('confirmBulkDelete marks all selected as deletable', function () {
        $taxes = Tax::factory()->count(2)->create(['rate' => 10]);

        $c = Livewire::test(Index::class)
            ->set('selected', $taxes->pluck('id')->map(fn ($id) => (string) $id)->toArray())
            ->call('confirmBulkDelete')
            ->assertSet('showDeleteConfirm', true);

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(2);
        expect($v['cannotDelete'])->toHaveCount(0);
    });

    it('bulkDelete removes selected taxes', function () {
        $taxes = Tax::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->set('selected', $taxes->pluck('id')->map(fn ($id) => (string) $id)->toArray())
            ->call('bulkDelete')
            ->assertSet('showDeleteConfirm', false)
            ->assertSet('selected', []);

        expect(Tax::count())->toBe(0);
    });

    it('activateSelected and deactivateSelected flip is_active', function () {
        $taxes = Tax::factory()->count(2)->inactive()->create();
        $ids = $taxes->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        Livewire::test(Index::class)
            ->set('selected', $ids)
            ->call('activateSelected')
            ->assertSet('selected', []);

        expect(Tax::where('is_active', true)->count())->toBe(2);

        Livewire::test(Index::class)
            ->set('selected', $ids)
            ->call('deactivateSelected');

        expect(Tax::where('is_active', false)->count())->toBe(2);
    });
});
