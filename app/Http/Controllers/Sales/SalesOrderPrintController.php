<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesOrder;
use Illuminate\Http\Request;

class SalesOrderPrintController extends Controller
{
    public function __invoke(Request $request, int $id)
    {
        $order = SalesOrder::with([
            'customer',
            'items.product',
            'items.tax',
            'user',
        ])->findOrFail($id);

        return view('sales.orders.print', [
            'order' => $order,
        ]);
    }
}
