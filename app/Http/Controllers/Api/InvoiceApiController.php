<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoicing\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with('customer');

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        if ($request->boolean('overdue')) {
            $query->where('status', 'overdue')
                ->orWhere(function ($q) {
                    $q->whereIn('status', ['sent', 'partial'])
                        ->where('due_date', '<', now());
                });
        }

        $invoices = $query->latest('invoice_date')->paginate($request->per_page ?? 15);

        return $this->paginated($invoices);
    }

    public function show(int $id): JsonResponse
    {
        $invoice = Invoice::with(['customer', 'items.product', 'payments'])->find($id);

        if (!$invoice) {
            return $this->notFound('Invoice not found');
        }

        return $this->success($invoice);
    }

    public function summary(): JsonResponse
    {
        $summary = [
            'total_outstanding' => Invoice::whereIn('status', ['sent', 'partial', 'overdue'])
                ->selectRaw('COALESCE(SUM(total - COALESCE(paid_amount, 0)), 0) as amount')
                ->value('amount'),
            'overdue_amount' => Invoice::where('status', 'overdue')
                ->selectRaw('COALESCE(SUM(total - COALESCE(paid_amount, 0)), 0) as amount')
                ->value('amount'),
            'paid_this_month' => Invoice::where('status', 'paid')
                ->whereMonth('paid_date', now()->month)
                ->whereYear('paid_date', now()->year)
                ->sum('total'),
            'by_status' => Invoice::selectRaw('status, COUNT(*) as count, COALESCE(SUM(total), 0) as total')
                ->groupBy('status')
                ->get(),
        ];

        return $this->success($summary);
    }
}
