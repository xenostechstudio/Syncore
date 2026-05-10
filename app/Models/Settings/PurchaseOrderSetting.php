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
            // Defaults match historical form output: "RFQ-NNNNN".
            // Admins can switch on yearly_reset / change prefix in the
            // settings UI to get "RFQ/2026/00001", "PO-2026-001", etc.
            'doc_number_prefix'       => 'RFQ',
            'doc_number_separator'    => '-',
            'doc_number_padding'      => 5,
            'doc_number_yearly_reset' => false,
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
     * Compute the next sequential document number for purchase records.
     * Mirrors what HasYearlySequenceNumber does for Eloquent saves, but
     * usable from non-Eloquent call sites (the Rfq form persists via
     * DB::table directly, bypassing the trait's `creating` event). One
     * source of truth, both paths agree on the format.
     *
     * Concurrency caveat: this is a best-effort generator like the trait
     * — under heavy concurrent insert load you can still race. The form's
     * single-user-action shape makes that unlikely; if it ever matters,
     * wrap the caller in a transaction with SELECT FOR UPDATE on a
     * sequences row.
     */
    public function nextDocumentNumber(): string
    {
        $year   = now()->year;
        $prefix = $this->doc_number_prefix;
        $sep    = $this->doc_number_separator;

        $pattern = $this->doc_number_yearly_reset
            ? "{$prefix}{$sep}{$year}{$sep}"
            : "{$prefix}{$sep}";
        $start = strlen($pattern) + 1;

        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $substr = $driver === 'pgsql'
            ? "SUBSTRING(reference FROM {$start})"
            : "SUBSTR(reference, {$start})";

        $maxSeq = (int) \Illuminate\Support\Facades\DB::table('purchase_rfqs')
            ->where('reference', 'like', $pattern . '%')
            ->selectRaw("MAX(CAST({$substr} AS INTEGER)) as max_seq")
            ->value('max_seq');

        return $this->formatDocumentNumber($maxSeq + 1, $year);
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
