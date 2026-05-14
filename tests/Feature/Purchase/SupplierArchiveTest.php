<?php

/**
 * Supplier follows the master-data Archive pattern (see "Destructive
 * actions" in CLAUDE.md). Its index has no row-selection UI, so this is
 * the per-row variant: the status filter gains an "Archived" option and
 * each archived row exposes a Restore action.
 */

use App\Livewire\Purchase\Suppliers\Form;
use App\Livewire\Purchase\Suppliers\Index;
use App\Models\Purchase\Supplier;
use Livewire\Livewire;

it('archives a supplier as a recoverable soft delete (not a hard delete)', function () {
    actAsAdmin();
    $supplier = Supplier::factory()->create();

    Livewire::test(Form::class, ['id' => $supplier->id])
        ->call('archive')
        ->assertRedirect(route('purchase.suppliers.index'));

    expect(Supplier::find($supplier->id))->toBeNull();
    expect(Supplier::withTrashed()->find($supplier->id))->not->toBeNull();
    expect(Supplier::withTrashed()->find($supplier->id)->trashed())->toBeTrue();
});

it('hides archived suppliers by default, shows them under the Archived status filter', function () {
    actAsAdmin();
    $live = Supplier::factory()->create(['name' => 'Live Supplier '.uniqid()]);
    $archived = Supplier::factory()->create(['name' => 'Archived Supplier '.uniqid()]);
    $archived->delete();

    // Default (status = all): live shown, archived hidden.
    Livewire::test(Index::class)
        ->assertSee($live->name)
        ->assertDontSee($archived->name);

    // status = archived: only archived shown.
    Livewire::test(Index::class)
        ->set('status', 'archived')
        ->assertSee($archived->name)
        ->assertDontSee($live->name);
});

it('restores an archived supplier via the per-row restore action', function () {
    actAsAdmin();
    $supplier = Supplier::factory()->create();
    $supplier->delete();

    expect(Supplier::find($supplier->id))->toBeNull();

    Livewire::test(Index::class)
        ->set('status', 'archived')
        ->call('restore', $supplier->id);

    expect(Supplier::find($supplier->id))->not->toBeNull();
});
