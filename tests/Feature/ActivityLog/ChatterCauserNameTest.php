<?php

use App\Livewire\Sales\Customers\Form;
use App\Models\Sales\Customer;
use App\Models\User;
use Livewire\Livewire;

/**
 * Regression: the chatter timeline showed "System" as the causer on every
 * activity, regardless of who actually performed the action. The DB row
 * stored user_id + user_name correctly via ActivityLogService; the bug
 * was in WithNotes::getActivitiesAndNotesProperty, which wrapped the
 * causer fields under a `causer` sub-object — but x-ui.activity-item
 * reads $activity->user_id / $activity->user_name flat. The mismatch
 * meant activity-item's null-causer fallback in user-name.blade.php:77
 * rendered "System" for everything.
 *
 * This test creates a record as a real user and asserts the timeline
 * exposes that user's id + name on the activity, not "System".
 */
it('chatter timeline attributes activities to the actual causer (not System)', function () {
    $actor = User::factory()->create(['name' => 'Alice Causer']);
    $this->actingAs($actor);

    // Create the customer — LogsActivity hooks the `created` event and
    // ActivityLogService::log writes a row with user_id = Auth::id().
    $customer = Customer::create([
        'type' => 'person',
        'name' => 'Test Customer '.uniqid(),
        'country' => 'ID',
        'status' => 'active',
    ]);

    // Mount the Livewire form for this customer and pull the computed
    // activities-and-notes timeline that the chatter blade renders from.
    $component = Livewire::test(Form::class, ['id' => $customer->id]);
    $timeline = $component->instance()->activitiesAndNotes;

    expect($timeline)->not->toBeEmpty();

    $createdActivity = $timeline->first(fn ($item) => $item['type'] === 'activity' && $item['data']->action === 'created');
    expect($createdActivity)->not->toBeNull('Expected a "created" activity row in the chatter timeline');

    // The data object must expose user_id + user_name FLAT — that's what
    // x-ui.activity-item reads. Wrapping them under `causer` (the prior
    // shape) silently fell through to the "System" fallback.
    $data = $createdActivity['data'];
    expect($data->user_id)->toBe($actor->id);
    expect($data->user_name)->toBe('Alice Causer');
    expect($data->user_name)->not->toBe('System');
});

it('chatter prefers the live users.name (renames are reflected immediately)', function () {
    $actor = User::factory()->create(['name' => 'Carol Old Name']);
    $this->actingAs($actor);

    $customer = Customer::create([
        'type' => 'person',
        'name' => 'Test Customer '.uniqid(),
        'country' => 'ID',
        'status' => 'active',
    ]);

    // Rename the actor — the activity_logs row still has the snapshot
    // "Carol Old Name" in user_name, but the LEFT JOIN to users.name
    // (causer_name) should win, so the timeline reflects the rename.
    $actor->update(['name' => 'Carol New Name']);

    $timeline = Livewire::test(Form::class, ['id' => $customer->id])
        ->instance()
        ->activitiesAndNotes;

    $createdActivity = $timeline->first(fn ($item) => $item['type'] === 'activity' && $item['data']->action === 'created');
    expect($createdActivity['data']->user_name)->toBe('Carol New Name');
});
