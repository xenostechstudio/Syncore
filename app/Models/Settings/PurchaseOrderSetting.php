<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Singleton settings model for the Purchase Order module. Mirrors the
 * InvoiceSetting / SalesOrderSetting pattern.
 *
 * Settings here drive supplier-facing behavior — document numbering,
 * default warehouse, lead time, and the auto-send / approval workflow
 * gates.
 */
class PurchaseOrderSetting extends Model
{
    protected $fillable = [
        'doc_number_prefix',
        'doc_number_separator',
        'doc_number_padding',
        'doc_number_yearly_reset',
        'default_warehouse_id',
        'default_lead_time_days',
        'auto_send_to_supplier',
        'approval_threshold',
        'default_terms',
        'default_notes',
    ];

    protected $casts = [
        'doc_number_padding'      => 'integer',
        'doc_number_yearly_reset' => 'boolean',
        'default_lead_time_days'  => 'integer',
        'auto_send_to_supplier'   => 'boolean',
        'approval_threshold'      => 'decimal:2',
    ];

    protected static ?self $cached = null;

    public static function instance(): self
    {
        if (static::$cached) {
            return static::$cached;
        }

        return static::$cached = static::firstOrCreate([], [
            'doc_number_prefix'       => 'PO',
            'doc_number_separator'    => '/',
            'doc_number_padding'      => 5,
            'doc_number_yearly_reset' => true,
            'default_lead_time_days'  => 7,
            'auto_send_to_supplier'   => false,
        ]);
    }

    public static function clearCache(): void
    {
        static::$cached = null;
    }

    public function defaultWarehouse(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Inventory\Warehouse::class, 'default_warehouse_id');
    }

    public function formatDocumentNumber(int $sequence, ?int $year = null): string
    {
        $year = $year ?? now()->year;
        $padded = str_pad((string) $sequence, $this->doc_number_padding, '0', STR_PAD_LEFT);

        if ($this->doc_number_yearly_reset) {
            return "{$this->doc_number_prefix}{$this->doc_number_separator}{$year}{$this->doc_number_separator}{$padded}";
        }

        return "{$this->doc_number_prefix}{$this->doc_number_separator}{$padded}";
    }

    /**
     * True when an amount is at or above the configured approval threshold
     * (and a threshold is configured at all). Returns false when threshold
     * is null — the existing "no approval workflow" mode.
     */
    public function requiresApproval(float $amount): bool
    {
        if (blank($this->approval_threshold)) {
            return false;
        }

        return $amount >= (float) $this->approval_threshold;
    }
}
