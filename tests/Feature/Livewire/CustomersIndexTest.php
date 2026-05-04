<?php

use App\Livewire\Sales\Customers\Index;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create()->assignRole('super-admin'));
});

describe('Customers Index (WithIndexComponent trait adoption)', function () {
    it('mounts with trait defaults plus custom filter flags', function () {
        Livewire::test(Index::class)
            ->assertSet('search', '')
            ->assertSet('status', '')
            ->assertSet('sort', 'latest')
            ->assertSet('view', 'list')
            ->assertSet('groupBy', '')
            ->assertSet('filterActive', false)
            ->assertSet('filterInactive', false)
            ->assertSet('filterWithOrders', false)
            ->assertSet('selected', []);
    });

    it('renders with customers', function () {
        Customer::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('customers', fn ($c) => $c->total() === 3);
    });

    it('filters by search term across name, email, phone', function () {
        Customer::factory()->create(['name' => 'Acme Corp', 'email' => 'info@acme.test']);
        Customer::factory()->create(['name' => 'Widgets Ltd', 'email' => 'hello@widgets.test']);

        Livewire::test(Index::class)
            ->set('search', 'Acme')
            ->assertViewHas('customers', fn ($c) => $c->total() === 1);

        Livewire::test(Index::class)
            ->set('search', 'widgets.test')
            ->assertViewHas('customers', fn ($c) => $c->total() === 1);
    });

    it('filterActive restricts to active customers only', function () {
        Customer::factory()->create(['status' => 'active']);
        Customer::factory()->count(2)->create(['status' => 'inactive']);

        Livewire::test(Index::class)
            ->set('filterActive', true)
            ->assertViewHas('customers', fn ($c) => $c->total() === 1);
    });

    it('filterInactive restricts to inactive customers only', function () {
        Customer::factory()->create(['status' => 'active']);
        Customer::factory()->count(2)->create(['status' => 'inactive']);

        Livewire::test(Index::class)
            ->set('filterInactive', true)
            ->assertViewHas('customers', fn ($c) => $c->total() === 2);
    });

    it('filterWithOrders restricts to customers with at least one sales order', function () {
        $withOrder = Customer::factory()->create();
        SalesOrder::factory()->create(['customer_id' => $withOrder->id]);
        Customer::factory()->count(2)->create();

        Livewire::test(Index::class)
            ->set('filterWithOrders', true)
            ->assertViewHas('customers', fn ($c) => $c->total() === 1);
    });

    it('clearFilters resets all filter flags including custom ones', function () {
        Customer::factory()->create();

        Livewire::test(Index::class)
            ->set('search', 'foo')
            ->set('status', 'active')
            ->set('filterActive', true)
            ->set('filterInactive', true)
            ->set('filterWithOrders', true)
            ->set('groupBy', 'country')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('status', '')
            ->assertSet('filterActive', false)
            ->assertSet('filterInactive', false)
            ->assertSet('filterWithOrders', false)
            ->assertSet('groupBy', '');
    });

    it('applies applySorting via the trait for name_asc and name_desc', function () {
        Customer::factory()->create(['name' => 'Charlie Co']);
        Customer::factory()->create(['name' => 'Alpha Co']);
        Customer::factory()->create(['name' => 'Bravo Co']);

        $ascNames = Livewire::test(Index::class)
            ->set('sort', 'name_asc')
            ->viewData('customers')->pluck('name')->all();

        expect($ascNames)->toBe(['Alpha Co', 'Bravo Co', 'Charlie Co']);

        $descNames = Livewire::test(Index::class)
            ->set('sort', 'name_desc')
            ->viewData('customers')->pluck('name')->all();

        expect($descNames)->toBe(['Charlie Co', 'Bravo Co', 'Alpha Co']);
    });

    it('confirmBulkDelete splits customers by active-order count', function () {
        $clean = Customer::factory()->create(['name' => 'Clean Customer']);
        $busy = Customer::factory()->create(['name' => 'Busy Customer']);
        SalesOrder::factory()->confirmed()->count(2)->create(['customer_id' => $busy->id]);
        // Cancelled and delivered orders do NOT count as active
        SalesOrder::factory()->cancelled()->create(['customer_id' => $clean->id]);
        SalesOrder::factory()->delivered()->create(['customer_id' => $clean->id]);

        $component = Livewire::test(Index::class)
            ->set('selected', [(string) $clean->id, (string) $busy->id])
            ->call('confirmBulkDelete')
            ->assertSet('showDeleteConfirm', true);

        $validation = $component->get('deleteValidation');

        expect($validation['canDelete'])->toHaveCount(1);
        expect($validation['canDelete'][0]['name'])->toBe('Clean Customer');
        expect($validation['cannotDelete'])->toHaveCount(1);
        expect($validation['cannotDelete'][0]['reason'])->toBe('Has 2 active orders');
    });

    it('bulkDelete removes only customers without active orders', function () {
        $clean = Customer::factory()->create();
        $busy = Customer::factory()->create();
        SalesOrder::factory()->confirmed()->create(['customer_id' => $busy->id]);

        Livewire::test(Index::class)
            ->set('selected', [(string) $clean->id, (string) $busy->id])
            ->call('bulkDelete');

        expect(Customer::find($clean->id))->toBeNull();
        expect(Customer::find($busy->id))->not->toBeNull();
    });

    it('bulkDelete flashes error when every selected customer has active orders', function () {
        $a = Customer::factory()->create();
        $b = Customer::factory()->create();
        SalesOrder::factory()->confirmed()->create(['customer_id' => $a->id]);
        SalesOrder::factory()->processing()->create(['customer_id' => $b->id]);

        Livewire::test(Index::class)
            ->set('selected', [(string) $a->id, (string) $b->id])
            ->call('bulkDelete');

        expect(Customer::count())->toBe(2);
    });

    it('bulkActivate and bulkDeactivate flip status and clear selection', function () {
        $customers = Customer::factory()->count(2)->create(['status' => 'inactive']);
        $ids = $customers->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        Livewire::test(Index::class)
            ->set('selected', $ids)
            ->call('bulkActivate')
            ->assertSet('selected', []);

        expect(Customer::where('status', 'active')->count())->toBe(2);

        Livewire::test(Index::class)
            ->set('selected', $ids)
            ->call('bulkDeactivate');

        expect(Customer::where('status', 'inactive')->count())->toBe(2);
    });

    it('resets page and clears selection when search changes (trait hook)', function () {
        $c = Customer::factory()->create();

        Livewire::test(Index::class)
            ->set('page', 3)
            ->set('selected', [(string) $c->id])
            ->set('search', 'hello')
            ->assertSet('page', 1)
            ->assertSet('selected', []);
    });
});
