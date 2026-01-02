<?php

namespace App\Livewire\Reports\Inventory;

use App\Models\Inventory\Warehouse;
use App\Services\Reports\InventoryReportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Reports'])]
#[Title('Inventory Reports')]
class Index extends Component
{
    public ?int $warehouseId = null;
    public int $lowStockThreshold = 10;
    public string $reportType = 'valuation';

    public function render()
    {
        $service = app(InventoryReportService::class);

        return view('livewire.reports.inventory.index', [
            'warehouses' => Warehouse::all(),
            'summary' => $service->getSummary(),
            'stockValuation' => $service->getStockValuation($this->warehouseId),
            'lowStockProducts' => $service->getLowStockProducts($this->lowStockThreshold),
            'outOfStockProducts' => $service->getOutOfStockProducts(),
            'stockByWarehouse' => $service->getStockByWarehouse(),
        ]);
    }
}
