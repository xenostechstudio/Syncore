<?php

use App\Livewire\Sales\Teams\Index;
use App\Models\Sales\SalesTeam;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

describe('SalesTeams Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('filter', 'active')
            ->assertSet('search', '');
    });

    it('renders active teams by default', function () {
        SalesTeam::factory()->count(2)->create();
        SalesTeam::factory()->count(3)->archived()->create();

        Livewire::test(Index::class)
            ->assertViewHas('teams', fn ($p) => $p->total() === 2);
    });

    it('filters by archived/all via setFilter', function () {
        SalesTeam::factory()->count(2)->create();
        SalesTeam::factory()->count(3)->archived()->create();

        Livewire::test(Index::class)
            ->call('setFilter', 'archived')
            ->assertViewHas('teams', fn ($p) => $p->total() === 3);

        Livewire::test(Index::class)
            ->call('setFilter', 'all')
            ->assertViewHas('teams', fn ($p) => $p->total() === 5);
    });

    it('bulkArchive flips active teams to archived', function () {
        $teams = SalesTeam::factory()->count(2)->create();
        $ids = $teams->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        Livewire::test(Index::class)
            ->set('selected', $ids)
            ->call('bulkArchive');

        expect(SalesTeam::where('is_active', false)->count())->toBe(2);
    });

    it('bulkRestore moves archived back to active', function () {
        $teams = SalesTeam::factory()->count(2)->archived()->create();
        $ids = $teams->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        Livewire::test(Index::class)
            ->set('selected', $ids)
            ->call('bulkRestore');

        expect(SalesTeam::where('is_active', true)->count())->toBe(2);
    });

    it('confirmBulkDelete blocks active teams (must be archived first)', function () {
        $active = SalesTeam::factory()->create();
        $archived = SalesTeam::factory()->archived()->create();

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $active->id, (string) $archived->id])
            ->call('confirmBulkDelete');

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect((int) $v['canDelete'][0]['id'])->toBe($archived->id);
        expect($v['cannotDelete'])->toHaveCount(1);
        expect($v['cannotDelete'][0]['reason'])->toBe('Must be archived first');
    });

    it('bulkDelete only removes archived teams', function () {
        $active = SalesTeam::factory()->create();
        $archived = SalesTeam::factory()->archived()->create();

        Livewire::test(Index::class)
            ->set('selected', [(string) $active->id, (string) $archived->id])
            ->call('bulkDelete');

        expect(SalesTeam::find($active->id))->not->toBeNull();
        expect(SalesTeam::find($archived->id))->toBeNull();
    });
});
