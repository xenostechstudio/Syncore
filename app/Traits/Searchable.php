<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Searchable Trait
 * 
 * Provides a standardized search scope for models with configurable searchable columns.
 * Automatically handles database-agnostic case-insensitive search.
 * 
 * Usage:
 * ```php
 * class Customer extends Model
 * {
 *     use Searchable;
 *     
 *     protected array $searchable = ['name', 'email', 'phone'];
 * }
 * 
 * // Then in queries:
 * Customer::search('john')->get();
 * Customer::search('john', ['name', 'email'])->get(); // Override columns
 * ```
 * 
 * @package App\Traits
 */
trait Searchable
{
    /**
     * Scope a query to search across searchable columns.
     *
     * @param Builder $query
     * @param string|null $term Search term
     * @param array|null $columns Override default searchable columns
     * @return Builder
     */
    public function scopeSearch(Builder $query, ?string $term, ?array $columns = null): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $searchColumns = $columns ?? $this->getSearchableColumns();

        if (empty($searchColumns)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term, $searchColumns) {
            foreach ($searchColumns as $column) {
                if (str_contains($column, '.')) {
                    // Handle relationship columns (e.g., 'customer.name')
                    [$relation, $relationColumn] = explode('.', $column, 2);
                    $q->orWhereHas($relation, function (Builder $subQuery) use ($relationColumn, $term) {
                        $subQuery->where($relationColumn, 'like', "%{$term}%");
                    });
                } else {
                    $q->orWhere($column, 'like', "%{$term}%");
                }
            }
        });
    }

    /**
     * Get the columns that should be searchable.
     *
     * @return array
     */
    public function getSearchableColumns(): array
    {
        return $this->searchable ?? ['name'];
    }

    /**
     * Scope to search with exact match on a specific column.
     *
     * @param Builder $query
     * @param string $column
     * @param mixed $value
     * @return Builder
     */
    public function scopeSearchExact(Builder $query, string $column, mixed $value): Builder
    {
        if ($value === null || $value === '') {
            return $query;
        }

        return $query->where($column, $value);
    }

    /**
     * Scope to search within a date range.
     *
     * @param Builder $query
     * @param string $column
     * @param string|null $from
     * @param string|null $to
     * @return Builder
     */
    public function scopeSearchDateRange(Builder $query, string $column, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate($column, '>=', $from);
        }

        if ($to) {
            $query->whereDate($column, '<=', $to);
        }

        return $query;
    }
}
