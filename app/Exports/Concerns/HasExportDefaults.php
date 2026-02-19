<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * HasExportDefaults Trait
 * 
 * Provides common export functionality for Excel exports.
 * Includes default styling and ID filtering.
 * 
 * Usage:
 * ```php
 * class MyExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
 * {
 *     use HasExportDefaults;
 *     
 *     public function collection() { ... }
 *     public function headings(): array { ... }
 * }
 * ```
 */
trait HasExportDefaults
{
    protected ?array $ids = null;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    /**
     * Default styles for exports - bold header row.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Apply ID filter to query if IDs are provided.
     */
    protected function applyIdFilter($query)
    {
        return $this->ids ? $query->whereIn('id', $this->ids) : $query;
    }

    /**
     * Format a date value for export.
     */
    protected function formatDate($date, string $format = 'Y-m-d'): string
    {
        return $date?->format($format) ?? '-';
    }

    /**
     * Format a datetime value for export.
     */
    protected function formatDateTime($date): string
    {
        return $date?->format('Y-m-d H:i') ?? '-';
    }

    /**
     * Format a status value for export.
     */
    protected function formatStatus(?string $status): string
    {
        if (!$status) {
            return '-';
        }
        return ucfirst(str_replace('_', ' ', $status));
    }

    /**
     * Format a currency value for export.
     */
    protected function formatCurrency($value, int $decimals = 2): string
    {
        return number_format((float) ($value ?? 0), $decimals);
    }

    /**
     * Get a value or default placeholder.
     */
    protected function valueOrDefault($value, string $default = '-'): string
    {
        return $value ?? $default;
    }
}
