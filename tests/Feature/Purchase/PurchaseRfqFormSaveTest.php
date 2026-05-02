<?php

use App\Livewire\Purchase\Rfq\Form;
use App\Models\Purchase\Supplier;
use App\Models\User;
use Database\Seeders\ModulePermissionSeeder;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(ModulePermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('super-admin');
    $this->actingAs($this->user);
});

it('stores NULL when expected_arrival is left blank (regression: pgsql 22007)', function () {
    $supplier = Supplier::factory()->create();

    Livewire::test(Form::class)
        ->set('supplier_id', $supplier->id)
        ->set('supplier_name', $supplier->name)
        ->set('order_date', '2026-05-02')
        ->set('expected_arrival', '')
        ->call('save')
        ->assertHasNoErrors();

    $row = DB::table('purchase_rfqs')->where('supplier_id', $supplier->id)->first();

    expect($row)->not->toBeNull()
        ->and($row->expected_arrival)->toBeNull();
});

it('stores the date when expected_arrival is provided', function () {
    $supplier = Supplier::factory()->create();

    Livewire::test(Form::class)
        ->set('supplier_id', $supplier->id)
        ->set('supplier_name', $supplier->name)
        ->set('order_date', '2026-05-02')
        ->set('expected_arrival', '2026-05-15')
        ->call('save')
        ->assertHasNoErrors();

    $row = DB::table('purchase_rfqs')->where('supplier_id', $supplier->id)->first();

    expect($row->expected_arrival)->toContain('2026-05-15');
});

it('rejects an invalid expected_arrival date', function () {
    $supplier = Supplier::factory()->create();

    Livewire::test(Form::class)
        ->set('supplier_id', $supplier->id)
        ->set('supplier_name', $supplier->name)
        ->set('order_date', '2026-05-02')
        ->set('expected_arrival', 'not-a-date')
        ->call('save')
        ->assertHasErrors(['expected_arrival' => 'date']);
});
