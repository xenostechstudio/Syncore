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
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function salesOrders(Request $request)
    {
        $ids = $request->input('ids') ? explode(',', $request->input('ids')) : null;
        return Excel::download(new SalesOrdersExport($ids), 'sales-orders-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function invoices(Request $request)
    {
        $ids = $request->input('ids') ? explode(',', $request->input('ids')) : null;
        return Excel::download(new InvoicesExport($ids), 'invoices-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function deliveryOrders(Request $request)
    {
        $ids = $request->input('ids') ? explode(',', $request->input('ids')) : null;
        return Excel::download(new DeliveryOrdersExport($ids), 'delivery-orders-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function customers(Request $request)
    {
        $ids = $request->input('ids') ? explode(',', $request->input('ids')) : null;
        return Excel::download(new CustomersExport($ids), 'customers-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function products(Request $request)
    {
        $ids = $request->input('ids') ? explode(',', $request->input('ids')) : null;
        return Excel::download(new ProductsExport($ids), 'products-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function purchaseOrders(Request $request)
    {
        $ids = $request->input('ids') ? explode(',', $request->input('ids')) : null;
        return Excel::download(new PurchaseOrdersExport($ids), 'purchase-orders-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function suppliers(Request $request)
    {
        $ids = $request->input('ids') ? explode(',', $request->input('ids')) : null;
        return Excel::download(new SuppliersExport($ids), 'suppliers-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function warehouses(Request $request)
    {
        $ids = $request->input('ids') ? explode(',', $request->input('ids')) : null;
        return Excel::download(new WarehousesExport($ids), 'warehouses-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function categories(Request $request)
    {
        $ids = $request->input('ids') ? explode(',', $request->input('ids')) : null;
        return Excel::download(new CategoriesExport($ids), 'categories-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function users(Request $request)
    {
        $ids = $request->input('ids') ? explode(',', $request->input('ids')) : null;
        return Excel::download(new UsersExport($ids), 'users-' . now()->format('Y-m-d') . '.xlsx');
    }
}
