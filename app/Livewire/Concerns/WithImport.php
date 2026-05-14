<?php

namespace App\Livewire\Concerns;

use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;

trait WithImport
{
    use WithFileUploads;

    public bool $showImportModal = false;
    public $importFile;
    public ?string $importResult = null;

    /**
     * Each entry is either a structured array
     * (`['row'=>int,'attribute'=>?string,'message'=>string,'values'=>array]`)
     * or a plain string (legacy import classes that push directly into
     * `$import->errors`). The modal renders both shapes.
     */
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

        $importClass = $this->getImportClass();
        $import = new $importClass();

        try {
            Excel::import($import, $this->importFile->getRealPath());
        } catch (ExcelValidationException $e) {
            // Defense-in-depth: every shipped Import class now implements
            // SkipsOnFailure, so WithValidation routes failures through
            // onFailure() instead of throwing here. This branch stays as a
            // safety net for any future Import that forgets the interface —
            // structured errors so the modal still renders row + field +
            // message instead of a stringified-exception wall of text.
            foreach ($e->failures() as $failure) {
                if (method_exists($import, 'addValidationFailure')) {
                    $import->addValidationFailure($failure);
                } else {
                    $import->errors[] = [
                        'row'       => $failure->row(),
                        'attribute' => $failure->attribute(),
                        'message'   => implode('; ', $failure->errors()),
                        'values'    => $failure->values(),
                    ];
                }
            }
        } catch (\Throwable $e) {
            $import->errors[] = [
                'row'       => 0,
                'attribute' => null,
                'message'   => $e->getMessage(),
                'values'    => [],
            ];
        }

        $this->importResult = "Import completed: {$import->imported} created, {$import->updated} updated.";

        if (!empty($import->errors)) {
            $this->importErrors = $import->errors;
            session()->flash('import_errors_payload', $import->errors);
            return;
        }

        $this->closeImportModal();
        session()->flash('success', $this->importResult);
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
     * Build a CSV of the rows that failed and stream it to the user. The
     * source is the structured `$this->importErrors` populated by import().
     * Plain-string legacy errors get an "Error" column with no row data;
     * structured errors keep their original row values + the failed field +
     * message — drop straight back into Excel and re-upload after fixing.
     */
    public function downloadImportErrors(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $errors = $this->importErrors;

        return response()->streamDownload(function () use ($errors) {
            $out = fopen('php://output', 'w');

            // Collect the union of all original-row keys, in stable order.
            $rowKeys = [];
            foreach ($errors as $err) {
                if (is_array($err) && !empty($err['values']) && is_array($err['values'])) {
                    foreach (array_keys($err['values']) as $k) {
                        if (!in_array($k, $rowKeys, true)) {
                            $rowKeys[] = $k;
                        }
                    }
                }
            }

            fputcsv($out, array_merge(['Row', 'Field', 'Error'], $rowKeys));

            foreach ($errors as $err) {
                if (is_string($err)) {
                    fputcsv($out, ['', '', $err]);
                    continue;
                }
                $values = $err['values'] ?? [];
                $rowOut = [
                    $err['row'] ?? '',
                    $err['attribute'] ?? '',
                    $err['message'] ?? '',
                ];
                foreach ($rowKeys as $k) {
                    $rowOut[] = $values[$k] ?? '';
                }
                fputcsv($out, $rowOut);
            }

            fclose($out);
        }, 'import-errors-' . now()->format('Y-m-d-His') . '.csv', [
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
