<?php

namespace App\Services\Reports;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ReportExportService
{
    /**
     * Export sales report to PDF.
     */
    public function exportSalesReportPdf(array $data, Carbon $startDate, Carbon $endDate): \Barryvdh\DomPDF\PDF
    {
        $pdf = Pdf::loadView('pdf.reports.sales', [
            'summary' => $data['summary'],
            'salesByPeriod' => $data['salesByPeriod'],
            'salesByCustomer' => $data['salesByCustomer'],
            'salesByProduct' => $data['salesByProduct'],
            'salespersonPerformance' => $data['salespersonPerformance'] ?? [],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now(),
            'companyName' => config('app.name'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Export inventory report to PDF.
     */
    public function exportInventoryReportPdf(array $data): \Barryvdh\DomPDF\PDF
    {
        $pdf = Pdf::loadView('pdf.reports.inventory', [
            'summary' => $data['summary'],
            'stockByWarehouse' => $data['stockByWarehouse'] ?? [],
            'lowStockItems' => $data['lowStockItems'] ?? [],
            'stockMovements' => $data['stockMovements'] ?? [],
            'generatedAt' => now(),
            'companyName' => config('app.name'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Export financial report to PDF.
     */
    public function exportFinancialReportPdf(array $data, Carbon $startDate, Carbon $endDate): \Barryvdh\DomPDF\PDF
    {
        $pdf = Pdf::loadView('pdf.reports.financial', [
            'summary' => $data['summary'],
            'revenueByMonth' => $data['revenueByMonth'] ?? [],
            'expensesByCategory' => $data['expensesByCategory'] ?? [],
            'profitLoss' => $data['profitLoss'] ?? [],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now(),
            'companyName' => config('app.name'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Generate filename for report.
     */
    public function generateFilename(string $reportType, ?Carbon $startDate = null, ?Carbon $endDate = null): string
    {
        $date = now()->format('Y-m-d_His');
        
        if ($startDate && $endDate) {
            $period = $startDate->format('Ymd') . '-' . $endDate->format('Ymd');
            return "{$reportType}_report_{$period}_{$date}.pdf";
        }

        return "{$reportType}_report_{$date}.pdf";
    }
}
