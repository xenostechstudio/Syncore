<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Request</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .container { padding: 40px; }
        .company-name { font-size: 24px; font-weight: bold; color: #111; margin-bottom: 8px; }
        .company-details { color: #666; font-size: 11px; }
        .doc-title { font-size: 28px; font-weight: bold; color: #111; margin-bottom: 8px; }
        .doc-meta { margin-top: 16px; }
        .doc-meta p { margin-bottom: 4px; }
        .doc-meta strong { color: #111; }
        .section { margin-bottom: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 12px; }
        .info-row { display: flex; margin-bottom: 8px; }
        .info-label { width: 150px; color: #666; }
        .info-value { font-weight: 500; color: #111; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-approved { background: #d1fae5; color: #059669; }
        .status-rejected { background: #fee2e2; color: #dc2626; }
        .status-cancelled { background: #f3f4f6; color: #6b7280; }
        .signature-section { margin-top: 60px; }
        .signature-box { display: inline-block; width: 200px; text-align: center; margin-right: 40px; }
        .signature-line { border-top: 1px solid #333; margin-top: 60px; padding-top: 8px; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #999; font-size: 10px; }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <table style="margin-bottom: 40px; width: 100%;">
            <tr>
                <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                    <div class="company-name">{{ $company['name'] ?? config('app.name') }}</div>
                    <div class="company-details">
                        @if($company['address'] ?? false){{ $company['address'] }}<br>@endif
                        @if($company['phone'] ?? false)Phone: {{ $company['phone'] }}<br>@endif
                        @if($company['email'] ?? false)Email: {{ $company['email'] }}@endif
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right; border: none; padding: 0;">
                    <div class="doc-title">LEAVE REQUEST</div>
                    <div class="doc-meta">
                        <p><strong>Date:</strong> {{ $leaveRequest->created_at->format('M d, Y') }}</p>
                        <p>
                            <span class="status-badge status-{{ $leaveRequest->status }}">
                                {{ ucfirst($leaveRequest->status) }}
                            </span>
                        </p>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Employee Information --}}
        <div class="section">
            <div class="section-title">Employee Information</div>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; border: none; padding: 4px 0;">
                        <span class="info-label">Name:</span>
                        <span class="info-value">{{ $leaveRequest->employee->name }}</span>
                    </td>
                    <td style="width: 50%; border: none; padding: 4px 0;">
                        <span class="info-label">Department:</span>
                        <span class="info-value">{{ $leaveRequest->employee->department?->name ?? '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 4px 0;">
                        <span class="info-label">Position:</span>
                        <span class="info-value">{{ $leaveRequest->employee->position?->name ?? '-' }}</span>
                    </td>
                    <td style="border: none; padding: 4px 0;">
                        <span class="info-label">Employee ID:</span>
                        <span class="info-value">{{ $leaveRequest->employee->id_number ?? '-' }}</span>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Leave Details --}}
        <div class="section">
            <div class="section-title">Leave Details</div>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; border: none; padding: 4px 0;">
                        <span class="info-label">Leave Type:</span>
                        <span class="info-value">{{ $leaveRequest->leaveType->name }}</span>
                    </td>
                    <td style="width: 50%; border: none; padding: 4px 0;">
                        <span class="info-label">Duration:</span>
                        <span class="info-value">{{ $leaveRequest->days }} day(s)</span>
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 4px 0;">
                        <span class="info-label">Start Date:</span>
                        <span class="info-value">{{ $leaveRequest->start_date->format('M d, Y') }}</span>
                    </td>
                    <td style="border: none; padding: 4px 0;">
                        <span class="info-label">End Date:</span>
                        <span class="info-value">{{ $leaveRequest->end_date->format('M d, Y') }}</span>
                    </td>
                </tr>
            </table>
            @if($leaveRequest->reason)
            <div style="margin-top: 16px;">
                <span class="info-label">Reason:</span>
                <p style="margin-top: 8px; color: #333;">{{ $leaveRequest->reason }}</p>
            </div>
            @endif
        </div>

        {{-- Approval Information --}}
        @if($leaveRequest->status !== 'draft' && $leaveRequest->status !== 'pending')
        <div class="section">
            <div class="section-title">Approval Information</div>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; border: none; padding: 4px 0;">
                        <span class="info-label">Status:</span>
                        <span class="info-value">{{ ucfirst($leaveRequest->status) }}</span>
                    </td>
                    <td style="width: 50%; border: none; padding: 4px 0;">
                        <span class="info-label">Approved/Rejected By:</span>
                        <span class="info-value">{{ $leaveRequest->approver?->name ?? '-' }}</span>
                    </td>
                </tr>
                @if($leaveRequest->approved_at || $leaveRequest->rejected_at)
                <tr>
                    <td style="border: none; padding: 4px 0;">
                        <span class="info-label">Date:</span>
                        <span class="info-value">{{ ($leaveRequest->approved_at ?? $leaveRequest->rejected_at)?->format('M d, Y H:i') }}</span>
                    </td>
                    <td style="border: none; padding: 4px 0;"></td>
                </tr>
                @endif
            </table>
        </div>
        @endif

        {{-- Signatures --}}
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Employee</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Manager/Supervisor</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">HR Department</div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>Generated on {{ now()->format('M d, Y H:i') }}</p>
            <p>{{ $company['name'] ?? config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
