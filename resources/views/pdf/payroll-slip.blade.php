<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Slip - {{ $payrollItem->employee?->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; line-height: 1.4; color: #333; }
        .container { padding: 30px; max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #333; }
        .company-name { font-size: 20px; font-weight: bold; color: #1a1a1a; margin-bottom: 5px; }
        .document-title { font-size: 16px; color: #666; margin-top: 10px; text-transform: uppercase; letter-spacing: 2px; }
        .period-info { margin-top: 10px; font-size: 14px; font-weight: bold; color: #333; }
        .employee-section { background: #f8f8f8; padding: 20px; border-radius: 8px; margin-bottom: 25px; }
        .employee-grid { display: table; width: 100%; }
        .employee-row { display: table-row; }
        .employee-cell { display: table-cell; padding: 5px 10px; width: 50%; }
        .employee-label { font-size: 9px; text-transform: uppercase; color: #666; }
        .employee-value { font-size: 12px; font-weight: bold; margin-top: 2px; }
        .earnings-deductions { display: table; width: 100%; margin-bottom: 25px; }
        .ed-column { display: table-cell; width: 50%; vertical-align: top; }
        .ed-column:first-child { padding-right: 15px; }
        .ed-column:last-child { padding-left: 15px; }
        .ed-title { font-size: 12px; font-weight: bold; text-transform: uppercase; color: #333; padding: 10px; background: #e8e8e8; margin-bottom: 10px; }
        .ed-title.earnings { background: #d1fae5; color: #065f46; }
        .ed-title.deductions { background: #fee2e2; color: #991b1b; }
        .ed-item { display: flex; justify-content: space-between; padding: 8px 10px; border-bottom: 1px solid #eee; }
        .ed-item:last-child { border-bottom: none; }
        .ed-item-name { color: #666; }
        .ed-item-amount { font-weight: bold; }
        .ed-total { display: flex; justify-content: space-between; padding: 10px; background: #f0f0f0; font-weight: bold; margin-top: 10px; }
        .summary-section { background: #1a1a1a; color: white; padding: 20px; border-radius: 8px; margin-bottom: 25px; }
        .summary-grid { display: table; width: 100%; }
        .summary-row { display: table-row; }
        .summary-cell { display: table-cell; text-align: center; padding: 10px; width: 33.33%; }
        .summary-label { font-size: 9px; text-transform: uppercase; color: #999; }
        .summary-value { font-size: 18px; font-weight: bold; margin-top: 5px; }
        .summary-value.net { color: #4ade80; font-size: 24px; }
        .net-pay-box { text-align: center; padding: 25px; background: linear-gradient(135deg, #059669, #047857); color: white; border-radius: 8px; margin-bottom: 25px; }
        .net-pay-label { font-size: 12px; text-transform: uppercase; letter-spacing: 2px; opacity: 0.9; }
        .net-pay-amount { font-size: 32px; font-weight: bold; margin-top: 10px; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; }
        .footer-grid { display: table; width: 100%; }
        .footer-cell { display: table-cell; width: 50%; padding: 20px; }
        .signature-line { border-top: 1px solid #333; margin-top: 50px; padding-top: 10px; text-align: center; font-size: 10px; color: #666; }
        .confidential { text-align: center; margin-top: 30px; font-size: 9px; color: #999; text-transform: uppercase; letter-spacing: 1px; }
        table.details { width: 100%; border-collapse: collapse; }
        table.details td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        table.details td:last-child { text-align: right; font-weight: bold; }
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #333; padding: 10px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">{{ $company['name'] }}</div>
            @if($company['address'])<div style="font-size: 10px; color: #666;">{{ $company['address'] }}</div>@endif
            <div class="document-title">Payroll Slip</div>
            <div class="period-info">{{ $payrollItem->period?->name ?? 'Payroll Period' }}</div>
        </div>

        <div class="employee-section">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; padding: 5px 10px; border: none;">
                        <div class="employee-label">Employee Name</div>
                        <div class="employee-value">{{ $payrollItem->employee?->name ?? '-' }}</div>
                    </td>
                    <td style="width: 50%; padding: 5px 10px; border: none;">
                        <div class="employee-label">Employee ID</div>
                        <div class="employee-value">{{ $payrollItem->employee?->employee_id ?? '-' }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px 10px; border: none;">
                        <div class="employee-label">Department</div>
                        <div class="employee-value">{{ $payrollItem->employee?->department?->name ?? '-' }}</div>
                    </td>
                    <td style="padding: 5px 10px; border: none;">
                        <div class="employee-label">Position</div>
                        <div class="employee-value">{{ $payrollItem->employee?->position?->name ?? '-' }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px 10px; border: none;">
                        <div class="employee-label">Join Date</div>
                        <div class="employee-value">{{ $payrollItem->employee?->join_date?->format('M d, Y') ?? '-' }}</div>
                    </td>
                    <td style="padding: 5px 10px; border: none;">
                        <div class="employee-label">Payment Date</div>
                        <div class="employee-value">{{ $payrollItem->payment_date?->format('M d, Y') ?? '-' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <table style="width: 100%; margin-bottom: 25px;">
            <tr>
                <td style="width: 48%; vertical-align: top; border: none; padding-right: 15px;">
                    <div class="section-title" style="background: #d1fae5; color: #065f46; padding: 10px;">Earnings</div>
                    <table class="details">
                        <tr>
                            <td>Basic Salary</td>
                            <td>Rp {{ number_format($payrollItem->basic_salary ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @if($payrollItem->details)
                            @foreach($payrollItem->details->where('type', 'allowance') as $detail)
                            <tr>
                                <td>{{ $detail->component?->name ?? $detail->description }}</td>
                                <td>Rp {{ number_format($detail->amount ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        @endif
                        <tr style="background: #f0f0f0;">
                            <td style="font-weight: bold;">Total Earnings</td>
                            <td style="font-weight: bold;">Rp {{ number_format(($payrollItem->basic_salary ?? 0) + ($payrollItem->total_allowances ?? 0), 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 48%; vertical-align: top; border: none; padding-left: 15px;">
                    <div class="section-title" style="background: #fee2e2; color: #991b1b; padding: 10px;">Deductions</div>
                    <table class="details">
                        @if($payrollItem->details)
                            @foreach($payrollItem->details->where('type', 'deduction') as $detail)
                            <tr>
                                <td>{{ $detail->component?->name ?? $detail->description }}</td>
                                <td>Rp {{ number_format($detail->amount ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        @endif
                        @if(!$payrollItem->details || $payrollItem->details->where('type', 'deduction')->isEmpty())
                        <tr>
                            <td colspan="2" style="text-align: center; color: #999;">No deductions</td>
                        </tr>
                        @endif
                        <tr style="background: #f0f0f0;">
                            <td style="font-weight: bold;">Total Deductions</td>
                            <td style="font-weight: bold;">Rp {{ number_format($payrollItem->total_deductions ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="net-pay-box">
            <div class="net-pay-label">Net Pay</div>
            <div class="net-pay-amount">Rp {{ number_format($payrollItem->net_salary ?? 0, 0, ',', '.') }}</div>
        </div>

        <table style="width: 100%; background: #f8f8f8; margin-bottom: 25px;">
            <tr>
                <td style="width: 33.33%; text-align: center; padding: 15px; border: none;">
                    <div style="font-size: 9px; text-transform: uppercase; color: #666;">Gross Salary</div>
                    <div style="font-size: 16px; font-weight: bold; margin-top: 5px;">Rp {{ number_format($payrollItem->gross_salary ?? 0, 0, ',', '.') }}</div>
                </td>
                <td style="width: 33.33%; text-align: center; padding: 15px; border: none;">
                    <div style="font-size: 9px; text-transform: uppercase; color: #666;">Total Deductions</div>
                    <div style="font-size: 16px; font-weight: bold; margin-top: 5px; color: #dc2626;">Rp {{ number_format($payrollItem->total_deductions ?? 0, 0, ',', '.') }}</div>
                </td>
                <td style="width: 33.33%; text-align: center; padding: 15px; border: none;">
                    <div style="font-size: 9px; text-transform: uppercase; color: #666;">Net Salary</div>
                    <div style="font-size: 16px; font-weight: bold; margin-top: 5px; color: #059669;">Rp {{ number_format($payrollItem->net_salary ?? 0, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>

        <div class="footer">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; padding: 20px; border: none; vertical-align: top;">
                        <div class="signature-line">Employee Signature</div>
                    </td>
                    <td style="width: 50%; padding: 20px; border: none; vertical-align: top;">
                        <div class="signature-line">Authorized Signature</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="confidential">
            This is a confidential document. Generated on {{ now()->format('M d, Y H:i') }}
        </div>
    </div>
</body>
</html>
