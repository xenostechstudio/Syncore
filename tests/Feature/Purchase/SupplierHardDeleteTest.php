<?php

/**
 * Master-data hard Delete: a true `forceDelete()`, distinct from the
 * recoverable Archive, allowed only when nothing references the
 * supplier. A supplier with purchase orders or vendor bills must be
 * Archived instead. See "Destructive actions" in CLAUDE.md.
 */

use App\Livewire\Purchase\Suppliers\Form;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\Supplier;
use Livewire\Livewire;

it('hard-deletes an unreferenced supplier', function () {
    actAsAdmin();
    $supplier = Supplier::factory()->create();

    Livewire::test(Form::class, ['id' => $supplier->id])
        ->assertSet('canDelete', true)
        ->call('delete')
        ->assertRedirect(route('purchase.suppliers.index'));

    expect(Supplier::withTrashed()->find($supplier->id))->toBeNull();
});

it('refuses to hard-delete a supplier that has purchase orders', function () {
    actAsAdmin();
    $supplier = Supplier::factory()->create();
    PurchaseRfq::factory()->create(['supplier_id' => $supplier->id]);

    Livewire::test(Form::class, ['id' => $supplier->id])
        ->assertSet('canDelete', false)
        ->call('delete');

    expect(Supplier::find($supplier->id))->not->toBeNull();
});
