<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Http\Response;

class ExportService
{
    /**
     * Export records to Excel
     */
    public static function toExcel(string $exportClass, string $filename, ?array $ids = null): BinaryFileResponse
    {
        $export = $ids ? new $exportClass($ids) : new $exportClass();
        return Excel::download($export, $filename . '_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    /**
     * Export records to CSV
     */
    public static function toCsv(string $exportClass, string $filename, ?array $ids = null): BinaryFileResponse
    {
        $export = $ids ? new $exportClass($ids) : new $exportClass();
        return Excel::download($export, $filename . '_' . now()->format('Y-m-d_His') . '.csv');
    }

    /**
     * Export data to PDF using a view template.
     */
    public static function toPdf(string $view, array $data, string $filename, string $orientation = 'portrait'): Response
    {
        $pdf = Pdf::loadView($view, array_merge($data, [
            'generatedAt' => now(),
            'companyName' => config('app.name'),
        ]));

        $pdf->setPaper('a4', $orientation);

        return $pdf->download($filename . '_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * Stream PDF to browser.
     */
    public static function streamPdf(string $view, array $data, string $filename, string $orientation = 'portrait'): Response
    {
        $pdf = Pdf::loadView($view, array_merge($data, [
            'generatedAt' => now(),
            'companyName' => config('app.name'),
        ]));

        $pdf->setPaper('a4', $orientation);

        return $pdf->stream($filename . '_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * Export report data to JSON.
     */
    public static function toJson(array $data, string $filename): Response
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        
        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '_' . now()->format('Y-m-d_His') . '.json"',
        ]);
    }

    /**
     * Generate filename with timestamp.
     */
    public static function generateFilename(string $prefix, string $extension = 'xlsx'): string
    {
        return $prefix . '_' . now()->format('Y-m-d_His') . '.' . $extension;
    }
}
