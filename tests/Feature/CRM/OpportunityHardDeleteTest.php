<?php

/**
 * Master-data hard Delete: a true `forceDelete()`, distinct from the
 * recoverable Archive. Nothing in the schema carries an `opportunity_id`
 * FK — the won-link is on the opportunity's own `sales_order_id` — so a
 * saved opportunity is always safe to hard-delete. See "Destructive
 * actions" in CLAUDE.md.
 */

use App\Livewire\CRM\Opportunities\Form;
use App\Models\CRM\Opportunity;
use Livewire\Livewire;

it('hard-deletes an opportunity (always allowed — nothing references it)', function () {
    actAsAdmin();
    $opportunity = Opportunity::factory()->create();

    Livewire::test(Form::class, ['id' => $opportunity->id])
        ->assertSet('canDelete', true)
        ->call('delete')
        ->assertRedirect(route('crm.opportunities.index'));

    expect(Opportunity::withTrashed()->find($opportunity->id))->toBeNull();
});
