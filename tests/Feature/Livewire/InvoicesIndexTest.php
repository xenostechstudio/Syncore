<?php

use App\Livewire\Invoicing\Invoices\Index;
use App\Models\Invoicing\Invoice;
use App\Models\Sales\Customer;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->me = User::factory()->create()->assignRole('super-admin');
    $this->actingAs($this->me);
});

describe('Invoices Index', function () {
    it('mounts with myInvoice=true by default', function () {
        Livewire::test(Index::class)
            ->assertSet('myInvoice', true)
            ->assertSet('search', '')
            ->assertSet('status', '');
    });

    it('filters to current user when myInvoice=true', function () {
        $other = User::factory()->create();
        Invoice::factory()->count(2)->create(['user_id' => $this->me->id]);
        Invoice::factory()->count(3)->create(['user_id' => $other->id]);

        Livewire::test(Index::class)
            ->assertViewHas('invoices', fn ($p) => $p->total() === 2);

        Livewire::test(Index::class)
            ->set('myInvoice', false)
            ->assertViewHas('invoices', fn ($p) => $p->total() === 5);
    });

    it('filters by search on invoice_number and customer name', function () {
        $c1 = Customer::factory()->create(['name' => 'Acme Corp']);
        Invoice::factory()->create(['user_id' => $this->me->id, 'customer_id' => $c1->id, 'invoice_number' => 'INV-1001']);
        Invoice::factory()->create(['user_id' => $this->me->id, 'invoice_number' => 'INV-2002']);

        Livewire::test(Index::class)
            ->set('search', 'Acme')
            ->assertViewHas('invoices', fn ($p) => $p->total() === 1);

        Livewire::test(Index::class)
            ->set('search', '1001')
            ->assertViewHas('invoices', fn ($p) => $p->total() === 1);
    });

    it('filters by status', function () {
        Invoice::factory()->draft()->count(2)->create(['user_id' => $this->me->id]);
        Invoice::factory()->paid()->count(3)->create(['user_id' => $this->me->id]);

        Livewire::test(Index::class)
            ->set('status', 'draft')
            ->assertViewHas('invoices', fn ($p) => $p->total() === 2);
    });

    it('confirmBulkDelete allows only draft invoices', function () {
        $draft = Invoice::factory()->draft()->create(['user_id' => $this->me->id]);
        $sent = Invoice::factory()->sent()->create(['user_id' => $this->me->id]);

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $draft->id, (string) $sent->id])
            ->call('confirmBulkDelete');

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect((int) $v['canDelete'][0]['id'])->toBe($draft->id);
        expect($v['cannotDelete'])->toHaveCount(1);
    });

    it('bulkDelete removes only drafts', function () {
        $draft = Invoice::factory()->draft()->create(['user_id' => $this->me->id]);
        $sent = Invoice::factory()->sent()->create(['user_id' => $this->me->id]);

        Livewire::test(Index::class)
            ->set('selected', [(string) $draft->id, (string) $sent->id])
            ->call('bulkDelete');

        expect(Invoice::find($draft->id))->toBeNull();
        expect(Invoice::find($sent->id))->not->toBeNull();
    });

    it('bulkMarkSent flips draft invoices to sent', function () {
        $draft = Invoice::factory()->draft()->create(['user_id' => $this->me->id]);
        $paid = Invoice::factory()->paid()->create(['user_id' => $this->me->id]);

        Livewire::test(Index::class)
            ->set('selected', [(string) $draft->id, (string) $paid->id])
            ->call('bulkMarkSent');

        expect($draft->fresh()->status)->toBe('sent');
        expect($paid->fresh()->status)->toBe('paid');
    });

    it('bulkMarkPaid flips sent/partial/overdue to paid', function () {
        $sent = Invoice::factory()->sent()->create(['user_id' => $this->me->id]);
        $overdue = Invoice::factory()->overdue()->create(['user_id' => $this->me->id]);
        $draft = Invoice::factory()->draft()->create(['user_id' => $this->me->id]);

        Livewire::test(Index::class)
            ->set('selected', [(string) $sent->id, (string) $overdue->id, (string) $draft->id])
            ->call('bulkMarkPaid');

        expect($sent->fresh()->status)->toBe('paid');
        expect($overdue->fresh()->status)->toBe('paid');
        expect($draft->fresh()->status)->toBe('draft');
    });

    it('statistics aggregates by status when showStats is true', function () {
        Invoice::factory()->draft()->count(2)->create(['user_id' => $this->me->id]);
        Invoice::factory()->paid()->count(3)->create(['user_id' => $this->me->id]);

        $c = Livewire::test(Index::class)->set('showStats', true);
        $s = $c->viewData('statistics');
        expect($s['draft'])->toBe(2);
        expect($s['paid'])->toBe(3);
    });
});
