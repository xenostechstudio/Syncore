<?php

use App\Livewire\Purchase\Rfq\Form;
use App\Models\Inventory\Product;
use App\Models\Purchase\Supplier;
use App\Models\User;
use Database\Seeders\ModulePermissionSeeder;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(ModulePermissionSeeder::class);
    $this->user = User::factory()->create()->assignRole('super-admin');
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

it('persists line items entered on the form (regression: lines were silently dropped)', function () {
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create();

    Livewire::test(Form::class)
        ->set('supplier_id', $supplier->id)
        ->set('supplier_name', $supplier->name)
        ->set('order_date', '2026-05-02')
        ->set('lines', [
            [
                'id' => null,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'description' => 'Bulk reorder',
                'quantity' => 5,
                'unit_price' => 1000,
                'discount' => 0,
                'total' => 5000,
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $rfqId = DB::table('purchase_rfqs')->where('supplier_id', $supplier->id)->value('id');

    $lines = DB::table('purchase_rfq_items')
        ->where('purchase_rfq_id', $rfqId)
        ->whereNull('deleted_at')
        ->get();

    expect($lines)->toHaveCount(1);
    expect((int) $lines[0]->product_id)->toBe($product->id);
    expect((float) $lines[0]->quantity)->toBe(5.0);
    expect((float) $lines[0]->unit_price)->toBe(1000.0);
    expect((float) $lines[0]->subtotal)->toBe(5000.0);
    expect($lines[0]->description)->toBe('Bulk reorder');
});

it('skips empty lines (no product selected) on save', function () {
    $supplier = Supplier::factory()->create();

    Livewire::test(Form::class)
        ->set('supplier_id', $supplier->id)
        ->set('supplier_name', $supplier->name)
        ->set('order_date', '2026-05-02')
        ->call('save')
        ->assertHasNoErrors();

    $rfqId = DB::table('purchase_rfqs')->where('supplier_id', $supplier->id)->value('id');

    expect(DB::table('purchase_rfq_items')->where('purchase_rfq_id', $rfqId)->count())->toBe(0);
});

it('reloads existing lines when editing an RFQ', function () {
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create();

    Livewire::test(Form::class)
        ->set('supplier_id', $supplier->id)
        ->set('supplier_name', $supplier->name)
        ->set('order_date', '2026-05-02')
        ->set('lines', [[
            'id' => null,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'description' => '',
            'quantity' => 3,
            'unit_price' => 500,
            'discount' => 0,
            'total' => 1500,
        ]])
        ->call('save');

    $rfqId = DB::table('purchase_rfqs')->where('supplier_id', $supplier->id)->value('id');

    $component = Livewire::test(Form::class, ['id' => $rfqId])->assertStatus(200);

    expect($component->get('lines'))->toHaveCount(1);
    expect((int) $component->get('lines')[0]['product_id'])->toBe($product->id);
    expect((float) $component->get('lines')[0]['quantity'])->toBe(3.0);
});

it('soft-deletes a line that the user removed from the form', function () {
    $supplier = Supplier::factory()->create();
    $productA = Product::factory()->create();
    $productB = Product::factory()->create();

    // Create with two lines
    Livewire::test(Form::class)
        ->set('supplier_id', $supplier->id)
        ->set('supplier_name', $supplier->name)
        ->set('order_date', '2026-05-02')
        ->set('lines', [
            ['id' => null, 'product_id' => $productA->id, 'product_name' => $productA->name, 'product_sku' => $productA->sku, 'description' => '', 'quantity' => 2, 'unit_price' => 100, 'discount' => 0, 'total' => 200],
            ['id' => null, 'product_id' => $productB->id, 'product_name' => $productB->name, 'product_sku' => $productB->sku, 'description' => '', 'quantity' => 4, 'unit_price' => 50, 'discount' => 0, 'total' => 200],
        ])
        ->call('save');

    $rfqId = DB::table('purchase_rfqs')->where('supplier_id', $supplier->id)->value('id');
    expect(DB::table('purchase_rfq_items')->where('purchase_rfq_id', $rfqId)->whereNull('deleted_at')->count())->toBe(2);

    // Reload, drop the second line, save again
    $component = Livewire::test(Form::class, ['id' => $rfqId]);
    $lines = $component->get('lines');
    $component
        ->set('lines', [$lines[0]])
        ->call('save');

    expect(DB::table('purchase_rfq_items')->where('purchase_rfq_id', $rfqId)->whereNull('deleted_at')->count())->toBe(1);
    expect(DB::table('purchase_rfq_items')->where('purchase_rfq_id', $rfqId)->whereNotNull('deleted_at')->count())->toBe(1);
});
