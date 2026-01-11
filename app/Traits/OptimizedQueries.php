<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * OptimizedQueries Trait
 * 
 * Provides query optimization methods for Eloquent models.
 */
trait OptimizedQueries
{
    /**
     * Scope to select only specific columns for list views.
     */
    public function scopeListSelect(Builder $query): Builder
    {
        $columns = $this->listColumns ?? ['id', 'name', 'created_at'];
        return $query->select($columns);
    }

    /**
     * Scope to eager load common relationships.
     */
    public function scopeWithCommon(Builder $query): Builder
    {
        $relations = $this->commonRelations ?? [];
        return $query->with($relations);
    }

    /**
     * Scope for efficient pagination with cursor.
     */
    public function scopeCursorPaginate(Builder $query, int $perPage = 15): \Illuminate\Contracts\Pagination\CursorPaginator
    {
        return $query->cursorPaginate($perPage);
    }

    /**
     * Scope to apply common filters.
     */
    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (in_array($field, $this->filterableFields ?? [])) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        return $query;
    }

    /**
     * Scope to apply search across searchable fields.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (!$search) {
            return $query;
        }

        $searchableFields = $this->searchableFields ?? ['name'];

        return $query->where(function ($q) use ($search, $searchableFields) {
            foreach ($searchableFields as $field) {
                $q->orWhere($field, 'ilike', "%{$search}%");
            }
        });
    }

    /**
     * Scope to apply date range filter.
     */
    public function scopeDateRange(Builder $query, string $field, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate($field, '>=', $from);
        }

        if ($to) {
            $query->whereDate($field, '<=', $to);
        }

        return $query;
    }

    /**
     * Scope to order by common fields.
     */
    public function scopeDefaultOrder(Builder $query): Builder
    {
        $orderBy = $this->defaultOrderBy ?? 'created_at';
        $orderDir = $this->defaultOrderDir ?? 'desc';

        return $query->orderBy($orderBy, $orderDir);
    }

    /**
     * Get count with caching.
     */
    public static function cachedCount(?string $cacheKey = null, int $ttl = 300): int
    {
        $key = $cacheKey ?? 'count_' . strtolower(class_basename(static::class));
        
        return cache()->remember($key, $ttl, function () {
            return static::count();
        });
    }

    /**
     * Chunk process for large datasets.
     */
    public static function processInChunks(callable $callback, int $chunkSize = 1000): void
    {
        static::query()->chunkById($chunkSize, $callback);
    }
}
