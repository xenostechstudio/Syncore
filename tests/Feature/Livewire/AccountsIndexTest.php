<?php

use App\Livewire\Accounting\Accounts\Index;
use App\Models\Accounting\Account;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

describe('Accounts Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('search', '')
            ->assertSet('type', '');
    });

    it('renders list', function () {
        Account::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('accounts', fn ($p) => $p->total() === 3);
    });

    it('filters by search on name or code', function () {
        Account::factory()->create(['name' => 'Cash', 'code' => '1000']);
        Account::factory()->create(['name' => 'Accounts Receivable', 'code' => '1200']);

        Livewire::test(Index::class)
            ->set('search', 'Cash')
            ->assertViewHas('accounts', fn ($p) => $p->total() === 1);

        Livewire::test(Index::class)
            ->set('search', '1200')
            ->assertViewHas('accounts', fn ($p) => $p->total() === 1);
    });

    it('filters by type', function () {
        Account::factory()->create(['type' => 'asset']);
        Account::factory()->count(2)->create(['type' => 'liability']);

        Livewire::test(Index::class)
            ->set('type', 'liability')
            ->assertViewHas('accounts', fn ($p) => $p->total() === 2);
    });

    it('delete removes a normal account', function () {
        $a = Account::factory()->create();

        Livewire::test(Index::class)->call('delete', $a->id);

        expect(Account::find($a->id))->toBeNull();
    });

    it('delete refuses system accounts', function () {
        $sys = Account::factory()->system()->create();

        Livewire::test(Index::class)->call('delete', $sys->id);

        expect(Account::find($sys->id))->not->toBeNull();
    });
});
