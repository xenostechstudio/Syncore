<?php

/**
 * Lead follows the master-data Archive pattern (see "Destructive
 * actions" in CLAUDE.md). It's the hybrid shape: a radio status filter
 * gains an "Archived" option, and recovery is offered both as
 * bulkRestore (list selection toolbar) and restore (grid cards).
 */

use App\Livewire\CRM\Leads\Form;
use App\Livewire\CRM\Leads\Index;
use App\Models\CRM\Lead;
use Livewire\Livewire;

it('archives a lead as a recoverable soft delete (not a hard delete)', function () {
    actAsAdmin();
    $lead = Lead::factory()->create();

    Livewire::test(Form::class, ['id' => $lead->id])
        ->call('archive')
        ->assertRedirect(route('crm.leads.index'));

    expect(Lead::find($lead->id))->toBeNull();
    expect(Lead::withTrashed()->find($lead->id))->not->toBeNull();
    expect(Lead::withTrashed()->find($lead->id)->trashed())->toBeTrue();
});

it('hides archived leads by default, shows them under the Archived status filter', function () {
    actAsAdmin();
    $live = Lead::factory()->create(['name' => 'Live Lead '.uniqid()]);
    $archived = Lead::factory()->create(['name' => 'Archived Lead '.uniqid()]);
    $archived->delete();

    Livewire::test(Index::class)
        ->assertSee($live->name)
        ->assertDontSee($archived->name);

    Livewire::test(Index::class)
        ->set('status', 'archived')
        ->assertSee($archived->name)
        ->assertDontSee($live->name);
});

it('restores archived leads via bulkRestore (list selection)', function () {
    actAsAdmin();
    $a = Lead::factory()->create();
    $b = Lead::factory()->create();
    $a->delete();
    $b->delete();

    expect(Lead::whereIn('id', [$a->id, $b->id])->count())->toBe(0);

    Livewire::test(Index::class)
        ->set('status', 'archived')
        ->set('selected', [$a->id, $b->id])
        ->call('bulkRestore');

    expect(Lead::whereIn('id', [$a->id, $b->id])->count())->toBe(2);
});

it('restores a single archived lead via restore (grid card)', function () {
    actAsAdmin();
    $lead = Lead::factory()->create();
    $lead->delete();

    expect(Lead::find($lead->id))->toBeNull();

    Livewire::test(Index::class)
        ->set('status', 'archived')
        ->call('restore', $lead->id);

    expect(Lead::find($lead->id))->not->toBeNull();
});
