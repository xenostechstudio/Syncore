<?php

use App\Livewire\Purchase\Suppliers\Index;
use App\Models\Purchase\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create()->assignRole('super-admin'));
});

function attachPurchaseRfqForSupplier(int $supplierId, string $status = 'draft'): int
{
    return DB::table('purchase_rfqs')->insertGetId([
        'reference' => 'RFQ-' . uniqid(),
        'supplier_id' => $supplierId,
        'status' => $status,
        'order_date' => now()->toDateString(),
        'subtotal' => 0,
        'tax' => 0,
        'total' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

describe('Suppliers Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('status', 'all')
            ->assertSet('perPage', 10)
            ->assertSet('viewType', 'list');
    });

    it('renders list', function () {
        Supplier::factory()->count(4)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('total', 4);
    });

    it('filters by search on name/email/contact_person', function () {
        Supplier::factory()->create([
            'name' => 'Zzz-Acme',
            'email' => 'zzz-acme@x.test',
            'contact_person' => 'Zzz-Contact1',
        ]);
        Supplier::factory()->create([
            'name' => 'Zzz-Widgets',
            'email' => 'zzz-widgets@x.test',
            'contact_person' => 'Zzz-MatchMe',
        ]);

        Livewire::test(Index::class)
            ->set('search', 'Zzz-Acme')
            ->assertViewHas('total', 1);

        Livewire::test(Index::class)
            ->set('search', 'Zzz-MatchMe')
            ->assertViewHas('total', 1);
    });

    it('filters by active status', function () {
        Supplier::factory()->count(2)->create();
        Supplier::factory()->count(3)->inactive()->create();

        Livewire::test(Index::class)
            ->set('status', 'active')
            ->assertViewHas('total', 2);

        Livewire::test(Index::class)
            ->set('status', 'inactive')
            ->assertViewHas('total', 3);
    });

    it('confirmBulkDelete splits suppliers by active PO count', function () {
        $clean = Supplier::factory()->create(['name' => 'Clean']);
        $busy = Supplier::factory()->create(['name' => 'Busy']);
        attachPurchaseRfqForSupplier($busy->id, 'draft');
        attachPurchaseRfqForSupplier($clean->id, 'received');

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $clean->id, (string) $busy->id])
            ->call('confirmBulkDelete')
            ->assertSet('showDeleteConfirm', true);

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect($v['canDelete'][0]['name'])->toBe('Clean');
        expect($v['cannotDelete'])->toHaveCount(1);
        expect($v['cannotDelete'][0]['reason'])->toBe('Has 1 active purchase orders');
    });

    it('bulkActivate and bulkDeactivate flip is_active', function () {
        $s = Supplier::factory()->count(2)->inactive()->create();
        $ids = $s->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        Livewire::test(Index::class)
            ->set('selected', $ids)
            ->call('bulkActivate');
        expect(Supplier::where('is_active', true)->count())->toBe(2);

        Livewire::test(Index::class)
            ->set('selected', $ids)
            ->call('bulkDeactivate');
        expect(Supplier::where('is_active', false)->count())->toBe(2);
    });

    it('clearFilters resets search/sort and sets status=all', function () {
        Livewire::test(Index::class)
            ->set('search', 'abc')
            ->set('status', 'active')
            ->set('sort', 'name')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('status', 'all')
            ->assertSet('sort', 'latest');
    });
});
