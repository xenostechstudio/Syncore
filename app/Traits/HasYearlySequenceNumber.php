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
                $model->{$column} = static::generateYearlySequenceNumber($model);
            }
        });
    }

    public static function generateYearlySequenceNumber($model = null): string
    {
        $model = $model ?? new static;
        $prefix = $model->getNumberPrefix();
        $column = $model->getNumberColumn();
        $digits = $model->getNumberDigits();
        $year = now()->year;
        $driver = DB::connection()->getDriverName();

        $pattern = "{$prefix}/{$year}/";
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
