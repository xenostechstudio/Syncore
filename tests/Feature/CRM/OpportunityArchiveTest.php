<?php

/**
 * Opportunity follows the master-data Archive pattern (see "Destructive
 * actions" in CLAUDE.md). Its index is kanban-default with no
 * row-selection UI, so this is the per-row variant: the stage filter
 * gains an "Archived" pseudo-option, archived always renders the list
 * view, and each archived row exposes a Restore action.
 */

use App\Livewire\CRM\Opportunities\Form;
use App\Livewire\CRM\Opportunities\Index;
use App\Models\CRM\Opportunity;
use Livewire\Livewire;

it('archives an opportunity as a recoverable soft delete (not a hard delete)', function () {
    actAsAdmin();
    $opportunity = Opportunity::factory()->create();

    Livewire::test(Form::class, ['id' => $opportunity->id])
        ->call('archive')
        ->assertRedirect(route('crm.opportunities.index'));

    expect(Opportunity::find($opportunity->id))->toBeNull();
    expect(Opportunity::withTrashed()->find($opportunity->id))->not->toBeNull();
    expect(Opportunity::withTrashed()->find($opportunity->id)->trashed())->toBeTrue();
});

it('hides archived opportunities by default, shows them under the Archived stage filter', function () {
    actAsAdmin();
    $live = Opportunity::factory()->create(['name' => 'Live Opp '.uniqid()]);
    $archived = Opportunity::factory()->create(['name' => 'Archived Opp '.uniqid()]);
    $archived->delete();

    Livewire::test(Index::class)
        ->set('view', 'list')
        ->assertSee($live->name)
        ->assertDontSee($archived->name);

    Livewire::test(Index::class)
        ->set('stage', 'archived')
        ->assertSee($archived->name)
        ->assertDontSee($live->name);
});

it('restores an archived opportunity via the per-row restore action', function () {
    actAsAdmin();
    $opportunity = Opportunity::factory()->create();
    $opportunity->delete();

    expect(Opportunity::find($opportunity->id))->toBeNull();

    Livewire::test(Index::class)
        ->set('stage', 'archived')
        ->call('restore', $opportunity->id);

    expect(Opportunity::find($opportunity->id))->not->toBeNull();
});
