<?php

use App\Livewire\Purchase\Orders\Index;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

function makeRfq(string $status, ?string $reference = null, ?string $supplierName = null): int
{
    return DB::table('purchase_rfqs')->insertGetId([
        'reference' => $reference ?? 'REF-' . uniqid(),
        'supplier_name' => $supplierName,
        'order_date' => now()->toDateString(),
        'status' => $status,
        'subtotal' => 0,
        'tax' => 0,
        'total' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

describe('Purchase Orders Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('status', 'all')
            ->assertSet('perPage', 10)
            ->assertSet('viewType', 'list');
    });

    it('only shows status=purchase_order rows', function () {
        makeRfq('purchase_order');
        makeRfq('purchase_order');
        makeRfq('rfq');
        makeRfq('draft');

        Livewire::test(Index::class)
            ->assertViewHas('total', 2);
    });

    it('filters by search on reference or supplier_name', function () {
        makeRfq('purchase_order', 'PO-111', 'Zzz-AcmeA');
        makeRfq('purchase_order', 'PO-222', 'Zzz-AcmeB');

        Livewire::test(Index::class)
            ->set('search', 'PO-111')
            ->assertViewHas('total', 1);

        Livewire::test(Index::class)
            ->set('search', 'Zzz-AcmeB')
            ->assertViewHas('total', 1);
    });

    it('confirmBulkDelete allows only draft/rfq', function () {
        $po = makeRfq('purchase_order');
        $rfq = makeRfq('rfq');
        $draft = makeRfq('draft');

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $po, (string) $rfq, (string) $draft])
            ->call('confirmBulkDelete');

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(2);
        expect($v['cannotDelete'])->toHaveCount(1);
    });

    it('bulkConfirm promotes rfq → purchase_order', function () {
        $rfq1 = makeRfq('rfq');
        $rfq2 = makeRfq('rfq');
        $po = makeRfq('purchase_order');

        Livewire::test(Index::class)
            ->set('selected', [(string) $rfq1, (string) $rfq2, (string) $po])
            ->call('bulkConfirm');

        expect(DB::table('purchase_rfqs')->where('status', 'purchase_order')->count())->toBe(3);
    });

    it('clearFilters resets status to all', function () {
        Livewire::test(Index::class)
            ->set('status', 'something')
            ->set('search', 'x')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('status', 'all');
    });
});
