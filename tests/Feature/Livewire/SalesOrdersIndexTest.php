<?php

use App\Livewire\Sales\Orders\Index;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->me = User::factory()->create();
    $this->actingAs($this->me);
});

describe('Sales Orders Index', function () {
    it('defaults to quotations mode with myQuotations=true', function () {
        Livewire::test(Index::class)
            ->assertSet('mode', 'quotations')
            ->assertSet('myQuotations', true);
    });

    it('filters to my own quotations by default', function () {
        $other = User::factory()->create();
        SalesOrder::factory()->count(2)->create(['user_id' => $this->me->id, 'status' => 'draft']);
        SalesOrder::factory()->count(3)->create(['user_id' => $other->id, 'status' => 'draft']);

        Livewire::test(Index::class)
            ->assertViewHas('orders', fn ($p) => $p->total() === 2);

        Livewire::test(Index::class)
            ->set('myQuotations', false)
            ->assertViewHas('orders', fn ($p) => $p->total() === 5);
    });

    it('filters by search on order_number and customer name', function () {
        $c = Customer::factory()->create(['name' => 'Zzz-Acme']);
        SalesOrder::factory()->create(['user_id' => $this->me->id, 'customer_id' => $c->id, 'order_number' => 'SO-1001']);
        SalesOrder::factory()->create(['user_id' => $this->me->id, 'order_number' => 'SO-2002']);

        Livewire::test(Index::class)
            ->set('search', 'SO-1001')
            ->assertViewHas('orders', fn ($p) => $p->total() === 1);

        Livewire::test(Index::class)
            ->set('search', 'Zzz-Acme')
            ->assertViewHas('orders', fn ($p) => $p->total() === 1);
    });

    it('filters by status', function () {
        SalesOrder::factory()->draft()->count(2)->create(['user_id' => $this->me->id]);
        SalesOrder::factory()->confirmed()->count(3)->create(['user_id' => $this->me->id]);

        Livewire::test(Index::class)
            ->set('status', 'confirmed')
            ->assertViewHas('orders', fn ($p) => $p->total() === 3);
    });

    it('confirmBulkDelete allows only draft/quotation/confirmed', function () {
        $draft = SalesOrder::factory()->draft()->create(['user_id' => $this->me->id]);
        $processing = SalesOrder::factory()->processing()->create(['user_id' => $this->me->id]);

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $draft->id, (string) $processing->id])
            ->call('confirmBulkDelete');

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect($v['cannotDelete'])->toHaveCount(1);
    });

    it('bulkDelete removes only allowed statuses', function () {
        $draft = SalesOrder::factory()->draft()->create(['user_id' => $this->me->id]);
        $processing = SalesOrder::factory()->processing()->create(['user_id' => $this->me->id]);

        Livewire::test(Index::class)
            ->set('selected', [(string) $draft->id, (string) $processing->id])
            ->call('bulkDelete');

        expect(SalesOrder::find($draft->id))->toBeNull();
        expect(SalesOrder::find($processing->id))->not->toBeNull();
    });

    it('bulkConfirm flips draft/quotation to confirmed', function () {
        $draft = SalesOrder::factory()->draft()->create(['user_id' => $this->me->id]);
        $delivered = SalesOrder::factory()->delivered()->create(['user_id' => $this->me->id]);

        Livewire::test(Index::class)
            ->set('selected', [(string) $draft->id, (string) $delivered->id])
            ->call('bulkConfirm');

        expect($draft->fresh()->status)->toBe('confirmed');
        expect($delivered->fresh()->status)->toBe('delivered');
    });

    it('bulkCancel flips non-terminal statuses to cancelled', function () {
        $draft = SalesOrder::factory()->draft()->create(['user_id' => $this->me->id]);
        $delivered = SalesOrder::factory()->delivered()->create(['user_id' => $this->me->id]);
        $cancelled = SalesOrder::factory()->cancelled()->create(['user_id' => $this->me->id]);

        Livewire::test(Index::class)
            ->set('selected', [(string) $draft->id, (string) $delivered->id, (string) $cancelled->id])
            ->call('bulkCancel');

        expect($draft->fresh()->status)->toBe('cancelled');
        expect($delivered->fresh()->status)->toBe('delivered');
    });
});
