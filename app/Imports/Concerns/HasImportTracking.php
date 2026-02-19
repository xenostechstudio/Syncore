<?php

namespace App\Imports\Concerns;

/**
 * Shared import tracking functionality.
 * 
 * Provides common properties and methods for tracking import results.
 */
trait HasImportTracking
{
    public int $imported = 0;
    public int $updated = 0;
    public int $skipped = 0;
    public array $errors = [];

    /**
     * Add an error message for a specific row.
     */
    protected function addError(int $rowIndex, string $message): void
    {
        $this->errors[] = "Row " . ($rowIndex + 2) . ": " . $message;
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
