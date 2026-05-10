<?php

/**
 * Wires SalesOrderSetting to the consuming code paths — the doc-number
 * trait, the PDF template, and the new-order form pre-fill. Each piece is
 * tested independently, then via the live-edit settings flow to make sure
 * the Livewire save action busts the cache and the next operation picks
 * up the new values.
 */

use App\Livewire\Sales\Orders\Form;
use App\Livewire\Settings\Modules\SalesOrder as SalesOrderSettingsForm;
use App\Models\Inventory\Product;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\Settings\SalesOrderSetting;
use Livewire\Livewire;

describe('SalesOrder document numbering integration', function () {
    it('issues new orders in the default "SO00001" format (matches production today)', function () {
        $customer = Customer::factory()->create();
        $order = SalesOrder::factory()->create(['customer_id' => $customer->id]);

        // RefreshDatabase wipes the row each test so seq starts at 1.
        expect($order->order_number)->toMatch('/^SO\d{5}$/');
    });

    it('switches the issued format when admin enables yearly reset + sets a separator', function () {
        $settings = SalesOrderSetting::instance();
        $settings->update([
            'doc_number_separator'    => '/',
            'doc_number_yearly_reset' => true,
        ]);
        SalesOrderSetting::clearCache();

        $customer = Customer::factory()->create();
        $order = SalesOrder::factory()->create(['customer_id' => $customer->id]);

        $year = now()->year;
        expect($order->order_number)->toBe("SO/{$year}/00001");
    });

    it('honors a custom prefix and padding', function () {
        $settings = SalesOrderSetting::instance();
        $settings->update([
            'doc_number_prefix'       => 'QUO',
            'doc_number_separator'    => '-',
            'doc_number_padding'      => 3,
            'doc_number_yearly_reset' => true,
        ]);
        SalesOrderSetting::clearCache();

        $customer = Customer::factory()->create();
        $order = SalesOrder::factory()->create(['customer_id' => $customer->id]);

        $year = now()->year;
        expect($order->order_number)->toBe("QUO-{$year}-001");
    });
});

describe('SalesOrder PDF quotation validity', function () {
    it('reads the configured validity period from settings', function () {
        SalesOrderSetting::instance()->update(['quotation_validity_days' => 14]);
        SalesOrderSetting::clearCache();

        $customer = Customer::factory()->create();
        $product  = Product::factory()->create();
        $order = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'status'      => 'draft',
        ]);
        SalesOrderItem::create([
            'sales_order_id'     => $order->id,
            'product_id'         => $product->id,
            'quantity'           => 1,
            'quantity_invoiced'  => 0,
            'quantity_delivered' => 0,
            'unit_price'         => 100_000,
            'discount'           => 0,
            'total'              => 100_000,
        ]);

        $bytes = \App\Services\PdfService::renderSalesOrder($order->fresh(['customer', 'items.product']));

        // Render the underlying view directly so we can assert against text
        // rather than a binary PDF blob.
        $html = view('pdf.sales-order', [
            'order'    => $order->fresh(['customer', 'items.product']),
            'company'  => ['name' => 'Test', 'address' => '', 'phone' => '', 'email' => '', 'website' => '', 'tax_id' => '', 'logo' => null],
            'settings' => \App\Models\Settings\InvoiceSetting::instance(),
        ])->render();

        expect($html)->toContain('14 days');
        expect($html)->not->toContain('30 days');
    });
});

describe('SalesOrder form pre-fill from settings', function () {
    it('pre-fills default_terms and default_notes on a new-order mount', function () {
        SalesOrderSetting::instance()->update([
            'default_terms' => 'Standard terms apply.',
            'default_notes' => 'Internal: confirm with rep.',
        ]);
        SalesOrderSetting::clearCache();

        actAsAdmin();

        Livewire::test(Form::class)
            ->assertSet('terms', 'Standard terms apply.')
            ->assertSet('notes', 'Internal: confirm with rep.');
    });

    it('does not overwrite an existing order\'s terms when editing', function () {
        // Settings have a default; existing order has its own value.
        SalesOrderSetting::instance()->update(['default_terms' => 'Settings default']);
        SalesOrderSetting::clearCache();

        $customer = Customer::factory()->create();
        $order = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'terms'       => 'This order\'s own terms',
        ]);

        actAsAdmin();

        Livewire::test(Form::class, ['id' => $order->id])
            ->assertSet('terms', 'This order\'s own terms');
    });
});

describe('Settings/Modules/SalesOrder Livewire form', function () {
    it('persists changes and busts the per-process cache', function () {
        actAsAdmin();

        Livewire::test(SalesOrderSettingsForm::class)
            ->set('doc_number_prefix', 'QUO')
            ->set('doc_number_separator', '-')
            ->set('doc_number_yearly_reset', true)
            ->set('quotation_validity_days', 7)
            ->set('stock_check_mode', 'block')
            ->call('save')
            ->assertHasNoErrors();

        // Cache is busted by save(); fresh instance() returns the new values.
        $reloaded = SalesOrderSetting::instance();
        expect($reloaded->doc_number_prefix)->toBe('QUO');
        expect($reloaded->doc_number_separator)->toBe('-');
        expect($reloaded->doc_number_yearly_reset)->toBeTrue();
        expect($reloaded->quotation_validity_days)->toBe(7);
        expect($reloaded->stock_check_mode)->toBe('block');
    });

    it('validates required fields and ranges', function () {
        actAsAdmin();

        Livewire::test(SalesOrderSettingsForm::class)
            ->set('doc_number_prefix', '')
            ->set('quotation_validity_days', 0)
            ->set('stock_check_mode', 'invalid-value')
            ->call('save')
            ->assertHasErrors([
                'doc_number_prefix',
                'quotation_validity_days',
                'stock_check_mode',
            ]);
    });

    it('exposes a live numberPreview that mirrors the trait\'s pattern', function () {
        actAsAdmin();

        Livewire::test(SalesOrderSettingsForm::class)
            ->set('doc_number_prefix', 'QUO')
            ->set('doc_number_separator', '-')
            ->set('doc_number_padding', 3)
            ->set('doc_number_yearly_reset', true)
            ->tap(function ($component) {
                $year = now()->year;
                expect($component->instance()->numberPreview)->toBe("QUO-{$year}-001");
            });
    });
});
