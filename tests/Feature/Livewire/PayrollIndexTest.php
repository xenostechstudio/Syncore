<?php

use App\Livewire\HR\Payroll\Index;
use App\Models\HR\PayrollPeriod;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

describe('Payroll Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('search', '')
            ->assertSet('status', '');
    });

    it('renders list', function () {
        PayrollPeriod::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('periods', fn ($p) => $p->total() === 3);
    });

    it('filters by search on name', function () {
        PayrollPeriod::factory()->create(['name' => 'March 2026 Payroll']);
        PayrollPeriod::factory()->create(['name' => 'April 2026 Payroll']);

        Livewire::test(Index::class)
            ->set('search', 'March')
            ->assertViewHas('periods', fn ($p) => $p->total() === 1);
    });

    it('filters by status', function () {
        PayrollPeriod::factory()->create(['status' => 'draft']);
        PayrollPeriod::factory()->paid()->count(2)->create();

        Livewire::test(Index::class)
            ->set('status', 'paid')
            ->assertViewHas('periods', fn ($p) => $p->total() === 2);
    });

    it('sorts by total_net ascending and descending', function () {
        PayrollPeriod::factory()->create(['name' => 'A', 'total_net' => 100]);
        PayrollPeriod::factory()->create(['name' => 'B', 'total_net' => 500]);
        PayrollPeriod::factory()->create(['name' => 'C', 'total_net' => 250]);

        $asc = Livewire::test(Index::class)->set('sort', 'total_asc')
            ->viewData('periods')->pluck('name')->all();
        expect($asc)->toBe(['A', 'C', 'B']);

        $desc = Livewire::test(Index::class)->set('sort', 'total_desc')
            ->viewData('periods')->pluck('name')->all();
        expect($desc)->toBe(['B', 'C', 'A']);
    });

    it('statistics returns counts by status', function () {
        PayrollPeriod::factory()->count(2)->create(['status' => 'draft']);
        PayrollPeriod::factory()->paid()->count(3)->create(['total_net' => 1000]);

        $c = Livewire::test(Index::class)->set('showStats', true);
        $s = $c->viewData('statistics');
        expect($s['draft'])->toBe(2);
        expect($s['paid'])->toBe(3);
        expect((int) $s['total_amount'])->toBe(3000);
    });
});
