<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Yearly sequence number: {PREFIX}{SEP}{YEAR}{SEP}{SEQUENCE}
 * Default: INV/2026/00001 — separator '/', yearly reset on.
 *
 * Models override `getNumberPrefix/Digits/Separator/UsesYearReset()` to
 * read from a settings model. SalesOrder reads from SalesOrderSetting,
 * for instance — admins can change "SO/2026/00001" to "QUO-2026-001"
 * without a code change.
 *
 * Note: For bulk inserts (like seeders), provide the number explicitly
 * to avoid race conditions. The auto-generation works best for single
 * record creation in normal application flow.
 */
trait HasYearlySequenceNumber
{
    protected static function bootHasYearlySequenceNumber(): void
    {
        static::creating(function ($model) {
            $column = $model->getNumberColumn();
            if (empty($model->{$column})) {
                $model->{$column} = static::generateYearlySequenceNumber($model);
            }
        });
    }

    public static function generateYearlySequenceNumber($model = null): string
    {
        $model     = $model ?? new static;
        $prefix    = $model->getNumberPrefix();
        $column    = $model->getNumberColumn();
        $digits    = $model->getNumberDigits();
        $separator = $model->getNumberSeparator();
        $useYear   = $model->getNumberUsesYearReset();
        $year      = now()->year;
        $driver    = DB::connection()->getDriverName();

        $pattern = $useYear
            ? "{$prefix}{$separator}{$year}{$separator}"
            : "{$prefix}{$separator}";
        $start = strlen($pattern) + 1;

        // Include soft-deleted records to avoid number conflicts
        $usesSoftDeletes = in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive(static::class)
        );

        $query = $usesSoftDeletes ? static::withTrashed() : static::query();
        $query->where($column, 'like', $pattern . '%');

        if ($driver === 'pgsql') {
            $maxNumber = $query->selectRaw("MAX(CAST(SUBSTRING({$column} FROM {$start}) AS INTEGER)) as max_num")->value('max_num');
        } else {
            // SQLite uses SUBSTR instead of SUBSTRING
            $maxNumber = $query->selectRaw("MAX(CAST(SUBSTR({$column}, {$start}) AS INTEGER)) as max_num")->value('max_num');
        }

        $next = ($maxNumber ?? 0) + 1;

        return $pattern . str_pad((string) $next, $digits, '0', STR_PAD_LEFT);
    }

    public function getNumberPrefix(): string
    {
        return defined('static::NUMBER_PREFIX') ? static::NUMBER_PREFIX : 'DOC';
    }

    public function getNumberColumn(): string
    {
        return defined('static::NUMBER_COLUMN') ? static::NUMBER_COLUMN : 'number';
    }

    public function getNumberDigits(): int
    {
        return defined('static::NUMBER_DIGITS') ? static::NUMBER_DIGITS : 5;
    }

    /**
     * Separator between prefix / year / sequence. Default '/'.
     * Override to read from a settings model.
     */
    public function getNumberSeparator(): string
    {
        return defined('static::NUMBER_SEPARATOR') ? static::NUMBER_SEPARATOR : '/';
    }

    /**
     * Whether the sequence resets each year (and the year is part of the
     * format). Default true. When false, the format becomes
     * `{PREFIX}{SEP}{SEQUENCE}` and counts globally.
     */
    public function getNumberUsesYearReset(): bool
    {
        return defined('static::NUMBER_USES_YEAR_RESET') ? static::NUMBER_USES_YEAR_RESET : true;
    }
}
