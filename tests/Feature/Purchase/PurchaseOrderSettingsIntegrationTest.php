<?php

/**
 * Wires PurchaseOrderSetting to its consuming code paths — the trait
 * override on PurchaseRfq, the Rfq Form's reference generation (which
 * was previously rand-based and unsynchronized with the Eloquent path),
 * the lead-time pre-fill, and the Settings/Modules/PurchaseOrder UI.
 */

use App\Livewire\Purchase\Rfq\Form;
use App\Livewire\Settings\Modules\PurchaseOrder as PurchaseOrderSettingsForm;
use App\Models\Inventory\Warehouse;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\Supplier;
use App\Models\Settings\PurchaseOrderSetting;
use Livewire\Livewire;

describe('PurchaseRfq trait reads from settings', function () {
    it('issues new Eloquent-created RFQs in the default RFQ-NNNNN format', function () {
        $supplier = Supplier::factory()->create();
        $rfq = PurchaseRfq::create([
            'supplier_id' => $supplier->id,
            'order_date'  => now(),
        ]);

        // Existing seeded data uses random numbers; the trait's max-query
        // will pick a sequential number after the highest existing.
        expect($rfq->reference)->toMatch('/^RFQ-\d{5}$/');
    });

    it('switches the issued format when admin enables yearly reset', function () {
        $settings = PurchaseOrderSetting::instance();
        $settings->update([
            'doc_number_separator'    => '/',
            'doc_number_yearly_reset' => true,
        ]);
        PurchaseOrderSetting::clearCache();

        $supplier = Supplier::factory()->create();
        $rfq = PurchaseRfq::create([
            'supplier_id' => $supplier->id,
            'order_date'  => now(),
        ]);

        $year = now()->year;
        expect($rfq->reference)->toMatch("/^RFQ\\/{$year}\\/\\d{5}$/");
    });
});

describe('Rfq Form reference generation', function () {
    it('uses the settings-driven helper instead of rand() (regression)', function () {
        // Regression: the form generated references via rand(1, 9999),
        // producing random not sequential numbers and disagreeing with
        // the Eloquent path. Both paths should now agree on the format.
        actAsAdmin();

        Livewire::test(Form::class)
            ->tap(function ($component) {
                $reference = $component->get('reference');
                expect($reference)->toMatch('/^RFQ-\d{5}$/');
            });
    });

    it('respects custom prefix + yearly_reset settings on new-form mount', function () {
        $settings = PurchaseOrderSetting::instance();
        $settings->update([
            'doc_number_prefix'       => 'PUR',
            'doc_number_separator'    => '-',
            'doc_number_yearly_reset' => true,
            'doc_number_padding'      => 3,
        ]);
        PurchaseOrderSetting::clearCache();

        actAsAdmin();

        Livewire::test(Form::class)
            ->tap(function ($component) {
                $year = now()->year;
                expect($component->get('reference'))->toBe("PUR-{$year}-001");
            });
    });

    it('pre-fills expected_arrival from default_lead_time_days', function () {
        PurchaseOrderSetting::instance()->update(['default_lead_time_days' => 14]);
        PurchaseOrderSetting::clearCache();

        actAsAdmin();

        Livewire::test(Form::class)
            ->assertSet('expected_arrival', now()->addDays(14)->format('Y-m-d'));
    });

    it('does not overwrite expected_arrival when editing an existing RFQ', function () {
        $supplier = Supplier::factory()->create();
        $rfq = PurchaseRfq::create([
            'supplier_id'      => $supplier->id,
            'order_date'       => now(),
            'expected_arrival' => '2026-12-31',
        ]);

        PurchaseOrderSetting::instance()->update(['default_lead_time_days' => 99]);
        PurchaseOrderSetting::clearCache();

        actAsAdmin();

        // DB casts to timestamp; what matters is that we got the existing
        // value back, not the lead-time-driven default.
        Livewire::test(Form::class, ['id' => $rfq->id])
            ->tap(function ($component) {
                expect($component->get('expected_arrival'))->toContain('2026-12-31');
                expect($component->get('expected_arrival'))->not->toBe(
                    now()->addDays(99)->format('Y-m-d')
                );
            });
    });
});

describe('Settings/Modules/PurchaseOrder Livewire form', function () {
    it('persists changes and busts the per-process cache', function () {
        actAsAdmin();
        $warehouse = Warehouse::factory()->create();

        Livewire::test(PurchaseOrderSettingsForm::class)
            ->set('doc_number_prefix', 'PUR')
            ->set('doc_number_separator', '-')
            ->set('doc_number_yearly_reset', true)
            ->set('default_warehouse_id', $warehouse->id)
            ->set('default_lead_time_days', 21)
            ->set('approval_threshold', 5_000_000)
            ->set('auto_send_to_supplier', true)
            ->call('save')
            ->assertHasNoErrors();

        $reloaded = PurchaseOrderSetting::instance();
        expect($reloaded->doc_number_prefix)->toBe('PUR');
        expect($reloaded->doc_number_yearly_reset)->toBeTrue();
        expect($reloaded->default_warehouse_id)->toBe($warehouse->id);
        expect($reloaded->default_lead_time_days)->toBe(21);
        expect((float) $reloaded->approval_threshold)->toBe(5_000_000.0);
        expect($reloaded->auto_send_to_supplier)->toBeTrue();
    });

    it('validates required fields and ranges', function () {
        actAsAdmin();

        Livewire::test(PurchaseOrderSettingsForm::class)
            ->set('doc_number_prefix', '')
            ->set('default_lead_time_days', -1)
            ->set('approval_threshold', -100)
            ->call('save')
            ->assertHasErrors([
                'doc_number_prefix',
                'default_lead_time_days',
                'approval_threshold',
            ]);
    });

    it('exposes a live numberPreview that mirrors what the trait will issue', function () {
        actAsAdmin();

        Livewire::test(PurchaseOrderSettingsForm::class)
            ->set('doc_number_prefix', 'PUR')
            ->set('doc_number_separator', '-')
            ->set('doc_number_padding', 3)
            ->set('doc_number_yearly_reset', true)
            ->tap(function ($component) {
                $year = now()->year;
                expect($component->instance()->numberPreview)->toBe("PUR-{$year}-001");
            });
    });
});
