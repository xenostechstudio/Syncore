<?php

use App\Livewire\CRM\Opportunities\Index;
use App\Models\CRM\Opportunity;
use App\Models\CRM\Pipeline;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

describe('Opportunities Index', function () {
    it('mounts with kanban view default', function () {
        Livewire::test(Index::class)
            ->assertSet('view', 'kanban')
            ->assertSet('stage', '');
    });

    it('renders list in list view', function () {
        Opportunity::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->set('view', 'list')
            ->assertStatus(200)
            ->assertViewHas('opportunities', fn ($p) => $p->total() === 3);
    });

    it('groups opportunities by pipeline_id in kanban view', function () {
        $p1 = Pipeline::factory()->create();
        $p2 = Pipeline::factory()->create();
        Opportunity::factory()->count(2)->create(['pipeline_id' => $p1->id]);
        Opportunity::factory()->create(['pipeline_id' => $p2->id]);

        $c = Livewire::test(Index::class);
        $grouped = $c->viewData('opportunities');
        expect($grouped)->toHaveKey($p1->id);
        expect($grouped[$p1->id])->toHaveCount(2);
    });

    it('filters by stage', function () {
        $p1 = Pipeline::factory()->create();
        $p2 = Pipeline::factory()->create();
        Opportunity::factory()->create(['pipeline_id' => $p1->id]);
        Opportunity::factory()->count(2)->create(['pipeline_id' => $p2->id]);

        Livewire::test(Index::class)
            ->set('view', 'list')
            ->set('stage', (string) $p2->id)
            ->assertViewHas('opportunities', fn ($p) => $p->total() === 2);
    });

    it('filters by search on name', function () {
        Opportunity::factory()->create(['name' => 'Big Enterprise Deal']);
        Opportunity::factory()->create(['name' => 'SMB Upsell']);

        Livewire::test(Index::class)
            ->set('view', 'list')
            ->set('search', 'Enterprise')
            ->assertViewHas('opportunities', fn ($p) => $p->total() === 1);
    });

    it('moveToStage updates pipeline_id and probability', function () {
        $p1 = Pipeline::factory()->create(['probability' => 25]);
        $p2 = Pipeline::factory()->create(['probability' => 75]);
        $opp = Opportunity::factory()->create(['pipeline_id' => $p1->id, 'probability' => 25]);

        Livewire::test(Index::class)->call('moveToStage', $opp->id, $p2->id);

        $fresh = $opp->fresh();
        expect($fresh->pipeline_id)->toBe($p2->id);
        expect((int) $fresh->probability)->toBe(75);
    });

    it('moveToStage to won pipeline sets won_at', function () {
        $normal = Pipeline::factory()->create();
        $won = Pipeline::factory()->won()->create();
        $opp = Opportunity::factory()->create(['pipeline_id' => $normal->id]);

        Livewire::test(Index::class)->call('moveToStage', $opp->id, $won->id);

        expect($opp->fresh()->won_at)->not->toBeNull();
    });

    it('delete removes opportunity', function () {
        $opp = Opportunity::factory()->create();

        Livewire::test(Index::class)->call('delete', $opp->id);

        expect(Opportunity::withTrashed()->find($opp->id)?->trashed())->toBeTrue();
    });
});
