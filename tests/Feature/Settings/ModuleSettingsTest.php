<?php

/**
 * Smoke for the SalesOrderSetting and PurchaseOrderSetting singleton models.
 * The data layer is the foundation for the SO/PO settings UI built in
 * subsequent commits — pin these contracts before we wire them into
 * HasYearlySequenceNumber, the PDF templates, the auto-send flows, etc.
 */

use App\Models\Settings\PurchaseOrderSetting;
use App\Models\Settings\SalesOrderSetting;

describe('SalesOrderSetting', function () {
    it('creates a row with sensible defaults on first instance() call', function () {
        SalesOrderSetting::clearCache();
        $settings = SalesOrderSetting::instance();

        // Defaults match current production format ("SO00001") so existing
        // installs don't see their numbering change after the migration.
        expect($settings)->toBeInstanceOf(SalesOrderSetting::class)
            ->and($settings->doc_number_prefix)->toBe('SO')
            ->and($settings->doc_number_separator)->toBe('')
            ->and($settings->doc_number_padding)->toBe(5)
            ->and($settings->doc_number_yearly_reset)->toBeFalse()
            ->and($settings->quotation_validity_days)->toBe(30)
            ->and($settings->auto_send_on_confirm)->toBeFalse()
            ->and($settings->stock_check_mode)->toBe('warn');
    });

    it('returns the same row on subsequent instance() calls (singleton)', function () {
        $a = SalesOrderSetting::instance();
        $b = SalesOrderSetting::instance();

        expect($a->id)->toBe($b->id);
        expect(SalesOrderSetting::count())->toBe(1);
    });

    it('formats document numbers in the default no-year format (matches current SO production)', function () {
        SalesOrderSetting::clearCache();
        $settings = SalesOrderSetting::instance();

        // With separator='' and yearly_reset=false the format is just
        // {prefix}{padded} — i.e. "SO00001". Same as production today.
        expect($settings->formatDocumentNumber(1))->toBe('SO00001');
        expect($settings->formatDocumentNumber(42))->toBe('SO00042');
    });

    it('formats document numbers with yearly reset when admin enables it', function () {
        SalesOrderSetting::clearCache();
        $settings = SalesOrderSetting::instance();
        $settings->update(['doc_number_separator' => '/', 'doc_number_yearly_reset' => true]);
        SalesOrderSetting::clearCache();

        expect(SalesOrderSetting::instance()->formatDocumentNumber(7, 2026))->toBe('SO/2026/00007');
    });

    it('honors a custom prefix, separator, and padding', function () {
        SalesOrderSetting::clearCache();
        $settings = SalesOrderSetting::instance();
        $settings->update([
            'doc_number_prefix'       => 'QUO',
            'doc_number_separator'    => '-',
            'doc_number_padding'      => 3,
            'doc_number_yearly_reset' => true,
        ]);
        SalesOrderSetting::clearCache();

        expect(SalesOrderSetting::instance()->formatDocumentNumber(7, 2026))->toBe('QUO-2026-007');
    });

    it('persists writes and survives a fresh load', function () {
        SalesOrderSetting::clearCache();
        $original = SalesOrderSetting::instance();
        $original->update([
            'quotation_validity_days' => 14,
            'auto_send_on_confirm'    => true,
            'stock_check_mode'        => 'block',
        ]);
        SalesOrderSetting::clearCache();

        $reloaded = SalesOrderSetting::instance();
        expect($reloaded->quotation_validity_days)->toBe(14)
            ->and($reloaded->auto_send_on_confirm)->toBeTrue()
            ->and($reloaded->stock_check_mode)->toBe('block');
    });
});

describe('PurchaseOrderSetting', function () {
    it('creates a row with sensible defaults on first instance() call', function () {
        $settings = PurchaseOrderSetting::instance();

        expect($settings->doc_number_prefix)->toBe('PO')
            ->and($settings->doc_number_padding)->toBe(5)
            ->and($settings->doc_number_yearly_reset)->toBeTrue()
            ->and($settings->default_lead_time_days)->toBe(7)
            ->and($settings->auto_send_to_supplier)->toBeFalse()
            ->and($settings->approval_threshold)->toBeNull();
    });

    it('formats document numbers like SalesOrder', function () {
        $settings = PurchaseOrderSetting::instance();

        expect($settings->formatDocumentNumber(1, 2026))->toBe('PO/2026/00001');
    });

    it('returns false from requiresApproval when no threshold is set', function () {
        $settings = PurchaseOrderSetting::instance();

        expect($settings->requiresApproval(1_000_000))->toBeFalse();
        expect($settings->requiresApproval(99_999_999))->toBeFalse();
    });

    it('returns true from requiresApproval at or above the threshold', function () {
        $settings = PurchaseOrderSetting::instance();
        $settings->update(['approval_threshold' => 5_000_000]);

        expect($settings->fresh()->requiresApproval(4_999_999))->toBeFalse();
        expect($settings->fresh()->requiresApproval(5_000_000))->toBeTrue();
        expect($settings->fresh()->requiresApproval(10_000_000))->toBeTrue();
    });

    it('is a singleton', function () {
        $a = PurchaseOrderSetting::instance();
        $b = PurchaseOrderSetting::instance();

        expect($a->id)->toBe($b->id);
        expect(PurchaseOrderSetting::count())->toBe(1);
    });
});

describe('Branded error pages', function () {
    it('serves the branded 404 when an unknown route is requested', function () {
        // Pass through to the framework's exception handler so the rendered
        // HTML view (errors/404.blade.php → errors/_shell.blade.php) is what
        // actually responds.
        $response = $this->get('/this-route-definitely-does-not-exist-' . uniqid());

        $response->assertStatus(404);
        expect($response->getContent())->toContain('Page not found');
        expect($response->getContent())->toContain(config('app.name'));
    });
});
