<?php

namespace App\Http\Controllers\Api;

use App\Services\DashboardService;
use App\Services\Reports\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $useCache = !$request->boolean('fresh');
        $data = DashboardService::getAllDashboardData($useCache);

        return $this->success($data);
    }

    public function sales(Request $request): JsonResponse
    {
        $reportService = new ReportService();
        $data = $reportService->getSalesWidgetData(!$request->boolean('fresh'));

        return $this->success($data);
    }

    public function inventory(Request $request): JsonResponse
    {
        $reportService = new ReportService();
        $data = $reportService->getInventoryWidgetData(!$request->boolean('fresh'));

        return $this->success($data);
    }

    public function invoicing(Request $request): JsonResponse
    {
        $reportService = new ReportService();
        $data = $reportService->getInvoicingWidgetData(!$request->boolean('fresh'));

        return $this->success($data);
    }

    public function hr(Request $request): JsonResponse
    {
        $reportService = new ReportService();
        $data = $reportService->getHRWidgetData(!$request->boolean('fresh'));

        return $this->success($data);
    }

    public function crm(Request $request): JsonResponse
    {
        $reportService = new ReportService();
        $data = $reportService->getCRMWidgetData(!$request->boolean('fresh'));

        return $this->success($data);
    }

    public function purchase(Request $request): JsonResponse
    {
        $reportService = new ReportService();
        $data = $reportService->getPurchaseWidgetData(!$request->boolean('fresh'));

        return $this->success($data);
    }

    public function kpi(): JsonResponse
    {
        $data = [
            'sales' => DashboardService::getSalesMetrics(),
            'invoices' => DashboardService::getInvoiceMetrics(),
            'inventory' => DashboardService::getInventoryMetrics(),
            'purchases' => DashboardService::getPurchaseMetrics(),
            'pending_actions' => DashboardService::getPendingActions(),
            'cash_flow' => DashboardService::getCashFlowSummary(),
        ];

        return $this->success($data);
    }
}
