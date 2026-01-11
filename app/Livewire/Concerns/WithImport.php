<?php

namespace App\Livewire\Concerns;

use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

trait WithImport
{
    use WithFileUploads;

    public bool $showImportModal = false;
    public $importFile;
    public ?string $importResult = null;
    public array $importErrors = [];

    public function openImportModal(): void
    {
        $this->showImportModal = true;
        $this->importFile = null;
        $this->importResult = null;
        $this->importErrors = [];
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->importFile = null;
        $this->importResult = null;
        $this->importErrors = [];
    }

    public function import(): void
    {
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $importClass = $this->getImportClass();
            $import = new $importClass();
            
            Excel::import($import, $this->importFile->getRealPath());

            $this->importResult = "Import completed: {$import->imported} created, {$import->updated} updated.";
            
            if (!empty($import->errors)) {
                $this->importErrors = $import->errors;
            }

            if (empty($import->errors)) {
                $this->closeImportModal();
                session()->flash('success', $this->importResult);
            }
        } catch (\Exception $e) {
            $this->importErrors = [$e->getMessage()];
        }
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $template = $this->getImportTemplate();
        $content = implode(',', $template['headers']) . "\n";

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $template['filename'], [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Override this method in the Index component to specify the import class
     */
    abstract protected function getImportClass(): string;

    /**
     * Override this method in the Index component to specify the template
     */
    abstract protected function getImportTemplate(): array;
}
