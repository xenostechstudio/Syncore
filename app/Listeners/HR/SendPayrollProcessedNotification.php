<?php

namespace App\Listeners\HR;

use App\Events\PayrollProcessed;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPayrollProcessedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(PayrollProcessed $event): void
    {
        $payrollPeriod = $event->payrollPeriod;

        NotificationService::create(
            type: 'payroll_processed',
            title: 'Payroll Processed',
            message: "Payroll '{$payrollPeriod->name}' has been processed. Total net salary: " . number_format($payrollPeriod->total_net, 2) . " for {$payrollPeriod->employee_count} employees.",
            notifiable: $payrollPeriod,
            data: [
                'payroll_period_id' => $payrollPeriod->id,
                'total_gross' => $payrollPeriod->total_gross,
                'total_deductions' => $payrollPeriod->total_deductions,
                'total_net' => $payrollPeriod->total_net,
                'employee_count' => $payrollPeriod->employee_count,
            ]
        );

        // Notify each employee about their payslip
        foreach ($payrollPeriod->items as $item) {
            if ($item->employee?->user_id) {
                NotificationService::create(
                    type: 'payslip_ready',
                    title: 'Payslip Ready',
                    message: "Your payslip for {$payrollPeriod->name} is ready. Net salary: " . number_format($item->net_salary, 2),
                    notifiable: $item,
                    userId: $item->employee->user_id,
                    data: [
                        'payroll_item_id' => $item->id,
                        'net_salary' => $item->net_salary,
                    ]
                );
            }
        }
    }
}
