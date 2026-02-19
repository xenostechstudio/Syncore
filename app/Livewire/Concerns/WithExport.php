<?php

namespace App\Livewire\Concerns;

use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * WithExport Trait
 * 
 * Provides export functionality for Livewire components.
 * Supports exporting all records or selected records only.
 * 
 * Usage:
 * ```php
 * class MyIndex extends Component
 * {
 *     use WithExport;
 *     
 *     protected function getExportClass(): string
 *     {
 *         return MyExport::class;
 *     }
 *     
 *     protected function getExportFilename(): string
 *     {
 *         return 'my-records';
 *     }
 * }
 * ```
 */
trait WithExport
{
    public bool $showExportModal = false;
    public string $exportFormat = 'xlsx';
    public bool $exportSelectedOnly = false;

    /**
     * Open export modal.
     */
    public function openExportModal(): void
    {
        $this->showExportModal = true;
        $this->exportFormat = 'xlsx';
        $this->exportSelectedOnly = false;
    }

    /**
     * Close export modal.
     */
    public function closeExportModal(): void
    {
        $this->showExportModal = false;
    }

    /**
     * Export records.
     */
    public function export(): BinaryFileResponse
    {
        $exportClass = $this->getExportClass();
        $filename = $this->getExportFilename();
        $extension = $this->exportFormat;

        // Get IDs to export (selected or all)
        $ids = null;
        if ($this->exportSelectedOnly && property_exists($this, 'selected') && !empty($this->selected)) {
            $ids = $this->selected;
        }

        $this->closeExportModal();

        return Excel::download(
            new $exportClass($ids),
            "{$filename}-" . now()->format('Y-m-d') . ".{$extension}"
        );
    }

    /**
     * Quick export all records.
     */
    public function exportAll(): BinaryFileResponse
    {
        $this->exportSelectedOnly = false;
        return $this->export();
    }

    /**
     * Quick export selected records only.
     */
    public function exportSelected(): BinaryFileResponse
    {
        $this->exportSelectedOnly = true;
        return $this->export();
    }

    /**
     * Get the export class to use.
     * Override this method in your component.
     */
    protected function getExportClass(): string
    {
        throw new \RuntimeException('Export class not defined. Override getExportClass() method.');
    }

    /**
     * Get the export filename (without extension).
     * Override this method in your component.
     */
    protected function getExportFilename(): string
    {
        return 'export';
    }

    /**
     * Get available export formats.
     */
    public function getExportFormats(): array
    {
        return [
            'xlsx' => 'Excel (.xlsx)',
            'csv' => 'CSV (.csv)',
            'pdf' => 'PDF (.pdf)',
        ];
    }
}
