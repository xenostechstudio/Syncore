<?php

/**
 * Master-data hard Delete: a true `forceDelete()`, distinct from the
 * recoverable Archive, allowed only when nothing references the lead.
 * A lead with opportunities must be Archived instead. See "Destructive
 * actions" in CLAUDE.md.
 */

use App\Livewire\CRM\Leads\Form;
use App\Models\CRM\Lead;
use App\Models\CRM\Opportunity;
use Livewire\Livewire;

it('hard-deletes an unreferenced lead', function () {
    actAsAdmin();
    $lead = Lead::factory()->create();

    Livewire::test(Form::class, ['id' => $lead->id])
        ->assertSet('canDelete', true)
        ->call('delete')
        ->assertRedirect(route('crm.leads.index'));

    expect(Lead::withTrashed()->find($lead->id))->toBeNull();
});

it('refuses to hard-delete a lead that has opportunities', function () {
    actAsAdmin();
    $lead = Lead::factory()->create();
    Opportunity::factory()->create(['lead_id' => $lead->id]);

    Livewire::test(Form::class, ['id' => $lead->id])
        ->assertSet('canDelete', false)
        ->call('delete');

    expect(Lead::find($lead->id))->not->toBeNull();
});
