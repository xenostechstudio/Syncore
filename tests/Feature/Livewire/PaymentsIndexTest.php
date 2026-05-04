<?php

use App\Livewire\Invoicing\Payments\Index;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create()->assignRole('super-admin'));
});

function makeInvoiceForPayment(): int
{
    $customerId = DB::table('customers')->insertGetId([
        'name' => 'Test Customer',
        'email' => 'cust-' . uniqid() . '@test',
        'type' => 'company',
        'country' => 'Indonesia',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return DB::table('invoices')->insertGetId([
        'invoice_number' => 'INV-' . uniqid(),
        'customer_id' => $customerId,
        'invoice_date' => now()->toDateString(),
        'subtotal' => 1000,
        'tax' => 100,
        'discount' => 0,
        'total' => 1100,
        'paid_amount' => 0,
        'status' => 'draft',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

describe('Payments Index', function () {
    it('mounts with showStats defaulting to true', function () {
        Livewire::test(Index::class)
            ->assertSet('showStats', true);
    });

    it('renders list of payments', function () {
        $iid = makeInvoiceForPayment();
        Payment::factory()->count(3)->create(['invoice_id' => $iid]);

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('payments', fn ($p) => $p->total() === 3);
    });

    it('filters by search on reference', function () {
        $iid = makeInvoiceForPayment();
        Payment::factory()->create(['invoice_id' => $iid, 'reference' => 'ABC-123']);
        Payment::factory()->create(['invoice_id' => $iid, 'reference' => 'XYZ-999']);

        Livewire::test(Index::class)
            ->set('search', 'ABC')
            ->assertViewHas('payments', fn ($p) => $p->total() === 1);
    });

    it('exposes statistics when showStats is true', function () {
        $iid = makeInvoiceForPayment();
        Payment::factory()->count(2)->create([
            'invoice_id' => $iid,
            'amount' => 5000,
            'payment_method' => 'bank_transfer',
        ]);

        $c = Livewire::test(Index::class);
        $stats = $c->viewData('statistics');
        expect($stats['total_count'])->toBe(2);
        expect((int) $stats['total_amount'])->toBe(10000);
    });

    it('toggleStats flips showStats', function () {
        Livewire::test(Index::class)
            ->assertSet('showStats', true)
            ->call('toggleStats')
            ->assertSet('showStats', false);
    });
});
