<?php

namespace App\Http\Controllers;

use App\Exports\CategoriesExport;
use App\Exports\CustomersExport;
use App\Exports\DeliveryOrdersExport;
use App\Exports\InvoicesExport;
use App\Exports\ProductsExport;
use App\Exports\PurchaseOrdersExport;
use App\Exports\SalesOrdersExport;
use App\Exports\SuppliersExport;
use App\Exports\UsersExport;
use App\Exports\WarehousesExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function salesOrders()
    {
        return Excel::download(new SalesOrdersExport, 'sales-orders-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function invoices()
    {
        return Excel::download(new InvoicesExport, 'invoices-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function deliveryOrders()
    {
        return Excel::download(new DeliveryOrdersExport, 'delivery-orders-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function customers()
    {
        return Excel::download(new CustomersExport, 'customers-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function products()
    {
        return Excel::download(new ProductsExport, 'products-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function purchaseOrders()
    {
        return Excel::download(new PurchaseOrdersExport, 'purchase-orders-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function suppliers()
    {
        return Excel::download(new SuppliersExport, 'suppliers-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function warehouses()
    {
        return Excel::download(new WarehousesExport, 'warehouses-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function categories()
    {
        return Excel::download(new CategoriesExport, 'categories-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function users()
    {
        return Excel::download(new UsersExport, 'users-' . now()->format('Y-m-d') . '.xlsx');
    }
}
