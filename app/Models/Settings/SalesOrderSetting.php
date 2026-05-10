<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Singleton settings model for the Sales Order module. Mirrors the
 * InvoiceSetting pattern: `instance()` returns the (created-on-demand)
 * single row; the consuming code reads typed properties off it.
 *
 * Settings here drive customer-facing behavior — document numbering,
 * quotation validity, default terms, and the auto-send / stock-check
 * workflow gates.
 */
class SalesOrderSetting extends Model
{
    protected $fillable = [
        'doc_number_prefix',
        'doc_number_separator',
        'doc_number_padding',
        'doc_number_yearly_reset',
        'quotation_validity_days',
        'default_terms',
        'default_notes',
        'auto_send_on_confirm',
        'stock_check_mode',
        'default_payment_term_id',
    ];

    protected $casts = [
        'doc_number_padding'      => 'integer',
        'doc_number_yearly_reset' => 'boolean',
        'quotation_validity_days' => 'integer',
        'auto_send_on_confirm'    => 'boolean',
    ];

    /**
     * Get the singleton instance, creating it with sensible defaults if it
     * doesn't exist yet. Same pattern as InvoiceSetting::instance().
     */
    public static function instance(): self
    {
        return static::firstOrCreate([], [
            'doc_number_prefix'       => 'SO',
            'doc_number_separator'    => '/',
            'doc_number_padding'      => 5,
            'doc_number_yearly_reset' => true,
            'quotation_validity_days' => 30,
            'auto_send_on_confirm'    => false,
            'stock_check_mode'        => 'warn',
        ]);
    }

    public function defaultPaymentTerm(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Sales\PaymentTerm::class, 'default_payment_term_id');
    }

    /**
     * Build a document number from the configured pattern. Mirrors what
     * HasYearlySequenceNumber does today, but lets admins control prefix /
     * padding / separator / yearly-reset behavior. The trait will call this
     * once it's wired up in the next commit.
     */
    public function formatDocumentNumber(int $sequence, ?int $year = null): string
    {
        $year = $year ?? now()->year;
        $padded = str_pad((string) $sequence, $this->doc_number_padding, '0', STR_PAD_LEFT);

        if ($this->doc_number_yearly_reset) {
            return "{$this->doc_number_prefix}{$this->doc_number_separator}{$year}{$this->doc_number_separator}{$padded}";
        }

        return "{$this->doc_number_prefix}{$this->doc_number_separator}{$padded}";
    }
}
