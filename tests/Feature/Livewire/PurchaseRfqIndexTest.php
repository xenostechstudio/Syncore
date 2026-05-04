<?php

use App\Livewire\Purchase\Rfq\Index;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create()->assignRole('super-admin'));
});

function makeRfqForRfqIndex(string $status, ?string $reference = null): int
{
    return DB::table('purchase_rfqs')->insertGetId([
        'reference' => $reference ?? 'RFQ-' . uniqid(),
        'order_date' => now()->toDateString(),
        'status' => $status,
        'subtotal' => 0,
        'tax' => 0,
        'total' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

describe('Purchase RFQ Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('status', 'all')
            ->assertSet('perPage', 10);
    });

    it('renders all statuses by default', function () {
        makeRfqForRfqIndex('draft');
        makeRfqForRfqIndex('rfq');
        makeRfqForRfqIndex('purchase_order');
        makeRfqForRfqIndex('cancelled');

        Livewire::test(Index::class)->assertViewHas('total', 4);
    });

    it('filters by status when not all', function () {
        makeRfqForRfqIndex('draft');
        makeRfqForRfqIndex('rfq');
        makeRfqForRfqIndex('rfq');

        Livewire::test(Index::class)
            ->set('status', 'rfq')
            ->assertViewHas('total', 2);
    });

    it('confirmBulkDelete allows only draft/rfq', function () {
        $draft = makeRfqForRfqIndex('draft');
        $cancelled = makeRfqForRfqIndex('cancelled');

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $draft, (string) $cancelled])
            ->call('confirmBulkDelete');

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect($v['cannotDelete'])->toHaveCount(1);
    });

    it('bulkConfirm flips rfq → purchase_order', function () {
        $rfq = makeRfqForRfqIndex('rfq');

        Livewire::test(Index::class)
            ->set('selected', [(string) $rfq])
            ->call('bulkConfirm');

        expect(DB::table('purchase_rfqs')->where('id', $rfq)->value('status'))->toBe('purchase_order');
    });

    it('bulkCancel excludes cancelled/received', function () {
        $rfq = makeRfqForRfqIndex('rfq');
        $cancelled = makeRfqForRfqIndex('cancelled');
        $received = makeRfqForRfqIndex('received');

        Livewire::test(Index::class)
            ->set('selected', [(string) $rfq, (string) $cancelled, (string) $received])
            ->call('bulkCancel');

        expect(DB::table('purchase_rfqs')->where('id', $rfq)->value('status'))->toBe('cancelled');
        expect(DB::table('purchase_rfqs')->where('id', $received)->value('status'))->toBe('received');
    });
});
