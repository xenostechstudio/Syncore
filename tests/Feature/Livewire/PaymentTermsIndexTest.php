<?php

use App\Livewire\Sales\Configuration\PaymentTerms\Index;
use App\Models\Sales\PaymentTerm;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create()->assignRole('super-admin'));
});

describe('PaymentTerms Index', function () {
    it('mounts and renders', function () {
        PaymentTerm::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertSet('search', '')
            ->assertViewHas('paymentTerms', fn ($p) => $p->total() === 3);
    });

    it('filters by search on name or code', function () {
        PaymentTerm::factory()->create(['name' => 'Net 30', 'code' => 'N30']);
        PaymentTerm::factory()->create(['name' => 'Immediate', 'code' => 'IMM']);

        Livewire::test(Index::class)
            ->set('search', 'Net')
            ->assertViewHas('paymentTerms', fn ($p) => $p->total() === 1);

        Livewire::test(Index::class)
            ->set('search', 'IMM')
            ->assertViewHas('paymentTerms', fn ($p) => $p->total() === 1);
    });

    it('selectAll populates $selected', function () {
        PaymentTerm::factory()->count(3)->create();
        $c = Livewire::test(Index::class)->set('selectAll', true);
        expect($c->get('selected'))->toHaveCount(3);
    });

    it('deleteSelected removes selected terms', function () {
        $terms = PaymentTerm::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->set('selected', $terms->pluck('id')->map(fn ($id) => (string) $id)->toArray())
            ->call('deleteSelected')
            ->assertSet('selected', []);

        expect(PaymentTerm::count())->toBe(0);
    });

    it('activateSelected and deactivateSelected flip is_active', function () {
        $terms = PaymentTerm::factory()->count(2)->inactive()->create();
        $ids = $terms->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        Livewire::test(Index::class)
            ->set('selected', $ids)
            ->call('activateSelected');

        expect(PaymentTerm::where('is_active', true)->count())->toBe(2);

        Livewire::test(Index::class)
            ->set('selected', $ids)
            ->call('deactivateSelected');

        expect(PaymentTerm::where('is_active', false)->count())->toBe(2);
    });
});
