<?php

namespace App\Imports\Concerns;

use Maatwebsite\Excel\Validators\Failure;

/**
 * Shared import tracking functionality.
 *
 * Provides common properties and methods for tracking import results.
 *
 * Errors are structured arrays so the UI can render them as a Row|Field|Message
 * table and produce a downloadable error report. Each entry shape:
 *
 *   ['row' => int, 'attribute' => ?string, 'message' => string, 'values' => array]
 *
 * Legacy callers that pushed plain strings still work — the modal renders
 * both shapes.
 */
trait HasImportTracking
{
    public int $imported = 0;
    public int $updated = 0;
    public int $skipped = 0;
    public array $errors = [];

    /**
     * Add an error message for a specific row. The row index is 0-based as
     * passed in by Maatwebsite collection iteration; we offset by 2 so the
     * displayed row matches the spreadsheet (header row + 1-based numbering).
     */
    protected function addError(int $rowIndex, string $message, ?string $attribute = null, array $values = []): void
    {
        $this->errors[] = [
            'row'       => $rowIndex + 2,
            'attribute' => $attribute,
            'message'   => $message,
            'values'    => $values,
        ];
    }

    /**
     * Convert a Maatwebsite validation Failure into a structured error.
     * Called from two places:
     *   - onFailure() below, when the import class implements
     *     SkipsOnFailure: invalid rows are collected, valid rows keep
     *     processing.
     *   - WithImport::import()'s catch block, as a fallback for import
     *     classes that don't yet implement SkipsOnFailure.
     */
    public function addValidationFailure(Failure $failure): void
    {
        foreach ($failure->errors() as $message) {
            $this->errors[] = [
                'row'       => $failure->row(),
                'attribute' => $failure->attribute(),
                'message'   => $message,
                'values'    => $failure->values(),
            ];
        }
    }

    /**
     * Satisfies Maatwebsite\Excel\Concerns\SkipsOnFailure. With this in
     * the shared trait, an import class only needs to add the interface
     * to its `implements` list to flip from abort-on-first-error to
     * collect-and-continue behavior.
     *
     * @param \Maatwebsite\Excel\Validators\Failure ...$failures
     */
    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->addValidationFailure($failure);
        }
    }

    /**
     * Parse a numeric value from various formats.
     */
    protected function parseNumber($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (empty($value)) {
            return 0.0;
        }

        // Remove currency symbols and thousands separators
        $cleaned = preg_replace('/[^0-9.,\-]/', '', (string) $value);
        
        // Handle different decimal separators
        if (preg_match('/,\d{2}$/', $cleaned)) {
            // European format: 1.234,56
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } else {
            // US format: 1,234.56
            $cleaned = str_replace(',', '', $cleaned);
        }

        return (float) $cleaned;
    }

    /**
     * Parse a date value from various formats.
     */
    protected function parseDate($value): ?\Carbon\Carbon
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \Carbon\Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return \Carbon\Carbon::instance($value);
        }

        // Handle Excel serial date numbers
        if (is_numeric($value)) {
            return \Carbon\Carbon::createFromTimestamp(
                ($value - 25569) * 86400
            );
        }

        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get a trimmed string value or null.
     */
    protected function getString($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Get import summary.
     */
    public function getSummary(): array
    {
        return [
            'imported' => $this->imported,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
            'total_processed' => $this->imported + $this->updated + $this->skipped,
            'has_errors' => count($this->errors) > 0,
        ];
    }
}
