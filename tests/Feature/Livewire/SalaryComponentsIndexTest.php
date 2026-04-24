<?php

use App\Livewire\HR\Payroll\Components\Index;
use App\Models\HR\SalaryComponent;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

describe('Salary Components Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('sort', 'sort_order')
            ->assertSet('componentType', '')
            ->assertSet('search', '')
            ->assertSet('showStats', false);
    });

    it('renders list', function () {
        SalaryComponent::factory()->count(4)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('components', fn ($p) => $p->total() === 4);
    });

    it('filters by search', function () {
        SalaryComponent::factory()->create(['name' => 'Basic Salary', 'code' => 'BSC']);
        SalaryComponent::factory()->create(['name' => 'Bonus', 'code' => 'BNS']);

        Livewire::test(Index::class)
            ->set('search', 'Basic')
            ->assertViewHas('components', fn ($p) => $p->total() === 1);
    });

    it('filters by component type', function () {
        SalaryComponent::factory()->earning()->count(2)->create();
        SalaryComponent::factory()->deduction()->count(3)->create();

        Livewire::test(Index::class)
            ->set('componentType', 'earning')
            ->assertViewHas('components', fn ($p) => $p->total() === 2);

        Livewire::test(Index::class)
            ->set('componentType', 'deduction')
            ->assertViewHas('components', fn ($p) => $p->total() === 3);
    });

    it('resets page when componentType changes', function () {
        SalaryComponent::factory()->earning()->create();
        Livewire::test(Index::class)
            ->set('page', 3)
            ->set('componentType', 'earning')
            ->assertSet('page', 1);
    });

    it('sorts by name and by amount', function () {
        SalaryComponent::factory()->create(['name' => 'Alpha', 'default_amount' => 100]);
        SalaryComponent::factory()->create(['name' => 'Bravo', 'default_amount' => 500]);
        SalaryComponent::factory()->create(['name' => 'Charlie', 'default_amount' => 250]);

        $byNameAsc = Livewire::test(Index::class)->set('sort', 'name_asc')
            ->viewData('components')->pluck('name')->all();
        expect($byNameAsc)->toBe(['Alpha', 'Bravo', 'Charlie']);

        $byAmountDesc = Livewire::test(Index::class)->set('sort', 'amount_desc')
            ->viewData('components')->pluck('name')->all();
        expect($byAmountDesc)->toBe(['Bravo', 'Charlie', 'Alpha']);
    });

    it('toggleStats flips showStats and populates statistics', function () {
        SalaryComponent::factory()->earning()->count(2)->create();
        SalaryComponent::factory()->deduction()->count(1)->create();

        $c = Livewire::test(Index::class)->call('toggleStats');
        expect($c->get('showStats'))->toBeTrue();

        $stats = $c->viewData('statistics');
        expect($stats['total'])->toBe(3);
        expect($stats['earnings'])->toBe(2);
        expect($stats['deductions'])->toBe(1);
    });

    it('selectAll populates $selected', function () {
        SalaryComponent::factory()->count(3)->create();
        $c = Livewire::test(Index::class)->set('selectAll', true);
        expect($c->get('selected'))->toHaveCount(3);
    });
});
