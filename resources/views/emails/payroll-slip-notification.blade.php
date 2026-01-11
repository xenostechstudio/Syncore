<x-mail::message>
# Payroll Slip

Dear {{ $employee?->name ?? 'Employee' }},

Your payroll slip for **{{ $period?->name ?? 'this period' }}** is now available.

## Salary Details

| Description | Amount |
|:------------|-------:|
| Basic Salary | Rp {{ number_format($payrollItem->basic_salary ?? 0, 0, ',', '.') }} |
| Total Allowances | Rp {{ number_format($payrollItem->total_allowances ?? 0, 0, ',', '.') }} |
| **Gross Salary** | **Rp {{ number_format($payrollItem->gross_salary ?? 0, 0, ',', '.') }}** |
| Total Deductions | (Rp {{ number_format($payrollItem->total_deductions ?? 0, 0, ',', '.') }}) |
| **Net Salary** | **Rp {{ number_format($payrollItem->net_salary ?? 0, 0, ',', '.') }}** |

@if($payrollItem->payment_date)
**Payment Date:** {{ $payrollItem->payment_date->format('M d, Y') }}
@endif

If you have any questions about your payroll, please contact the HR department.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
