<?php

namespace App\Services\Reports;

use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceReportService
{
    public function getRevenueByPeriod(Carbon $startDate, Carbon $endDate, string $groupBy = 'month'): array
    {
        // Use database-agnostic approach: fetch data and group in PHP
        $invoices = Invoice::query()
            ->select('invoice_date', 'total', 'paid_amount')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->get();

        $grouped = $invoices->groupBy(function ($invoice) use ($groupBy) {
            $date = Carbon::parse($invoice->invoice_date);
            return match ($groupBy) {
                'day' => $date->format('Y-m-d'),
                'week' => $date->format('o-W'),
                'month' => $date->format('Y-m'),
                'year' => $date->format('Y'),
                default => $date->format('Y-m'),
            };
        });

        return $grouped->map(fn($items, $period) => [
            'period' => $period,
            'invoice_count' => $items->count(),
            'total_revenue' => $items->sum('total'),
            'total_collected' => $items->sum('paid_amount'),
        ])->sortKeys()->values()->toArray();
    }

    public function getAgingReport(): array
    {
        $today = now();

        $aging = [
            'current' => ['count' => 0, 'amount' => 0],
            '1_30' => ['count' => 0, 'amount' => 0],
            '31_60' => ['count' => 0, 'amount' => 0],
            '61_90' => ['count' => 0, 'amount' => 0],
            'over_90' => ['count' => 0, 'amount' => 0],
        ];

        $invoices = Invoice::query()
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->get();

        foreach ($invoices as $invoice) {
            $dueDate = $invoice->due_date ?? $invoice->invoice_date;
            $daysOverdue = $today->diffInDays($dueDate, false);
            $amountDue = $invoice->total - ($invoice->paid_amount ?? 0);

            if ($daysOverdue >= 0) {
                $aging['current']['count']++;
                $aging['current']['amount'] += $amountDue;
            } elseif ($daysOverdue >= -30) {
                $aging['1_30']['count']++;
                $aging['1_30']['amount'] += $amountDue;
            } elseif ($daysOverdue >= -60) {
                $aging['31_60']['count']++;
                $aging['31_60']['amount'] += $amountDue;
            } elseif ($daysOverdue >= -90) {
                $aging['61_90']['count']++;
                $aging['61_90']['amount'] += $amountDue;
            } else {
                $aging['over_90']['count']++;
                $aging['over_90']['amount'] += $amountDue;
            }
        }

        return $aging;
    }

    public function getPaymentsByMethod(Carbon $startDate, Carbon $endDate): array
    {
        return Payment::query()
            ->select('payment_method')
            ->selectRaw('COUNT(*) as payment_count, SUM(amount) as total_amount')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get()
            ->toArray();
    }

    public function getSummary(Carbon $startDate, Carbon $endDate): array
    {
        $invoices = Invoice::whereBetween('invoice_date', [$startDate, $endDate]);
        
        $totalInvoiced = (clone $invoices)->sum('total');
        $totalCollected = (clone $invoices)->sum('paid_amount');
        $invoiceCount = (clone $invoices)->count();
        
        $outstanding = Invoice::whereIn('status', ['sent', 'partial', 'overdue'])
            ->selectRaw('SUM(total - COALESCE(paid_amount, 0)) as amount')
            ->value('amount') ?? 0;

        $overdue = Invoice::where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->whereIn('status', ['sent', 'partial'])
                    ->where('due_date', '<', now());
            })
            ->selectRaw('SUM(total - COALESCE(paid_amount, 0)) as amount')
            ->value('amount') ?? 0;

        return [
            'total_invoiced' => $totalInvoiced,
            'total_collected' => $totalCollected,
            'invoice_count' => $invoiceCount,
            'outstanding' => $outstanding,
            'overdue' => $overdue,
            'collection_rate' => $totalInvoiced > 0 ? ($totalCollected / $totalInvoiced) * 100 : 0,
        ];
    }
}
