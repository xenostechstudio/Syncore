<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Yearly sequence number: {PREFIX}/{YEAR}/{SEQUENCE}
 * Example: INV/2026/00001, PAY/2026/00001
 * Resets sequence each year
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
                $model->{$column} = static::generateYearlySequenceNumber();
            }
        });
    }

    public static function generateYearlySequenceNumber(): string
    {
        $model = new static;
        $prefix = $model->getNumberPrefix();
        $column = $model->getNumberColumn();
        $digits = $model->getNumberDigits();
        $year = now()->year;
        $driver = DB::connection()->getDriverName();

        $pattern = "{$prefix}/{$year}/";
        
        // Include soft-deleted records to avoid number conflicts
        $usesSoftDeletes = in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive(static::class)
        );
        
        $query = $usesSoftDeletes ? static::withTrashed() : static::query();
        $query->where($column, 'like', $pattern . '%');
        
        if ($driver === 'pgsql') {
            $last = $query
                ->orderByRaw("CAST(SUBSTRING({$column}, ?) AS INTEGER) DESC", [strlen($pattern) + 1])
                ->value($column);
        } else {
            // SQLite uses SUBSTR instead of SUBSTRING
            $last = $query
                ->orderByRaw("CAST(SUBSTR({$column}, ?) AS INTEGER) DESC", [strlen($pattern) + 1])
                ->value($column);
        }

        $next = 1;
        if ($last) {
            $numericPart = substr($last, strlen($pattern));
            $next = ((int) $numericPart) + 1;
        }

        return $pattern . str_pad($next, $digits, '0', STR_PAD_LEFT);
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
}
