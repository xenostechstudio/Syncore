<?php

use App\Livewire\Purchase\Bills\Index;
use App\Models\Purchase\Supplier;
use App\Models\Purchase\VendorBill;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

describe('VendorBills Index', function () {
    it('mounts and renders', function () {
        VendorBill::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('bills', fn ($p) => $p->total() === 3);
    });

    it('filters by search on bill_number, vendor_reference, or supplier name', function () {
        $s1 = Supplier::factory()->create(['name' => 'Zzz-AcmeSupply']);
        VendorBill::factory()->create(['supplier_id' => $s1->id, 'bill_number' => 'BILL-111', 'vendor_reference' => 'VR-AAA']);
        VendorBill::factory()->create(['bill_number' => 'BILL-222', 'vendor_reference' => 'VR-BBB']);

        Livewire::test(Index::class)
            ->set('search', 'BILL-111')
            ->assertViewHas('bills', fn ($p) => $p->total() === 1);

        Livewire::test(Index::class)
            ->set('search', 'VR-BBB')
            ->assertViewHas('bills', fn ($p) => $p->total() === 1);

        Livewire::test(Index::class)
            ->set('search', 'Zzz-AcmeSupply')
            ->assertViewHas('bills', fn ($p) => $p->total() === 1);
    });

    it('filters by status', function () {
        VendorBill::factory()->create(['status' => 'draft']);
        VendorBill::factory()->paid()->count(2)->create();

        Livewire::test(Index::class)
            ->set('status', 'paid')
            ->assertViewHas('bills', fn ($p) => $p->total() === 2);
    });

    it('confirmBulkDelete allows only draft', function () {
        $draft = VendorBill::factory()->create(['status' => 'draft']);
        $paid = VendorBill::factory()->paid()->create();

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $draft->id, (string) $paid->id])
            ->call('confirmBulkDelete');

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect($v['cannotDelete'])->toHaveCount(1);
    });

    it('bulkDelete removes only drafts', function () {
        $draft = VendorBill::factory()->create(['status' => 'draft']);
        $paid = VendorBill::factory()->paid()->create();

        Livewire::test(Index::class)
            ->set('selected', [(string) $draft->id, (string) $paid->id])
            ->call('bulkDelete');

        expect(VendorBill::find($draft->id))->toBeNull();
        expect(VendorBill::find($paid->id))->not->toBeNull();
    });

    it('bulkUpdateStatus updates selected bills', function () {
        $bills = VendorBill::factory()->count(2)->create();

        Livewire::test(Index::class)
            ->set('selected', $bills->pluck('id')->map(fn ($id) => (string) $id)->toArray())
            ->call('bulkUpdateStatus', 'pending');

        expect(VendorBill::where('status', 'pending')->count())->toBe(2);
    });
});
