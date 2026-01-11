<?php

namespace App\Traits;

/**
 * HasDocumentNumber Trait
 * 
 * Provides standardized document number generation for models.
 * Supports configurable prefixes, year-based numbering, and padding.
 * 
 * Usage:
 * ```php
 * class Invoice extends Model
 * {
 *     use HasDocumentNumber;
 *     
 *     protected string $documentNumberColumn = 'invoice_number';
 *     protected string $documentNumberPrefix = 'INV';
 *     protected bool $documentNumberIncludeYear = true;
 *     protected int $documentNumberPadding = 5;
 * }
 * ```
 * 
 * @package App\Traits
 */
trait HasDocumentNumber
{
    /**
     * Boot the HasDocumentNumber trait.
     * Automatically generates document number on model creation.
     *
     * @return void
     */
    protected static function bootHasDocumentNumber(): void
    {
        static::creating(function ($model) {
            $column = $model->getDocumentNumberColumn();
            
            if (empty($model->{$column})) {
                $model->{$column} = $model->generateDocumentNumber();
            }
        });
    }

    /**
     * Generate a new document number.
     *
     * @return string
     */
    public function generateDocumentNumber(): string
    {
        $prefix = $this->getDocumentNumberPrefix();
        $includeYear = $this->getDocumentNumberIncludeYear();
        $padding = $this->getDocumentNumberPadding();
        $column = $this->getDocumentNumberColumn();

        if ($includeYear) {
            $year = now()->year;
            $searchPrefix = "{$prefix}/{$year}/";
        } else {
            $searchPrefix = $prefix;
        }

        $lastNumber = static::where($column, 'like', $searchPrefix . '%')
            ->pluck($column)
            ->map(function (string $number) use ($searchPrefix) {
                return (int) substr($number, strlen($searchPrefix));
            })
            ->max() ?? 0;

        $nextNumber = $lastNumber + 1;

        if ($includeYear) {
            return $searchPrefix . str_pad($nextNumber, $padding, '0', STR_PAD_LEFT);
        }

        return $prefix . str_pad($nextNumber, $padding, '0', STR_PAD_LEFT);
    }

    /**
     * Get the column name for the document number.
     *
     * @return string
     */
    protected function getDocumentNumberColumn(): string
    {
        return $this->documentNumberColumn ?? 'document_number';
    }

    /**
     * Get the prefix for the document number.
     *
     * @return string
     */
    protected function getDocumentNumberPrefix(): string
    {
        return $this->documentNumberPrefix ?? 'DOC';
    }

    /**
     * Whether to include year in the document number.
     *
     * @return bool
     */
    protected function getDocumentNumberIncludeYear(): bool
    {
        return $this->documentNumberIncludeYear ?? true;
    }

    /**
     * Get the padding length for the sequential number.
     *
     * @return int
     */
    protected function getDocumentNumberPadding(): int
    {
        return $this->documentNumberPadding ?? 5;
    }
}
