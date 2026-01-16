<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Simple sequence number: {PREFIX}{SEQUENCE}
 * Example: SO00001, DO00001
 * 
 * Note: For bulk inserts (like seeders), provide the number explicitly
 * to avoid race conditions. The auto-generation works best for single
 * record creation in normal application flow.
 */
trait HasSequenceNumber
{
    protected static function bootHasSequenceNumber(): void
    {
        static::creating(function ($model) {
            $column = $model->getNumberColumn();
            if (empty($model->{$column})) {
                $model->{$column} = static::generateSequenceNumber();
            }
        });
    }

    public static function generateSequenceNumber(): string
    {
        $model = new static;
        $prefix = $model->getNumberPrefix();
        $column = $model->getNumberColumn();
        $digits = $model->getNumberDigits();
        $driver = DB::connection()->getDriverName();

        // Include soft-deleted records to avoid number conflicts
        $usesSoftDeletes = in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive(static::class)
        );
        
        $query = $usesSoftDeletes ? static::withTrashed() : static::query();
        $query->where($column, 'like', $prefix . '%');
        
        if ($driver === 'pgsql') {
            $last = $query
                ->orderByRaw("CAST(SUBSTRING({$column}, ?) AS INTEGER) DESC", [strlen($prefix) + 1])
                ->value($column);
        } else {
            // SQLite uses SUBSTR instead of SUBSTRING
            $last = $query
                ->orderByRaw("CAST(SUBSTR({$column}, ?) AS INTEGER) DESC", [strlen($prefix) + 1])
                ->value($column);
        }

        $next = 1;
        if ($last) {
            $numericPart = substr($last, strlen($prefix));
            $next = ((int) $numericPart) + 1;
        }

        return $prefix . str_pad($next, $digits, '0', STR_PAD_LEFT);
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
