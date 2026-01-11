<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app-home')]
#[Title('Dashboard')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.dashboard.index', [
            'salesMetrics' => DashboardService::getSalesMetrics(),
            'invoiceMetrics' => DashboardService::getInvoiceMetrics(),
            'inventoryMetrics' => DashboardService::getInventoryMetrics(),
            'purchaseMetrics' => DashboardService::getPurchaseMetrics(),
            'pendingActions' => DashboardService::getPendingActions(),
            'cashFlow' => DashboardService::getCashFlowSummary(),
            'topCustomers' => DashboardService::getTopCustomers(5),
            'topProducts' => DashboardService::getTopProducts(5),
            'salesChartData' => DashboardService::getSalesChartData(6),
            'lowStockProducts' => DashboardService::getLowStockProducts(5),
            'recentOrders' => DashboardService::getRecentOrders(5),
            'recentInvoices' => DashboardService::getRecentInvoices(5),
            'recentActivities' => DashboardService::getRecentActivities(10),
        ]);
    }
}
